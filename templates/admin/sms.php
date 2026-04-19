<?php
/**
 * SMS Centre View
 * HCI/UX: Premium messaging dashboard with real-time balance tracking and live previews.
 */
$pageTitle = 'SMS Centre';
include __DIR__ . '/../layout/header.php';

global $smsLogs, $classes, $totalSent, $recentLogs, $smsBalance;
$logs    = $smsLogs ?? [];
$choices = $classes ?? [];
$balance = $smsBalance ?? ['success' => false];
$base    = defined('APP_BASE') ? APP_BASE : '';
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-3xl); font-weight:900; letter-spacing:-0.04em; color:var(--clr-text);">SMS Centre</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); opacity:0.8;">Manage institutional broadcasts, monitor parent engagement, and track communication credits.</p>
  </div>
  
  <div class="flex gap-4">
    <!-- Balance Card -->
    <?php if ($balance['success']): ?>
        <div class="card flex items-center gap-4" style="padding:12px 24px; border-radius:16px; border:1.5px solid rgba(59, 130, 246, 0.2); background:linear-gradient(to right, #f8faff, #fff);">
           <div style="width:42px; height:42px; border-radius:12px; background:rgba(59, 130, 246, 0.1); color:#3b82f6; display:flex; align-items:center; justify-content:center;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
           </div>
           <div>
              <div style="font-size:10px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Available Credits</div>
              <div style="display:flex; align-items:baseline; gap:4px;">
                 <span style="font-size:18px; font-weight:900; color:var(--clr-text);"><?= htmlspecialchars($balance['currency']) ?> <?= number_format($balance['balance'], 2) ?></span>
                 <span style="font-size:11px; font-weight:700; color:#3b82f6;">~<?= number_format($balance['estimate']) ?> SMS</span>
              </div>
           </div>
        </div>
    <?php else: ?>
        <div class="card flex items-center gap-3" style="padding:12px 24px; border-radius:16px; border-color:rgba(239, 68, 68, 0.1); background:#fff5f5;">
           <div style="width:8px; height:8px; border-radius:50%; background:#ef4444; animation: pulse 2s infinite;"></div>
           <div style="font-size:12px; font-weight:800; color:#ef4444;">API DISCONNECTED</div>
           <a href="<?= $base ?>/admin/settings" class="btn btn-ghost btn-xs" style="font-size:10px; padding:2px 8px;">Link Now</a>
        </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── SMS CONNECTIVITY SECTION ──────────────────────────────── -->
