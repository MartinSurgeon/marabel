<?php
/**
 * SMS Centre View
 * HCI/UX: Direct messaging interface, character counter, delivery logs.
 */
$pageTitle = 'SMS Centre';
include __DIR__ . '/../layout/header.php';

global $smsLogs, $classes, $totalSent, $recentLogs;
$logs    = $smsLogs ?? [];
$choices = $classes ?? [];
$base    = defined('APP_BASE') ? APP_BASE : '';
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">SMS Centre</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Broadcast messages to parents, send termly report notifications, and monitor communication history.
    </p>
  </div>
  <div class="flex gap-4">
    <div class="card flex items-center gap-3" style="padding:10px 20px; border-radius:var(--radius-lg); border-color:var(--clr-primary-100); background:rgba(var(--clr-primary-rgb), 0.05); border:1px solid rgba(var(--clr-primary-rgb), 0.1);">
       <div style="width:10px; height:10px; border-radius:5px; background:var(--clr-success);"></div>
       <div style="font-size:12px; font-weight:800; color:var(--clr-text);">API LINKED</div>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns: 1.5fr 1fr; gap:2rem;">
  
  <!-- Compose Message -->
  <div class="card" style="padding:2rem;">
    <h3 style="font-weight:800; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="color:var(--clr-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
       Compose Broadcast
    </h3>

    <form method="POST" action="<?= $base ?>/admin/sms" id="sms-form" onsubmit="return confirmSMS(event)">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="send_broadcast">

      <div class="mb-6">
        <label style="display:block; font-size:13px; font-weight:800; color:var(--clr-text-muted); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Recipients</label>
        <select name="target" class="form-control" style="font-weight:700;">
          <option value="all">All Parents (Global Broadcast)</option>
          <optgroup label="Specific Classes">
            <?php foreach ($choices as $c): ?>
              <option value="class_<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </div>

      <div class="mb-4">
        <label style="display:block; font-size:13px; font-weight:800; color:var(--clr-text-muted); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Message Content</label>
        <textarea name="message" id="message" rows="5" class="form-control" placeholder="Type your message here..." style="resize:none; padding:1.25rem; font-family:inherit; scrollbar-width:thin;" oninput="updateCounter()"></textarea>
      </div>

      <div class="flex justify-between items-center mb-8">
        <div style="font-size:12px; font-weight:700; color:var(--clr-text-muted);">
           <span id="char-count">0</span> characters | <span id="segment-count">1</span> segment(s)
        </div>
        <div style="font-size:11px; font-weight:600; color:var(--clr-text-muted); text-transform:uppercase;">Max 160 per SMS</div>
      </div>

      <button type="submit" class="btn btn-primary w-full" style="padding:1rem;">
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" style="margin-right:0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
         Send Broadcast Now
      </button>
    </form>
  </div>

  <!-- Stats & Side Info -->
  <div class="flex flex-col gap-6">
    <div class="card" style="background:linear-gradient(135deg, var(--clr-primary), var(--clr-primary-700)); color:var(--clr-white); border:none;">
       <div style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; opacity:0.8; margin-bottom:1.5rem;">Total Messages Sent</div>
       <div style="font-size:2.5rem; font-weight:900; letter-spacing:-0.03em; margin-bottom:0.5rem;"><?= number_format($totalSent) ?></div>
       <div style="font-size:12px; font-weight:700; opacity:0.8;">Across all communication types</div>
    </div>

    <div class="card">
       <h4 style="font-weight:800; margin-bottom:1rem; font-size:14px; color:var(--clr-text);">Recent Activity</h4>
       <div class="flex flex-col gap-4">
         <?php if (empty($recentLogs)): ?>
           <div class="text-muted text-center py-4" style="font-size:13px;">No recent activity</div>
         <?php else: ?>
           <?php foreach (array_slice($logs, 0, 5) as $log): ?>
              <div class="flex items-start gap-3">
                 <div style="width:32px; height:32px; border-radius:50%; background:var(--clr-surface-2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14" style="color:var(--clr-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                 </div>
                 <div style="flex:1;">
                    <div class="flex justify-between items-center mb-1">
                       <span style="font-size:12px; font-weight:800; color:var(--clr-text);"><?= htmlspecialchars($log['recipient_phone']) ?></span>
                       <span style="font-size:10px; font-weight:700; color:var(--clr-text-muted); opacity:0.7;"><?= date('H:i', strtotime($log['sent_at'])) ?></span>
                    </div>
                    <p style="font-size:11px; color:var(--clr-text-muted); margin:0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:1; line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">
                      <?= htmlspecialchars($log['message']) ?>
                    </p>
                 </div>
              </div>
           <?php endforeach; ?>
         <?php endif; ?>
       </div>
    </div>
  </div>

</div>

<!-- Full Logs -->
<div class="card mt-12" style="padding:0; overflow:hidden;">
  <div style="padding:1.5rem 2rem; border-bottom:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:center;">
     <h3 style="font-weight:800; margin:0;">Communication History</h3>
  </div>
  <div style="overflow-x:auto;">
     <table class="table" style="width:100%; border-collapse:collapse;">
        <thead style="background:var(--clr-surface-2); border-bottom:2px solid var(--clr-border);">
           <tr>
              <th style="padding:1rem 2rem; text-align:left; font-size:12px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Recipient</th>
              <th style="padding:1rem; text-align:left; font-size:12px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Message</th>
              <th style="padding:1rem; text-align:left; font-size:12px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Type</th>
              <th style="padding:1rem; text-align:left; font-size:12px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Status</th>
              <th style="padding:1rem 2rem; text-align:right; font-size:12px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Sent At</th>
           </tr>
        </thead>
        <tbody>
           <?php if (empty($logs)): ?>
             <tr><td colspan="5" class="py-12 text-center text-muted" style="font-weight:700;">No SMS logs found.</td></tr>
           <?php else: ?>
             <?php foreach ($logs as $log): ?>
                <tr style="border-bottom:1px solid var(--clr-border);">
                   <td style="padding:1rem 2rem; font-weight:800; color:var(--clr-text);"><?= htmlspecialchars($log['recipient_phone']) ?></td>
                   <td style="padding:1rem; max-width:400px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:13px; color:var(--clr-text-muted);"><?= htmlspecialchars($log['message']) ?></td>
                   <td style="padding:1rem;">
                      <span class="badge" style="background:var(--clr-surface-2); color:var(--clr-text); text-transform:uppercase; font-size:10px; font-weight:800; padding:4px 8px;">
                         <?= htmlspecialchars($log['sms_type']) ?>
                      </span>
                   </td>
                   <td style="padding:1rem;">
                      <?php if($log['status'] === 'sent'): ?>
                         <span class="badge badge-success" style="font-size:10px; padding:4px 10px;">SENT</span>
                      <?php else: ?>
                         <span class="badge badge-danger" style="font-size:10px; padding:4px 10px;">FAILED</span>
                      <?php endif; ?>
                   </td>
                   <td style="padding:1rem 2rem; text-align:right; font-size:12px; font-weight:700; color:var(--clr-text-muted);"><?= date('M j, Y H:i', strtotime($log['sent_at'])) ?></td>
                </tr>
             <?php endforeach; ?>
           <?php endif; ?>
        </tbody>
     </table>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<script>
function updateCounter() {
  const text = document.getElementById('message').value;
  const count = text.length;
  const segments = Math.ceil(count / 160) || 1;
  
  document.getElementById('char-count').innerText = count;
  document.getElementById('segment-count').innerText = segments;
  
  const counterEl = document.getElementById('char-count').parentElement;
  if (count > 480) { // arbitrary warning threshold (3 segments)
    counterEl.style.color = 'var(--clr-danger)';
  } else {
    counterEl.style.color = 'var(--clr-text-muted)';
  }
}

function confirmSMS(e) {
  e.preventDefault();
  const target = document.querySelector('select[name="target"]').selectedOptions[0].innerText;
  confirmAction(`Are you sure you want to send this broadcast to ${target}?\n\nThis will consume SMS credits.`, () => {
    e.target.submit();
    Loader.show();
  });
  return false;
}
</script>
