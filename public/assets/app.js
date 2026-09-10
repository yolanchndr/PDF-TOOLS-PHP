const input = document.querySelector('#merge-files');
const list = document.querySelector('#file-list');
const orderInput = document.querySelector('#merge-order');

let currentOrder = [];

function syncOrder() {
  orderInput.value = currentOrder.join(',');
}

function renderList() {
  list.innerHTML = '';

  if (!input.files.length) {
    syncOrder();
    return;
  }

  currentOrder.forEach((fileIndex, visibleIndex) => {
    const file = input.files[fileIndex];
    const item = document.createElement('li');
    item.draggable = true;
    item.dataset.fileIndex = String(fileIndex);
    item.innerHTML = `
      <span class="handle">↕</span>
      <span class="file-name">${file.name}</span>
      <span class="file-number">${visibleIndex + 1}</span>
    `;

    item.addEventListener('dragstart', event => {
      event.dataTransfer.setData('text/plain', String(fileIndex));
    });

    item.addEventListener('dragover', event => {
      event.preventDefault();
    });

    item.addEventListener('drop', event => {
      event.preventDefault();
      const draggedIndex = Number(event.dataTransfer.getData('text/plain'));
      const targetIndex = Number(item.dataset.fileIndex);
      const from = currentOrder.indexOf(draggedIndex);
      const to = currentOrder.indexOf(targetIndex);

      currentOrder.splice(from, 1);
      currentOrder.splice(to, 0, draggedIndex);
      renderList();
    });

    list.appendChild(item);
  });

  syncOrder();
}

if (input) {
  input.addEventListener('change', () => {
    currentOrder = Array.from(input.files, (_, index) => index);
    renderList();
  });
}
