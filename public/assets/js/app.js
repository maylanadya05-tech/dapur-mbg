/**
 * Dapur MBG — Main JavaScript File v2.0
 * 
 * Micro-interactions implemented:
 * 1. Ripple effect on all .btn elements
 * 2. Stagger entrance animation via IntersectionObserver
 * 3. Auto-dismiss flash alerts with countdown progress bar
 * 4. Live datetime clock
 * 5. Confirm delete handler
 * 6. Client-side table search
 */

document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();

  initLiveClock();
  initAlertAutoClose();
  initDeleteConfirmation();
  initRippleEffect();
  initStaggerEntrance();
});

/* ─────────────────────────────────────────────────────────────
   1. RIPPLE EFFECT — Micro-interaction on all .btn clicks
   ───────────────────────────────────────────────────────────── */
function initRippleEffect() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn');
    if (!btn) return;

    // Calculate ripple position relative to button
    const rect   = btn.getBoundingClientRect();
    const size   = Math.max(rect.width, rect.height) * 2;
    const x      = e.clientX - rect.left - size / 2;
    const y      = e.clientY - rect.top  - size / 2;

    // Create ripple wave element
    const wave = document.createElement('span');
    wave.classList.add('ripple-wave');
    wave.style.cssText = `
      width:  ${size}px;
      height: ${size}px;
      left:   ${x}px;
      top:    ${y}px;
    `;

    btn.appendChild(wave);

    // Clean up after animation completes
    wave.addEventListener('animationend', () => wave.remove(), { once: true });
  });
}

/* ─────────────────────────────────────────────────────────────
   2. STAGGER ENTRANCE — IntersectionObserver for cards
   ───────────────────────────────────────────────────────────── */
function initStaggerEntrance() {
  // Target: stat cards, content cards, batch cards
  const staggerSelectors = [
    '.stats-grid > .stat-card',
    '.content-grid > .card',
    '.content-grid > div',
    '.batch-grid > .batch-card',
    '.card',
  ];

  // Mark elements ready for stagger
  const allCards = document.querySelectorAll(
    '.stats-grid > .stat-card, .batch-grid > .batch-card'
  );

  allCards.forEach(el => {
    el.classList.add('stagger-ready');
  });

  // IntersectionObserver — fires when element enters viewport
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          el.classList.remove('stagger-ready');
          el.classList.add('stagger-visible');
          observer.unobserve(el); // only animate once
        }
      });
    },
    { threshold: 0.1, rootMargin: '0px 0px -32px 0px' }
  );

  allCards.forEach(el => observer.observe(el));

  // Stagger content-grid cards with simple delay on page load
  const contentCards = document.querySelectorAll('.content-grid > .card, .content-grid > div');
  contentCards.forEach((card, index) => {
    card.style.opacity    = '0';
    card.style.transform  = 'translateY(16px)';
    card.style.transition = `opacity 0.4s ease ${index * 80}ms, transform 0.4s ease ${index * 80}ms`;

    // Trigger after micro-delay so CSS transition fires
    requestAnimationFrame(() => {
      setTimeout(() => {
        card.style.opacity   = '';
        card.style.transform = '';
      }, 50);
    });
  });
}

/* ─────────────────────────────────────────────────────────────
   3. FLASH ALERTS — Auto-close with countdown bar (5s)
   ───────────────────────────────────────────────────────────── */
function initAlertAutoClose() {
  const alerts = document.querySelectorAll('.alert');

  alerts.forEach(alert => {
    // Manual close button
    const closeBtn = alert.querySelector('button');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => dismissAlert(alert));
    }

    // Auto dismiss after 5s (matches CSS animation-duration on ::before)
    const timer = setTimeout(() => {
      if (alert.parentNode) dismissAlert(alert);
    }, 5000);

    // Cancel auto-dismiss if user hovers (reads it)
    alert.addEventListener('mouseenter', () => clearTimeout(timer));
    alert.addEventListener('mouseleave', () => {
      setTimeout(() => { if (alert.parentNode) dismissAlert(alert); }, 2000);
    });
  });
}

function dismissAlert(alert) {
  alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, margin 0.4s ease, padding 0.4s ease';
  alert.style.opacity    = '0';
  alert.style.transform  = 'translateY(-6px)';
  alert.style.maxHeight  = '0';
  alert.style.marginBottom = '0';
  alert.style.paddingTop   = '0';
  alert.style.paddingBottom = '0';
  setTimeout(() => alert.remove(), 400);
}

/* ─────────────────────────────────────────────────────────────
   4. LIVE DATETIME CLOCK
   ───────────────────────────────────────────────────────────── */
function initLiveClock() {
  const clockEl = document.getElementById('liveClock');
  if (!clockEl) return;

  const update = () => {
    const now = new Date();
    clockEl.textContent = now.toLocaleDateString('id-ID', {
      weekday: 'long',
      year:    'numeric',
      month:   'long',
      day:     'numeric',
      hour:    '2-digit',
      minute:  '2-digit',
      second:  '2-digit',
      hour12:  false,
    });
  };

  update();
  setInterval(update, 1000);
}

/* ─────────────────────────────────────────────────────────────
   5. CONFIRM DELETE
   ───────────────────────────────────────────────────────────── */
function initDeleteConfirmation() {
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.btn-delete-confirm');
    if (!deleteBtn) return;
    const message = deleteBtn.getAttribute('data-message')
      || 'Apakah Anda yakin ingin menghapus data ini?';
    if (!confirm(message)) e.preventDefault();
  });
}

/* ─────────────────────────────────────────────────────────────
   6. CLIENT-SIDE TABLE SEARCH UTILITY
   Usage: oninput="filterTable('tableId', this.value)"
   ───────────────────────────────────────────────────────────── */
function filterTable(tableId, query) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const rows       = table.querySelectorAll('tbody tr');
  const cleanQuery = query.toLowerCase().trim();
  let   visibleCount = 0;

  rows.forEach(row => {
    if (row.classList.contains('empty-row')) return;

    const match = [...row.querySelectorAll('td')]
      .some(cell => cell.textContent.toLowerCase().includes(cleanQuery));

    row.style.display = match ? '' : 'none';
    if (match) visibleCount++;
  });

  // Show/hide empty state row if present
  const emptyRow = table.querySelector('tbody .empty-row');
  if (emptyRow) {
    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
  }
}
