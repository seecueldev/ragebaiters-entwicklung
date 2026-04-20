/* ============================================================
   Ragebaiters – Dashboard-Upload + Mediathek-Lightbox
   ============================================================ */

(function () {
  const $ = (sel, root) => (root || document).querySelector(sel);
  const $$ = (sel, root) => Array.from((root || document).querySelectorAll(sel));

  /* ---------- DASHBOARD UPLOAD ---------- */
  const dropzone  = $('#dropzone');
  const fileInput = $('#fileInput');
  const csrfEl    = $('#csrfToken');
  const listEl    = $('#uploadList');

  if (dropzone && fileInput) {
    dropzone.addEventListener('click', () => fileInput.click());

    ['dragover', 'dragenter'].forEach(ev => {
      dropzone.addEventListener(ev, e => {
        e.preventDefault();
        dropzone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach(ev => {
      dropzone.addEventListener(ev, e => {
        e.preventDefault();
        dropzone.classList.remove('is-dragover');
      });
    });

    dropzone.addEventListener('drop', e => {
      if (e.dataTransfer?.files?.length) handleFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length) handleFiles(fileInput.files);
      fileInput.value = '';
    });

    function handleFiles(files) {
      [...files].forEach(uploadOne);
    }

    function uploadOne(file) {
      const row = document.createElement('div');
      row.className = 'upload-row';
      row.innerHTML = `
        <div class="upload-row-name">${escapeHtml(file.name)}</div>
        <div class="upload-row-bar"><span></span></div>
        <div class="upload-row-status">0 %</div>
      `;
      listEl.appendChild(row);

      const bar     = row.querySelector('.upload-row-bar span');
      const status  = row.querySelector('.upload-row-status');

      const fd = new FormData();
      fd.append('csrf', csrfEl.value);
      fd.append('photo', file);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php');
      xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
          const pct = Math.round((e.loaded / e.total) * 100);
          bar.style.width = pct + '%';
          status.textContent = pct + ' %';
        }
      });
      xhr.onload = () => {
        let resp = {};
        try { resp = JSON.parse(xhr.responseText); } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300 && resp.ok) {
          bar.style.width = '100%';
          status.textContent = '✓ fertig';
          row.classList.add('is-done');
          setTimeout(() => location.reload(), 700);
        } else {
          status.textContent = '✗ ' + (resp.error || 'Fehler');
          row.classList.add('is-error');
        }
      };
      xhr.onerror = () => {
        status.textContent = '✗ Netzwerkfehler';
        row.classList.add('is-error');
      };
      xhr.send(fd);
    }
  }

  /* ---------- DASHBOARD LÖSCHEN ---------- */
  $$('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Dieses Foto wirklich löschen?')) return;
      const fd = new FormData();
      fd.append('csrf', csrfEl ? csrfEl.value : '');
      fd.append('id', btn.dataset.id);
      try {
        const r = await fetch('delete.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (j.ok) {
          const fig = btn.closest('.photo-item');
          fig.style.transition = 'opacity .25s ease, transform .25s ease';
          fig.style.opacity = '0';
          fig.style.transform = 'scale(.95)';
          setTimeout(() => fig.remove(), 250);
        } else {
          alert(j.error || 'Löschen fehlgeschlagen.');
        }
      } catch (_) {
        alert('Netzwerkfehler.');
      }
    });
  });

  /* ---------- LIGHTBOX ---------- */
  const grid = $('.lightbox-grid');
  const lb   = $('#lightbox');
  if (grid && lb) {
    const img     = $('.lightbox-img', lb);
    const caption = $('.lightbox-caption', lb);
    const btnClose= $('.lightbox-close', lb);
    const btnPrev = $('.prev', lb);
    const btnNext = $('.next', lb);

    const items = $$('.photo-item', grid);
    let idx = -1;

    function show(i) {
      if (i < 0 || i >= items.length) return;
      idx = i;
      const it = items[i];
      img.src = it.dataset.full;
      caption.innerHTML =
        `<strong>${escapeHtml(it.dataset.title || '')}</strong>` +
        (it.dataset.author ? ` <span>von ${escapeHtml(it.dataset.author)}</span>` : '');
      lb.classList.add('is-open');
      lb.setAttribute('aria-hidden', 'false');
    }
    function close() {
      lb.classList.remove('is-open');
      lb.setAttribute('aria-hidden', 'true');
      img.src = '';
    }

    items.forEach((it, i) => {
      it.addEventListener('click', e => {
        e.preventDefault();
        show(i);
      });
    });

    btnClose.addEventListener('click', close);
    btnPrev.addEventListener('click', () => show((idx - 1 + items.length) % items.length));
    btnNext.addEventListener('click', () => show((idx + 1) % items.length));
    lb.addEventListener('click', e => { if (e.target === lb) close(); });

    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape')     close();
      if (e.key === 'ArrowLeft')  show((idx - 1 + items.length) % items.length);
      if (e.key === 'ArrowRight') show((idx + 1) % items.length);
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[c]);
  }
})();
