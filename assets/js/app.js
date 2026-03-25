// ================================================================
// Uaddara Basic School — SBA Management System
// Global JavaScript Utilities
// ================================================================

'use strict';

/// ── Global Loader ────────────────────────────────────────────────
const Loader = {
  _el: null,
  get el() { return this._el || (this._el = document.getElementById('global-loader')); },
  show() { if (this.el) this.el.style.display = 'block'; },
  hide() { if (this.el) this.el.style.display = 'none'; }
};

// ── Toast Notification System ────────────────────────────────────
const Toast = {
  _container: null,
  _getContainer() {
    if (!this._container) {
      this._container = document.getElementById('toast-container') || this._createContainer();
    }
    return this._container;
  },
  _createContainer() {
    const div = document.createElement('div');
    div.id = 'toast-container';
    document.body.appendChild(div);
    return div;
  },
  _icons: {
    success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="color:var(--clr-success)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    error:   `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="color:var(--clr-danger)"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="color:var(--clr-warning)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
    info:    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="color:var(--clr-info)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
  },
  show(message, type = 'info', duration = 4000) {
    const container = this._getContainer();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      ${this._icons[type] || this._icons.info}
      <span style="flex:1;font-size:var(--text-sm);font-weight:500">${message}</span>
      <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--clr-text-muted);opacity:0.6;padding:4px;" aria-label="Dismiss">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    `;
    container.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('hiding');
      toast.addEventListener('animationend', () => toast.remove());
    }, duration);
  },
  success(m) { this.show(m, 'success'); },
  error(m)   { this.show(m, 'error'); },
  warning(m) { this.show(m, 'warning'); },
  info(m)    { this.show(m, 'info'); }
};

// ── AJAX Helper ──────────────────────────────────────────────────
const Ajax = {
  async post(url, data = {}) {
    const csrf = document.querySelector('input[name="_csrf_token"]');
    if (csrf) data._csrf_token = csrf.value;

    const resp = await fetch(url, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify(data),
    });

    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
  },

  async get(url) {
    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
  },
};

// ── Save Indicator ────────────────────────────────────────────────
const SaveIndicator = {
  show(el) {
    if (!el) return;
    el.classList.add('visible');
    clearTimeout(el._hideTimer);
    el._hideTimer = setTimeout(() => el.classList.remove('visible'), 2500);
  },
};

// ── Sidebar Toggle (Mobile Off-Canvas) ───────────────────────────
const Sidebar = {
  _sidebar: null,
  _backdrop: null,
  _toggle: null,

  init() {
    this._sidebar  = document.getElementById('sidebar');
    this._toggle   = document.getElementById('sidebar-toggle');
    this._backdrop = document.getElementById('sidebar-backdrop');

    if (!this._sidebar) return;

    // Create backdrop if it doesn't exist
    if (!this._backdrop) {
      this._backdrop = document.createElement('div');
      this._backdrop.id = 'sidebar-backdrop';
      this._backdrop.className = 'sidebar-backdrop';
      document.body.appendChild(this._backdrop);
    }

    // Backdrop click → close
    this._backdrop.addEventListener('click', () => this.close());

    // Toggle button
    if (this._toggle) {
      this._toggle.addEventListener('click', () => this.toggle());
    }

    // Nav items on mobile → close sidebar after navigation
    this._sidebar.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', () => {
        if (window.innerWidth <= 768) this.close();
      });
    });
  },

  open() {
    if (!this._sidebar) return;
    this._sidebar.classList.add('open');
    this._backdrop.classList.add('visible');
    document.body.style.overflow = 'hidden';
    if (this._toggle) this._toggle.setAttribute('aria-expanded', 'true');
  },

  close() {
    if (!this._sidebar) return;
    this._sidebar.classList.remove('open');
    this._backdrop.classList.remove('visible');
    document.body.style.overflow = '';
    if (this._toggle) this._toggle.setAttribute('aria-expanded', 'false');
  },

  toggle() {
    if (this._sidebar && this._sidebar.classList.contains('open')) {
      this.close();
    } else {
      this.open();
    }
  }
};

// ── Modal System ─────────────────────────────────────────────────
// Handles both .modal-overlay and .modal-backdrop classes uniformly.
function openModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;

  overlay.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  // Force reflow before adding class for CSS transition to fire
  void overlay.offsetHeight;
  overlay.classList.add('open');

  // Accessibility: focus first interactive element
  const firstInput = overlay.querySelector('input:not([type="hidden"]), select, textarea, button:not(.modal-close)');
  if (firstInput) setTimeout(() => firstInput.focus(), 150);
}