<div class="card mb-10" style="padding: 1.5rem 2rem; border-color: rgba(59, 130, 246, 0.15); border-radius:18px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:36px; height:36px; border-radius:10px; background:rgba(59, 130, 246, 0.1); color:#3b82f6; display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <h3 style="margin:0; font-weight:800; font-size:1rem; letter-spacing:-0.01em;">SMS Connectivity</h3>
                <p style="font-size:11px; color:var(--clr-text-muted); margin:0;">Manage your Zenoph Notify credentials and sender identity.</p>
            </div>
        </div>

        <form method="POST" action="<?= $base ?>/admin/sms" style="flex:1; min-width:300px;">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="update_settings">
            <input type="hidden" name="sms_host" value="api.smsonlinegh.com">
            
            <div style="display:flex; gap:1.5rem; align-items:flex-end;">
                <div style="flex:2;">
                    <label class="form-label" style="text-transform:uppercase; font-size:10px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800; margin-bottom:6px;">Zenoph API Key</label>
                    <div style="position:relative;">
                        <input type="password" name="sms_api_key" class="form-control" value="<?= htmlspecialchars(Config::get('sms_api_key', '')) ?>" style="height:42px; padding-right:40px; font-family:var(--font-mono); font-size:12px; border-radius:10px;">
                        <button type="button" onclick="togglePass(this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer; color:var(--clr-text-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div style="flex:1;">
                    <label class="form-label" style="text-transform:uppercase; font-size:10px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800; margin-bottom:6px;">Sender ID</label>
                    <input type="text" name="sms_sender" class="form-control" value="<?= htmlspecialchars(Config::get('sms_sender', 'Marabel')) ?>" maxlength="11" style="height:42px; font-weight:800; border-radius:10px;">
                </div>

                <button type="submit" class="btn btn-primary" style="height:42px; padding:0 20px; border-radius:10px; font-size:13px; font-weight:700;">Update Settings</button>
            </div>
        </form>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1.4fr 1fr; gap:2.5rem;">
  
  <!-- Step 1: Compose -->
  <div class="card" style="padding:2.5rem; position:relative; overflow:hidden;">
    <div style="position:absolute; top:0; right:0; width:150px; height:150px; background:linear-gradient(135deg, rgba(var(--clr-primary-rgb), 0.05), transparent); border-radius:0 0 0 100%;"></div>
    
    <h3 style="font-weight:900; font-size:1.25rem; margin-bottom:2rem; display:flex; align-items:center; gap:0.75rem; letter-spacing:-0.02em;">
       <div style="width:32px; height:32px; border-radius:8px; background:var(--clr-primary-50); color:var(--clr-primary); display:flex; align-items:center; justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
       </div>
       Compose Broadcast
    </h3>

    <form method="POST" action="<?= $base ?>/admin/sms" id="sms-form" onsubmit="return confirmSMS(event)">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="send_broadcast">

      <div class="mb-6">
        <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800;">Target Audience</label>
        <div style="position:relative;">
            <select name="target" class="form-control" style="height:50px; font-weight:700; padding-left:45px; border-radius:12px; border-color:var(--clr-border);">
                <option value="all">Global Broadcast (All Parents)</option>
                <optgroup label="Class Specific">
                    <?php foreach ($choices as $c): ?>
                    <option value="class_<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
            <div style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--clr-text-muted); opacity:0.6;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
      </div>

      <div class="mb-4">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
            <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800; margin:0;">Message Body</label>
            <div id="counter-badge" style="background:var(--clr-surface-2); padding:2px 10px; border-radius:20px; font-size:10px; font-weight:800; color:var(--clr-text-muted);">
                1 SEGMENT
            </div>
        </div>
        <textarea name="message" id="message" rows="6" class="form-control" placeholder="Describe the announcement, event, or emergency notice..." style="resize:none; padding:1.25rem; font-family:inherit; border-radius:14px; border-color:var(--clr-border); box-shadow:var(--shadow-sm); line-height:1.6;" oninput="updatePreview()"></textarea>
      </div>

      <div class="flex justify-between items-center mb-8 px-2">
        <div id="char-metrics" style="font-size:11px; font-weight:700; color:var(--clr-text-muted); display:flex; gap:12px;">
           <span><strong id="char-count" style="color:var(--clr-text);">0</strong> Characters</span>
           <span style="opacity:0.4;">|</span>
           <span><strong id="segment-count" style="color:var(--clr-text);">1</strong> SMS Segment</span>
        </div>
        <div style="font-size:10px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; opacity:0.6;">Sender ID: <span style="color:var(--clr-primary);"><?= htmlspecialchars(Config::get('sms_sender', 'Marabel')) ?></span></div>
      </div>

      <button type="submit" class="btn btn-primary w-full shadow-purple" style="height:56px; border-radius:14px; font-size:15px; font-weight:800; letter-spacing:-0.01em;">
         Broadcast Message
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" style="margin-left:8px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
      </button>
    </form>
  </div>

  <!-- Step 2: Live Preview -->
  <div class="flex flex-col gap-8">
    <div class="card" style="padding:0; background:#f1f5f9; border:none; border-radius:32px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.1); width:280px; margin:0 auto; overflow:hidden;">
        <!-- Phone Mockup -->
        <div style="background:#111; padding:12px; border-radius:32px 32px 0 0; position:relative;">
            <div style="width:50px; height:4px; background:#333; border-radius:2px; margin:0 auto 10px;"></div>
            <div style="background:#fff; border-radius:12px; padding:10px; min-height:350px; font-family: -apple-system, sans-serif;">
                <div style="display:flex; justify-content:center; margin-bottom:15px; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center;">
                        <svg fill="#999" viewBox="0 0 24 24" width="18" height="18"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                </div>
                <div style="font-size:10px; font-weight:700; color:#888; text-align:center; margin-bottom:12px;">MESSAGE FROM <?= htmlspecialchars(Config::get('sms_sender', 'MARABEL')) ?></div>
                
                <div id="phone-bubble" style="background:#e9e9eb; color:#000; padding:12px; border-radius:15px; font-size:12px; line-height:1.4; max-width:90%; position:relative; word-break:break-word;">
                    Your broadcast message will appear here exactly as parents will see it on their mobile devices...
                </div>
            </div>
        </div>
        <div style="background:#111; padding:20px; border-radius:0 0 32px 32px; text-align:center;">
            <div style="width:30px; height:30px; border-radius:50%; border:2px solid #333; margin:0 auto;"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="card" style="padding:1.5rem; background:#fff; border-radius:20px;">
        <h4 style="margin:0 0 1.25rem 0; font-size:13px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Engagement Statistics</h4>
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap:1rem;">
            <div style="background:var(--clr-surface-2); padding:1.25rem; border-radius:14px; text-align:center;">
                <div style="font-size:20px; font-weight:900; color:var(--clr-text);"><?= number_format($totalSent) ?></div>
                <div style="font-size:10px; font-weight:700; color:var(--clr-success); text-transform:uppercase;">Delivered</div>
            </div>
            <div style="background:var(--clr-surface-2); padding:1.25rem; border-radius:14px; text-align:center;">
                <div style="font-size:20px; font-weight:900; color:var(--clr-text);">0</div>
                <div style="font-size:10px; font-weight:700; color:#888; text-transform:uppercase;">Scheduled</div>
            </div>
        </div>
    </div>
  </div>

