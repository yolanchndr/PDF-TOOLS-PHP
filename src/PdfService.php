<?php

declare(strict_types=1);

namespace LocalPdf;

use RuntimeException;
use setasign\Fpdi\Fpdi;
use SplFileInfo;

final class PdfService
{
    public function merge(array $files, string $outputPath): void
    {
        if (count($files) < 2) {
            throw new RuntimeException('Upload minimal 2 file PDF untuk digabung.');
        }

        $pdf = new Fpdi();

        foreach ($files as $file) {
            $this->assertPdf($file);
            $pageCount = $pdf->setSourceFile($file);

            for ($page = 1; $page <= $pageCount; $page++) {
                $this->copyPage($pdf, $page);
            }
        }

        $pdf->Output('F', $outputPath);
    }

    public function extractPages(string $file, string $pageExpression, string $outputPath): void
    {
        $this->assertPdf($file);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($file);
        $pages = $this->parsePageExpression($pageExpression, $pageCount);

        if ($pages === []) {
            throw new RuntimeException('Masukkan halaman yang ingin dipisahkan, contoh: 1-3,5.');
        }

        foreach ($pages as $page) {
            $this->copyPage($pdf, $page);
        }

        $pdf->Output('F', $outputPath);
    }

    private function copyPage(Fpdi $pdf, int $page): void
    {
        $templateId = $pdf->importPage($page);
        $size = $pdf->getTemplateSize($templateId);
        $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }

    private function parsePageExpression(string $expression, int $maxPage): array
    {
        $pages = [];
        $parts = array_filter(array_map('trim', explode(',', $expression)));

        foreach ($parts as $part) {
            if (preg_match('/^\d+$/', $part) === 1) {
                $pages[] = (int) $part;
                continue;
            }

            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $part, $matches) === 1) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                $step = $start <= $end ? 1 : -1;

                for ($page = $start; $page !== $end + $step; $page += $step) {
                    $pages[] = $page;
                }
            }
        }

        $validPages = [];

        foreach ($pages as $page) {
            if ($page < 1 || $page > $maxPage) {
                throw new RuntimeException("Halaman {$page} tidak ada. PDF ini hanya punya {$maxPage} halaman.");
            }

            $validPages[] = $page;
        }

        return $validPages;
    }

    private function assertPdf(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException('File PDF tidak ditemukan.');
        }

        $info = new SplFileInfo($file);
        if (strtolower($info->getExtension()) !== 'pdf') {
            throw new RuntimeException('Hanya file PDF yang boleh diproses.');
        }
    }
}
