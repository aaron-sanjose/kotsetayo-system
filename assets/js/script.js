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