</div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulk-delete-form" method="POST" action="<?= $base ?>/admin/sms" style="display:none;">
    <?= CSRF::field() ?>
    <input type="hidden" name="_action" value="delete_logs">
    <div id="bulk-ids-container"></div>
</form>

<form id="single-delete-form" method="POST" action="<?= $base ?>/admin/sms" style="display:none;">
    <?= CSRF::field() ?>
    <input type="hidden" name="_action" value="delete_logs">
    <input type="hidden" name="ids[]" id="single-delete-id">
</form>

<!-- Full Logs -->
<div class="card mt-12" style="padding:0; overflow:hidden; border-radius:24px; box-shadow:var(--shadow-lg);">
  <div style="padding:1.75rem 2.5rem; border-bottom:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:center; background:#fff;">
      <div>
        <h3 style="font-weight:900; margin:0; font-size:1.1rem; letter-spacing:-0.02em;">Communication History</h3>
        <p style="font-size:11px; color:var(--clr-text-muted); margin:4px 0 0 0;">Review every SMS sent from this portal, including automated reports. Use checkboxes for bulk actions.</p>
      </div>
      
      <!-- Bulk Actions -->
      <div id="bulk-actions-bar" style="display:none; align-items:center; gap:1rem; background:rgba(239, 68, 68, 0.05); padding:8px 16px; border-radius:12px; border:1px solid rgba(239, 68, 68, 0.1);">
         <span style="font-size:12px; font-weight:800; color:#ef4444;"><span id="selection-count">0</span> Selected</span>
         <button type="button" class="btn btn-ghost btn-xs text-red-600" onclick="submitBulkDelete()" style="font-weight:900; font-size:10px; border:1.5px solid currentColor; background:#fff;">DELETE SELECTED</button>
      </div>
  </div>
  
  <div style="overflow-x:auto;">
     <table class="table" style="width:100%; border-collapse:separate; border-spacing:0;">
        <thead style="background:var(--clr-surface-2);">
           <tr>
              <th style="padding:1.25rem 1rem 1.25rem 2.5rem; width:40px;">
                  <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" style="cursor:pointer; width:16px; height:16px; accent-color:var(--clr-primary);">
              </th>
              <th style="padding:1.25rem 1rem; text-align:left; font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Recipient</th>
              <th style="padding:1.25rem 1rem; text-align:left; font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Message Content</th>
              <th style="padding:1.25rem 1rem; text-align:center; font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Trigger</th>
              <th style="padding:1.25rem 1rem; text-align:center; font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Status</th>
              <th style="padding:1.25rem 1rem; text-align:right; font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;">Timestamp</th>
              <th style="padding:1.25rem 2.5rem; text-align:right;"></th>
           </tr>
        </thead>
        <tbody>
           <?php if (empty($logs)): ?>
             <tr><td colspan="7" class="py-12 text-center text-muted" style="font-weight:700;">No SMS records located.</td></tr>
           <?php else: ?>
             <?php foreach ($logs as $log): ?>
                <tr style="border-bottom:1px solid var(--clr-border); transition:background 0.2s;" onmouseover="this.style.background='#fcfbff'" onmouseout="this.style.background='white'">
                   <td style="padding:1.25rem 1rem 1.25rem 2.5rem;">
                       <input type="checkbox" name="log_checkbox" value="<?= $log['id'] ?>" onclick="updateSelectionState()" style="cursor:pointer; width:15px; height:15px; accent-color:var(--clr-primary);">
                   </td>
                   <td style="padding:1.25rem 1rem;">
                      <div class="flex items-center gap-3">
                        <div style="width:30px; height:30px; border-radius:50%; background:var(--clr-surface-2); display:flex; align-items:center; justify-content:center; color:var(--clr-primary); font-size:12px;">
                            <svg fill="currentColor" viewBox="0 0 24 24" width="14" height="14"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <span style="font-weight:800; color:var(--clr-text); font-size:14px; letter-spacing:-0.01em;"><?= htmlspecialchars($log['recipient_phone']) ?></span>
                      </div>
                   </td>
                   <td style="padding:1.25rem 1rem; max-width:300px;">
                      <div style="font-size:13px; color:var(--clr-text); line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;" title="<?= htmlspecialchars($log['message']) ?>">
                         <?= htmlspecialchars($log['message']) ?>
                      </div>
                   </td>
                   <td style="padding:1.25rem 1rem; text-align:center;">
                      <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid rgba(0,0,0,0.05); text-transform:uppercase; font-size:9px; font-weight:900; padding:4px 10px; border-radius:6px;">
                         <?= htmlspecialchars($log['sms_type']) ?>
                      </span>
                   </td>
                   <td style="padding:1.25rem 1rem; text-align:center;">
                      <?php if($log['status'] === 'sent'): ?>
                         <div style="display:inline-flex; align-items:center; gap:6px; color:var(--clr-success); font-weight:900; font-size:10px;">
                            <div style="width:6px; height:6px; background:currentColor; border-radius:50%;"></div>
                            DELIVERED
                         </div>
                      <?php else: ?>
                         <div style="display:inline-flex; align-items:center; gap:6px; color:#ef4444; font-weight:900; font-size:10px;">
                            <div style="width:6px; height:6px; background:currentColor; border-radius:50%;"></div>
                            FAILED
                         </div>
                      <?php endif; ?>
                   </td>
                   <td style="padding:1.25rem 1rem; text-align:right;">
                      <div style="font-size:13px; font-weight:700; color:var(--clr-text);"><?= date('M j, Y', strtotime($log['sent_at'])) ?></div>
                      <div style="font-size:11px; font-weight:600; color:var(--clr-text-muted); opacity:0.6;"><?= date('H:i', strtotime($log['sent_at'])) ?></div>
                   </td>
                   <td style="padding:1.25rem 2.5rem; text-align:right;">
                       <button type="button" class="btn btn-ghost btn-xs text-red-600" onclick="submitSingleDelete(<?= $log['id'] ?>)" style="padding:5px; border-radius:8px;">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                       </button>
                   </td>
                </tr>
             <?php endforeach; ?>
           <?php endif; ?>
        </tbody>
     </table>
  </div>
  
  <!-- Pagination Bar -->
  <?php if (($pagination['total'] ?? 0) > 1): ?>
  <div style="padding:1.5rem 2.5rem; background:var(--clr-surface-2); border-top:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:center;">
      <div style="font-size:12px; font-weight:700; color:var(--clr-text-muted);">
          Showing <?= (($pagination['current'] - 1) * $pagination['limit']) + 1 ?> to <?= min($pagination['current'] * $pagination['limit'], $pagination['count']) ?> of <?= $pagination['count'] ?> entries
      </div>
      
      <div class="flex gap-2">
          <?php if ($pagination['current'] > 1): ?>
            <a href="?page=<?= $pagination['current'] - 1 ?>" class="btn btn-ghost btn-sm" style="border:1.5px solid var(--clr-border); background:#fff; font-weight:800; font-size:11px;">PREVIOUS</a>
          <?php endif; ?>
          
          <div style="display:flex; gap:4px;">
              <?php
              $start = max(1, $pagination['current'] - 2);
              $end   = min($pagination['total'], $pagination['current'] + 2);
              for ($i = $start; $i <= $end; $i++):
              ?>
                <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $pagination['current'] ? 'btn-primary' : 'btn-ghost' ?>" style="min-width:32px; font-weight:900; font-size:11px; border:<?= $i === $pagination['current'] ? 'none' : '1.5px solid var(--clr-border); background:#fff;' ?>"><?= $i ?></a>
              <?php endfor; ?>
          </div>
          
          <?php if ($pagination['current'] < $pagination['total']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?>" class="btn btn-ghost btn-sm" style="border:1.5px solid var(--clr-border); background:#fff; font-weight:800; font-size:11px;">NEXT</a>
          <?php endif; ?>
      </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<style>
@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.5; }
  70% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.5; }
}
</style>

