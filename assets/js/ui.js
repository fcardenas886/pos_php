// assets/js/ui.js — Toasts y diálogos del sistema.
// Reemplazan a alert() / confirm() / prompt() nativos, que bloquean la caja y se ven pobres.
//   toast(mensaje, tipo)                       -> notificación no bloqueante (success|error|warn|info)
//   confirmDialog({title, message, ...})       -> Promise<boolean>
//   promptDialog({title, message, ...})        -> Promise<string|null>
(function () {
  'use strict';

  const ICONS = {
    success: 'fa-circle-check',
    error: 'fa-circle-exclamation',
    warn: 'fa-triangle-exclamation',
    info: 'fa-circle-info',
  };

  let toastRoot = null;
  function ensureToastRoot() {
    if (!toastRoot || !document.body.contains(toastRoot)) {
      toastRoot = document.createElement('div');
      toastRoot.className = 'toast-stack';
      toastRoot.setAttribute('aria-live', 'polite');
      document.body.appendChild(toastRoot);
    }
    return toastRoot;
  }

  window.toast = function (message, type, opts) {
    type = ICONS[type] ? type : 'info';
    opts = opts || {};
    const root = ensureToastRoot();

    const el = document.createElement('div');
    el.className = 'toast toast--' + type;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.innerHTML =
      '<i class="fa-solid ' + ICONS[type] + '"></i>' +
      '<span class="toast__msg"></span>' +
      '<button class="toast__close" aria-label="Cerrar" type="button">&times;</button>';
    el.querySelector('.toast__msg').textContent = message;
    root.appendChild(el);
    requestAnimationFrame(() => el.classList.add('is-in'));

    const ttl = opts.duration != null ? opts.duration : (type === 'error' ? 6000 : 4000);
    let timer = setTimeout(dismiss, ttl);

    function dismiss() {
      clearTimeout(timer);
      el.classList.remove('is-in');
      el.classList.add('is-out');
      setTimeout(() => el.remove(), 250);
    }
    el.querySelector('.toast__close').addEventListener('click', function (e) { e.stopPropagation(); dismiss(); });
    el.addEventListener('click', dismiss);
    return dismiss;
  };

  function buildModal(cfg) {
    const overlay = document.createElement('div');
    const inputType = cfg.isPassword ? 'password' : 'text';
    overlay.className = 'ui-modal';
    overlay.innerHTML =
      '<div class="ui-modal__card" role="dialog" aria-modal="true">' +
        '<h3 class="ui-modal__title"></h3>' +
        '<p class="ui-modal__msg"></p>' +
        (cfg.withInput ? '<input class="form-control ui-modal__input" type="' + inputType + '" autocomplete="off">' : '') +
        '<div class="ui-modal__actions">' +
          '<button class="btn btn-secondary ui-modal__cancel" type="button"></button>' +
          '<button class="btn ui-modal__ok" type="button"></button>' +
        '</div>' +
      '</div>';

    overlay.querySelector('.ui-modal__title').textContent = cfg.title || 'Confirmar';
    const msgEl = overlay.querySelector('.ui-modal__msg');
    if (cfg.message) { msgEl.textContent = cfg.message; } else { msgEl.remove(); }

    const okBtn = overlay.querySelector('.ui-modal__ok');
    okBtn.textContent = cfg.confirmText || 'Aceptar';
    okBtn.classList.add(cfg.danger ? 'btn-danger' : 'btn-primary');
    overlay.querySelector('.ui-modal__cancel').textContent = cfg.cancelText || 'Cancelar';

    const input = overlay.querySelector('.ui-modal__input');
    if (input) {
      input.placeholder = cfg.placeholder || '';
      input.value = cfg.defaultValue || '';
    }
    return { overlay, okBtn, cancelBtn: overlay.querySelector('.ui-modal__cancel'), input };
  }

  function openModal(cfg) {
    return new Promise((resolve) => {
      const { overlay, okBtn, cancelBtn, input } = buildModal(cfg);
      const cancelValue = cfg.withInput ? null : false;
      document.body.appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add('is-in'));
      const prevFocus = document.activeElement;

      function close(result) {
        overlay.classList.remove('is-in');
        setTimeout(() => overlay.remove(), 200);
        document.removeEventListener('keydown', onKey);
        if (prevFocus && typeof prevFocus.focus === 'function') prevFocus.focus();
        resolve(result);
      }
      function onKey(e) {
        if (e.key === 'Escape') { e.preventDefault(); close(cancelValue); }
        else if (e.key === 'Enter' && (cfg.withInput || document.activeElement !== cancelBtn)) {
          e.preventDefault(); okBtn.click();
        }
      }

      okBtn.addEventListener('click', () => close(cfg.withInput ? (input.value.trim() || null) : true));
      cancelBtn.addEventListener('click', () => close(cancelValue));
      overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) close(cancelValue); });
      document.addEventListener('keydown', onKey);
      setTimeout(() => (input || okBtn).focus(), 60);
    });
  }

  window.confirmDialog = function (opts) {
    return openModal(Object.assign({ confirmText: 'Aceptar', cancelText: 'Cancelar' }, opts || {}, { withInput: false }));
  };
  window.promptDialog = function (opts) {
    return openModal(Object.assign({ confirmText: 'Guardar', cancelText: 'Cancelar' }, opts || {}, { withInput: true }));
  };
  window.supervisorPromptDialog = function (opts) {
    return openModal(Object.assign({
      title: '🛡️ Autorización de Supervisor',
      message: 'Ingresa la clave de un Supervisor o Administrador para autorizar esta operación:',
      placeholder: '••••••••',
      confirmText: 'Autorizar',
      cancelText: 'Cancelar',
      isPassword: true,
      danger: opts && opts.danger ? true : false
    }, opts || {}, { withInput: true }));
  };
})();
