<?php

declare(strict_types=1);

use LocalPdf\PdfService;

require __DIR__ . '/../vendor/autoload.php';

$basePath = dirname(__DIR__);
$uploadDir = $basePath . '/storage/uploads';
$outputDir = $basePath . '/storage/outputs';

ensureDirectory($uploadDir);
ensureDirectory($outputDir);

if (isset($_GET['download'])) {
    sendDownload($outputDir, (string) $_GET['download']);
}

$message = null;
$download = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $service = new PdfService();
        $action = $_POST['action'] ?? '';

        if ($action === 'merge') {
            $paths = receiveUploadedPdfs('pdfs', $uploadDir);
            $orderedPaths = orderUploadedFiles($paths, $_POST['order'] ?? '');
            $outputPath = $outputDir . '/merged-' . date('Ymd-His') . '.pdf';

            $service->merge($orderedPaths, $outputPath);
            $message = 'PDF berhasil digabung.';
            $download = publicDownloadPath($outputPath);
        }

        if ($action === 'split') {
            $paths = receiveUploadedPdfs('single_pdf', $uploadDir);
            $range = trim((string) ($_POST['pages'] ?? ''));
            $outputPath = $outputDir . '/pages-' . date('Ymd-His') . '.pdf';

            $service->extractPages($paths[0], $range, $outputPath);
            $message = 'Halaman PDF berhasil dipisahkan.';
            $download = publicDownloadPath($outputPath);
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function receiveUploadedPdfs(string $field, string $uploadDir): array
{
    if (!isset($_FILES[$field])) {
        throw new RuntimeException('Pilih file PDF terlebih dahulu.');
    }

    $files = normalizeFiles($_FILES[$field]);
    $saved = [];

    foreach ($files as $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload gagal. Coba cek ukuran file dan setting PHP.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            throw new RuntimeException('Hanya file PDF yang boleh diupload.');
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename($file['name']));
        $target = $uploadDir . '/' . uniqid('pdf-', true) . '-' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Gagal menyimpan file upload.');
        }

        $saved[] = $target;
    }

    if ($saved === []) {
        throw new RuntimeException('Pilih file PDF terlebih dahulu.');
    }

    return $saved;
}

function normalizeFiles(array $files): array
{
    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index],
            'tmp_name' => $files['tmp_name'][$index],
            'error' => $files['error'][$index],
            'size' => $files['size'][$index],
        ];
    }

    return $normalized;
}

function orderUploadedFiles(array $paths, string $order): array
{
    $indexes = array_filter(array_map('trim', explode(',', $order)), static fn (string $item): bool => $item !== '');
    if ($indexes === []) {
        return $paths;
    }

    $ordered = [];
    foreach ($indexes as $index) {
        $position = (int) $index;
        if (isset($paths[$position])) {
            $ordered[] = $paths[$position];
        }
    }

    return count($ordered) === count($paths) ? $ordered : $paths;
}

function publicDownloadPath(string $path): string
{
    return '?download=' . rawurlencode(basename($path));
}

function sendDownload(string $outputDir, string $fileName): void
{
    $safeName = basename($fileName);
    $path = $outputDir . '/' . $safeName;

    if (!is_file($path)) {
        http_response_code(404);
        echo 'File tidak ditemukan.';
        exit;
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $safeName . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Local PDF Tools</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <div>
                <p class="eyebrow">Local only</p>
                <h1>PDF Tools</h1>
            </div>
            <span class="badge">Merge · Split · Reorder</span>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($message && $download): ?>
            <div class="alert alert-success">
                <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
                <a href="<?= htmlspecialchars($download, ENT_QUOTES, 'UTF-8') ?>" download>Download hasil</a>
            </div>
        <?php endif; ?>

        <section class="grid">
            <form class="panel" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="merge">
                <input type="hidden" id="merge-order" name="order" value="">

                <div class="panel-head">
                    <h2>Gabungkan PDF</h2>
                    <p>Pilih beberapa file lalu geser untuk mengatur urutan.</p>
                </div>

                <label class="dropzone">
                    <input id="merge-files" type="file" name="pdfs[]" accept="application/pdf,.pdf" multiple required>
                    <span>Pilih PDF</span>
                </label>

                <ol id="file-list" class="file-list"></ol>

                <button type="submit">Gabungkan PDF</button>
            </form>

            <form class="panel" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="split">

                <div class="panel-head">
                    <h2>Pisahkan Halaman</h2>
                    <p>Ambil halaman tertentu dan simpan sebagai PDF baru.</p>
                </div>

                <label class="dropzone">
                    <input type="file" name="single_pdf" accept="application/pdf,.pdf" required>
                    <span>Pilih 1 PDF</span>
                </label>

                <label class="field">
                    <span>Halaman</span>
                    <input type="text" name="pages" placeholder="Contoh: 1-3,5,8-6" required>
                </label>

                <button type="submit">Pisahkan PDF</button>
            </form>
        </section>
    </main>

    <script src="assets/app.js"></script>
</body>
</html>