<script>
function updatePreview() {
  const text = document.getElementById('message').value;
  const bubble = document.getElementById('phone-bubble');
  const count = text.length;
  const segments = Math.ceil(count / 160) || 1;
  
  // Update Phone Bubble
  if (text.trim().length > 0) {
    bubble.textContent = text;
    bubble.style.color = '#000';
    bubble.style.opacity = '1';
  } else {
    bubble.textContent = "Your broadcast message will appear here exactly as parents will see it on their mobile devices...";
    bubble.style.color = '#888';
    bubble.style.opacity = '0.6';
  }

  // Update Metrics
  document.getElementById('char-count').innerText = count;
  document.getElementById('segment-count').innerText = segments;
  document.getElementById('counter-badge').innerText = segments + (segments === 1 ? ' SEGMENT' : ' SEGMENTS');

  // Warning colors
  if (segments > 3) {
    document.getElementById('counter-badge').style.background = '#fee2e2';
    document.getElementById('counter-badge').style.color = '#ef4444';
  } else {
    document.getElementById('counter-badge').style.background = 'var(--clr-surface-2)';
    document.getElementById('counter-badge').style.color = 'var(--clr-text-muted)';
  }
}

function togglePass(btn) {
    const input = btn.previousElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        btn.style.color = 'var(--clr-primary)';
    } else {
        input.type = 'password';
        btn.style.color = 'var(--clr-text-muted)';
    }
}

