/* ============================================================
   KotseTayo - Vanilla JavaScript
   Mobile nav, filters, validation, image preview, modals, etc.
   ============================================================ */
(function () {
  'use strict';

  /* ---------- Mobile navigation toggle ---------- */
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      const open = mainNav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  /* ---------- Admin sidebar toggle ---------- */
  const sidebarToggle = document.getElementById('sidebarToggle');
  const adminSidebar = document.getElementById('adminSidebar');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', function () {
      adminSidebar.classList.toggle('open');
    });
  }

  /* ---------- Gallery thumbnails ---------- */
  const mainImage = document.getElementById('mainImage');
  document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      if (mainImage) {
        mainImage.src = thumb.getAttribute('data-src');
      }
      document.querySelectorAll('.gallery-thumb').forEach(function (t) {
        t.classList.remove('active');
      });
      thumb.classList.add('active');
    });
  });

  /* ---------- Image preview (add/edit car) ---------- */
  const imageInput = document.getElementById('images');
  const imagePreview = document.getElementById('imagePreview');
  if (imageInput && imagePreview) {
    imageInput.addEventListener('change', function () {
      imagePreview.innerHTML = '';
      Array.from(imageInput.files).forEach(function (file) {
        if (!file.type.startsWith('image/')) return;
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        imagePreview.appendChild(img);
      });
    });
  }

  /* ---------- Sortable vehicle images (edit car) ---------- */
  const imageManager = document.getElementById('imageManager');
  if (imageManager) {
    let draggedImage = null;
    let dragPointerId = null;

    function refreshImageOrder() {
      const items = imageManager.querySelectorAll('.image-manager-item');
      const visibleItems = Array.from(items).filter(function (item) {
        const remove = item.querySelector('input[name="delete_image[]"]');
        return !remove || !remove.checked;
      });
      const emptyMessage = imageManager.querySelector('.image-manager-empty');
      if (emptyMessage) emptyMessage.hidden = visibleItems.length !== 0;
      const summary = document.getElementById('imageOrderSummary');
      if (summary) summary.textContent = visibleItems.length + (visibleItems.length === 1 ? ' image' : ' images');
      items.forEach(function (item, index) {
        const visibleIndex = visibleItems.indexOf(item);
        const badge = item.querySelector('.image-thumbnail-badge');
        const sequence = item.querySelector('.image-sequence-number');
        const up = item.querySelector('.image-move-up');
        const down = item.querySelector('.image-move-down');
        const makeThumbnail = item.querySelector('.image-make-thumbnail');
        const remove = item.querySelector('input[name="delete_image[]"]');
        item.classList.toggle('is-thumbnail', visibleIndex === 0);
        item.classList.toggle('is-marked-for-removal', Boolean(remove && remove.checked));
        if (badge) badge.hidden = visibleIndex !== 0;
        if (sequence) sequence.textContent = visibleIndex === -1 ? '—' : String(visibleIndex + 1).padStart(2, '0');
        if (up) up.disabled = visibleIndex <= 0;
        if (down) down.disabled = visibleIndex === -1 || visibleIndex === visibleItems.length - 1;
        if (makeThumbnail) {
          makeThumbnail.disabled = visibleIndex <= 0;
          makeThumbnail.textContent = visibleIndex === 0 ? 'Current thumbnail' : 'Make thumbnail';
        }
      });
    }

    imageManager.addEventListener('change', function (event) {
      if (event.target.matches('input[name="delete_image[]"]')) refreshImageOrder();
    });

    imageManager.addEventListener('click', function (event) {
      const button = event.target.closest('button');
      if (!button) return;
      const item = button.closest('.image-manager-item');
      if (!item) return;
      const visibleItems = Array.from(imageManager.querySelectorAll('.image-manager-item')).filter(function (candidate) {
        const remove = candidate.querySelector('input[name="delete_image[]"]');
        return !remove || !remove.checked;
      });
      const visibleIndex = visibleItems.indexOf(item);

      if (button.classList.contains('image-move-up') && visibleIndex > 0) {
        imageManager.insertBefore(item, visibleItems[visibleIndex - 1]);
      } else if (button.classList.contains('image-move-down') && visibleIndex < visibleItems.length - 1) {
        imageManager.insertBefore(visibleItems[visibleIndex + 1], item);
      } else if (button.classList.contains('image-make-thumbnail') && visibleIndex > 0) {
        imageManager.insertBefore(item, visibleItems[0]);
      }
      refreshImageOrder();
    });

    function finishImageDrag() {
      if (!draggedImage) return;
      draggedImage.classList.remove('is-dragging');
      draggedImage = null;
      dragPointerId = null;
      refreshImageOrder();
    }

    imageManager.addEventListener('pointerdown', function (event) {
      const handle = event.target.closest('.image-drag-handle');
      if (!handle || (event.pointerType === 'mouse' && event.button !== 0)) return;
      event.preventDefault();
      draggedImage = handle.closest('.image-manager-item');
      if (!draggedImage) return;
      dragPointerId = event.pointerId;
      draggedImage.classList.add('is-dragging');
      handle.setPointerCapture(event.pointerId);
    });

    imageManager.addEventListener('pointermove', function (event) {
      if (!draggedImage || event.pointerId !== dragPointerId) return;
      const pointedElement = document.elementFromPoint(event.clientX, event.clientY);
      const target = pointedElement && pointedElement.closest('.image-manager-item');
      if (!target || !imageManager.contains(target) || target === draggedImage) return;

      event.preventDefault();
      const bounds = target.getBoundingClientRect();
      const after = event.clientY > bounds.top + bounds.height / 2;
      imageManager.insertBefore(draggedImage, after ? target.nextElementSibling : target);
    });

    imageManager.addEventListener('pointerup', function (event) {
      if (event.pointerId === dragPointerId) finishImageDrag();
    });
    imageManager.addEventListener('pointercancel', finishImageDrag);

    if (imageInput) {
      imageInput.addEventListener('change', function () {
        imageManager.querySelectorAll('.new-image').forEach(function (item) {
          const img = item.querySelector('img');
          if (img && img.src.startsWith('blob:')) URL.revokeObjectURL(img.src);
          item.remove();
        });

        Array.from(imageInput.files).forEach(function (file, index) {
          if (!file.type.startsWith('image/')) return;
          const item = document.createElement('div');
          item.className = 'image-manager-item new-image';
          item.dataset.imageKey = 'new:' + index;

          const preview = document.createElement('div');
          preview.className = 'image-card-preview';
          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          img.alt = file.name;
          preview.appendChild(img);

          const badge = document.createElement('span');
          badge.className = 'image-thumbnail-badge';
          badge.textContent = 'Thumbnail';
          preview.appendChild(badge);
          item.appendChild(preview);

          const meta = document.createElement('div');
          meta.className = 'image-manager-meta';
          meta.innerHTML = '<span class="image-sequence-number"></span><span></span>';
          meta.lastElementChild.textContent = file.name;
          item.appendChild(meta);

          const controls = document.createElement('div');
          controls.className = 'image-order-controls';
          controls.innerHTML =
            '<div class="image-position-buttons">' +
              '<button type="button" class="image-move-up" aria-label="Move image earlier" title="Move earlier">&uarr;</button>' +
              '<button type="button" class="image-move-down" aria-label="Move image later" title="Move later">&darr;</button>' +
              '<span class="image-drag-handle" title="Drag to reorder" aria-label="Drag to reorder">&#9776; <span>Drag</span></span>' +
            '</div>' +
            '<button type="button" class="image-make-thumbnail">Make thumbnail</button>';
          item.appendChild(controls);

          const order = document.createElement('input');
          order.type = 'hidden';
          order.name = 'image_order[]';
          order.value = item.dataset.imageKey;
          item.appendChild(order);
          imageManager.appendChild(item);
        });
        refreshImageOrder();
      });
    }

    refreshImageOrder();
  }

  /* ---------- Delete confirmation modal ---------- */
  const deleteModal = document.getElementById('deleteModal');
  const deleteId = document.getElementById('deleteId');
  const deleteName = document.getElementById('deleteName');
  const deleteCancel = document.getElementById('deleteCancel');

  function showModal() {
    if (deleteModal) deleteModal.classList.add('show');
  }
  function hideModal() {
    if (deleteModal) deleteModal.classList.remove('show');
  }
  document.querySelectorAll('[data-delete-id]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (deleteId) deleteId.value = btn.getAttribute('data-delete-id');
      if (deleteName) deleteName.textContent = btn.getAttribute('data-delete-name');
      showModal();
    });
  });
  if (deleteCancel) deleteCancel.addEventListener('click', hideModal);
  if (deleteModal) deleteModal.addEventListener('click', function (e) {
    if (e.target === deleteModal) hideModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideModal();
  });