function closeModal(idOrEl) {
  const overlay = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
  if (!overlay) return;

  overlay.classList.remove('open');

  // Wait for the opacity transition to finish before hiding
  overlay.addEventListener('transitionend', function handler(e) {
    // Only act on our opacity transition (not inner child transitions)
    if (e.target !== overlay) return;
    overlay.style.display = 'none';
    document.body.style.overflow = '';
    overlay.removeEventListener('transitionend', handler);
  });

  // Fallback: if transitionend never fires (e.g. display:none before transition)
  setTimeout(() => {
    if (!overlay.classList.contains('open')) {
      overlay.style.display = 'none';
      document.body.style.overflow = '';
    }
  }, 400);
}

// Close modal when clicking the backdrop (not the modal card itself)
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal-backdrop')) {
    closeModal(e.target);
  }
});

// Close on Escape key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open, .modal-backdrop.open').forEach(m => closeModal(m));
  }
});

// ── Confirm Dialog (Premium Modal) ───────────────────────────────
function confirmAction(options, callback) {
  // Support legacy string-only calls: confirmAction('Message', cb)
  const config = typeof options === 'string' 
    ? { message: options, title: 'Are you sure?', confirmText: 'Confirm', type: 'danger' }
    : { title: 'Are you sure?', confirmText: 'Confirm', type: 'danger', ...options };

  const modal   = document.getElementById('modal-confirm');
  const title   = document.getElementById('confirm-title');
  const msg     = document.getElementById('confirm-message');
  const btn     = document.getElementById('confirm-submit-btn');
  const iconBox = document.getElementById('confirm-icon');

  if (!modal || !btn) {
    // Fallback if modal HTML is missing
    if (window.confirm(config.message)) callback();
    return;
  }

  // Set Content
  title.textContent = config.title;
  msg.innerHTML     = config.message;
  btn.textContent   = config.confirmText;
  
  // Set Type/Styling
  btn.className = `btn btn-${config.type || 'danger'}`;
  if (config.type === 'warning') {
    iconBox.style.color = 'var(--clr-warning)';
    iconBox.style.background = 'rgba(245, 158, 11, 0.1)';
  } else if (config.type === 'info') {
    iconBox.style.color = 'var(--clr-info)';
    iconBox.style.background = 'rgba(59, 130, 246, 0.1)';
  } else {
    iconBox.style.color = 'var(--clr-danger)';
    iconBox.style.background = 'rgba(239, 68, 68, 0.1)';
  }

  // Handle Click (One-time)
  const handleConfirm = () => {
    closeModal('modal-confirm');
    btn.removeEventListener('click', handleConfirm);
    callback();
  };

  // Cleanup potential previous listeners
  const newBtn = btn.cloneNode(true);
  btn.parentNode.replaceChild(newBtn, btn);
  newBtn.addEventListener('click', handleConfirm);

  openModal('modal-confirm');
}

// ── Table search/filter ────────────────────────────────────────────
function filterTable(inputId, tableId, colIndex = null) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      const cells = colIndex !== null
        ? [row.cells[colIndex]]
        : Array.from(row.cells);
      const text = cells.map(c => c.textContent.toLowerCase()).join(' ');
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

// ── Proficiency badge renderer ────────────────────────────────────
function proficiencyBadge(level) {
  const labels = {
    1: { cls: 'level-hp', abbr: 'HP',  label: 'Highly Proficient'     },
    2: { cls: 'level-p',  abbr: 'P',   label: 'Proficient'            },
    3: { cls: 'level-ap', abbr: 'AP',  label: 'Approaching Proficiency'},
    4: { cls: 'level-d',  abbr: 'D',   label: 'Developing'            },
    5: { cls: 'level-e',  abbr: 'E',   label: 'Emerging'              },
  };
  const info = labels[level] || labels[5];
  return `<span class="badge ${info.cls}" title="${info.label}">${level} — ${info.abbr}</span>`;
}

// ── Init on DOM ready ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Init sidebar (mobile off-canvas)
  Sidebar.init();

  // Auto-dismiss flash alerts after 5s
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity .4s';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });

  // Mark current nav item as active
  const path = window.location.pathname;
  document.querySelectorAll('.nav-item').forEach(item => {
    const href = item.getAttribute('href');
    if (href && (item.getAttribute('href') === path || path.startsWith(href + '/'))) {
      item.classList.add('active');
    }
  });
});
