'use strict';

function showAlert(el, type, msg) {
  el.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function initImagePreview(inputId, previewId, areaId) {
  const input   = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  const area    = document.getElementById(areaId);
  if (!input) return;

  area.addEventListener('click', () => input.click());
  area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
  area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
  area.addEventListener('drop', e => {
    e.preventDefault();
    area.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) showPreview(e.dataTransfer.files[0]);
  });

  input.addEventListener('change', function() {
    if (this.files[0]) showPreview(this.files[0]);
  });

  function showPreview(file) {
    if (!file.type.startsWith('image/')) {
      showAlert(document.getElementById('msg') || area, 'error', 'Please select an image file (JPG, PNG, GIF, WebP)');
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      area.classList.add('has-preview');
    };
    reader.readAsDataURL(file);
  }
}

function starsFromRating(n) {
  const r = Math.round(n / 2);
  return '★'.repeat(r) + '☆'.repeat(5 - r);
}