/* ---------- Inquiry detail modal ---------- */
  const inquiryModal = document.getElementById('inquiryModal');
  const inquiryModalBody = document.getElementById('inquiryModalBody');
  const inquiryClose = document.getElementById('inquiryClose');
  const inquiryData = window.__inquiries || [];

  function renderInquiry(i) {
    if (!inquiryModalBody) return;
    const d = inquiryData[i] || {};
    inquiryModalBody.innerHTML =
      '<div class="customer-detail-info">' +
        '<p><strong>Customer:</strong> ' + (d.customer || '') + '</p>' +
        '<p><strong>Vehicle:</strong> ' + (d.vehicle || '') + '</p>' +
        '<p><strong>Email:</strong> ' + (d.email || '') + '</p>' +
        '<p><strong>Contact:</strong> ' + (d.contact || '') + '</p>' +
        '<p><strong>Status:</strong> <span class="badge status-' + (d.status || '').toLowerCase() + '">' + (d.status || '') + '</span></p>' +
        '<p><strong>Date:</strong> ' + (d.date || '') + '</p>' +
        '<p><strong>Message:</strong></p><p style="white-space:pre-wrap">' + (d.message || '') + '</p>' +
      '</div>';
  }

  document.querySelectorAll('[data-inquiry-view]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const idx = parseInt(btn.getAttribute('data-inquiry-view'), 10) - 1;
      renderInquiry(idx);
      if (inquiryModal) inquiryModal.classList.add('show');
    });
  });
  if (inquiryClose) inquiryClose.addEventListener('click', function () {
    if (inquiryModal) inquiryModal.classList.remove('show');
  });

  /* ---------- Status pill: recolor instantly when status changes ---------- */
  document.querySelectorAll('select.status-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      sel.classList.remove('status-pending', 'status-contacted', 'status-completed', 'status-cancelled');
      sel.classList.add('status-' + (sel.value || '').toLowerCase());
    });
  });

  /* ---------- Auto-fill selling price when vehicle selected ---------- */
  const carSelect = document.getElementById('car_id');
  const priceInput = document.getElementById('selling_price');
  if (carSelect && priceInput) {
    carSelect.addEventListener('change', function () {
      const opt = carSelect.options[carSelect.selectedIndex];
      if (opt && opt.getAttribute('data-price')) {
        priceInput.value = opt.getAttribute('data-price');
      }
    });
  }

  /* ---------- Client-side form validation ---------- */
  function validateForm(form) {
    const required = form.querySelectorAll('[required]');
    let valid = true;
    required.forEach(function (field) {
      if (!field.value.trim()) {
        valid = false;
        field.style.borderColor = '#d64545';
      } else {
        field.style.borderColor = '';
      }
    });
    form.querySelectorAll('input[type="email"]').forEach(function (em) {
      const val = em.value.trim();
      if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        valid = false;
        em.style.borderColor = '#d64545';
      }
    });
    return valid;
  }

  ['inquiryForm', 'contactForm', 'loginForm', 'addCarForm', 'editCarForm', 'saleForm', 'purchaseForm'].forEach(function (id) {
    const form = document.getElementById(id);
    if (form) {
      form.addEventListener('submit', function (e) {
        if (!validateForm(form)) {
          e.preventDefault();
          alert('Please fill in all required fields correctly.');
        }
      });
    }
  });
})();