<?php
/**
 * Footer Layout Partial
 * Uaddara Basic School — SBA Management System
 */
?>
    </main><!-- /app-main -->
  </div><!-- /content column -->
</div><!-- /app-layout -->

<?php $base = defined('APP_BASE') ? APP_BASE : ''; ?>

<!-- ══ Confirmation Modal (Global) ══════════════════════════════ -->
<div id="modal-confirm" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none; z-index:9999;">
  <div class="modal w-full max-w-sm mx-4" style="transform:translateY(20px); transition:transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
    <div class="modal-body" style="padding:2rem; text-align:center;">
      <div id="confirm-icon" style="width:64px; height:64px; background:rgba(239, 68, 68, 0.1); color:var(--clr-danger); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
      <h3 id="confirm-title" style="font-weight:800; color:var(--clr-text); margin-bottom:0.75rem; font-size:1.25rem;">Are you sure?</h3>
      <p id="confirm-message" class="text-muted" style="font-size:var(--text-sm); line-height:1.6; margin-bottom:2rem;">This action cannot be undone and may affect associated data.</p>
      
      <div class="flex gap-3">
         <button class="btn btn-ghost" style="flex:1; justify-content:center;" onclick="closeModal('modal-confirm')">Cancel</button>
         <button id="confirm-submit-btn" class="btn btn-danger" style="flex:1; justify-content:center;">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast notification container -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- Core JS -->
<script src="<?= $base ?>/assets/js/app.js"></script>

<?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>

<!-- Core JS handled in app.js -->
</body>
</html>