function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('input[name="log_checkbox"]');
  checkboxes.forEach(cb => cb.checked = master.checked);
  updateSelectionState();
}

function updateSelectionState() {
  const checkboxes = document.querySelectorAll('input[name="log_checkbox"]:checked');
  const count = checkboxes.length;
  const bar = document.getElementById('bulk-actions-bar');
  const countDisp = document.getElementById('selection-count');
  const master = document.getElementById('select-all');

  if (count > 0) {
    bar.style.display = 'flex';
    countDisp.innerText = count;
  } else {
    bar.style.display = 'none';
  }

  // Update master checkbox state
  const total = document.querySelectorAll('input[name="log_checkbox"]').length;
  master.checked = (count === total && total > 0);
  master.indeterminate = (count > 0 && count < total);
}

function submitSingleDelete(id) {
  confirmAction("Are you sure you want to delete this log entry? This cannot be undone.", () => {
    document.getElementById('single-delete-id').value = id;
    document.getElementById('single-delete-form').submit();
    Loader.show();
  });
}

function submitBulkDelete() {
  const checkboxes = document.querySelectorAll('input[name="log_checkbox"]:checked');
  const ids = Array.from(checkboxes).map(cb => cb.value);

  confirmAction(`Are you sure you want to delete ${ids.length} selected log(s)? This cannot be undone.`, () => {
    const container = document.getElementById('bulk-ids-container');
    container.innerHTML = '';
    ids.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      container.appendChild(input);
    });
    document.getElementById('bulk-delete-form').submit();
    Loader.show();
  });
}

function confirmSMS(e) {
  e.preventDefault();
  const select = document.querySelector('select[name="target"]');
  const target = select.selectedOptions[0].innerText;
  
  if (document.getElementById('message').value.trim().length === 0) {
      alert("Please enter a message content.");
      return false;
  }

  confirmAction(`Confirm Global Broadcast?\n\nRecipient: ${target}\n\nThis will consume SMS credits based on segment count. Proceed?`, () => {
    e.target.submit();
    Loader.show();
  });
  return false;
}

// Initial count
updatePreview();
</script>
