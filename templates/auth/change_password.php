<?php
/**
 * Change Password Template
 */
$pageTitle = 'Change Password';
include __DIR__ . '/../layout/header.php';

$base = defined('APP_BASE') ? APP_BASE : '';
?>

<div class="max-w-md mx-auto mt-10 animate-fade-in">
    <div class="card p-8 shadow-xl border border-gray-100">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="32" height="32">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900 leading-tight">Secure Your Account</h1>
            <p class="text-gray-500 text-sm mt-2 font-medium">Create a strong password to protect your data.</p>
        </div>

        <form action="<?= $base ?>/profile/password" method="POST" class="space-y-6">
            <?= CSRF::field() ?>
            
            <div>
                <label for="current_password" class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Current Password</label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all outline-none font-semibold text-gray-700"
                           placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="new_password" class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">New Password</label>
                <input type="password" name="new_password" id="new_password" required minlength="8"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all outline-none font-semibold text-gray-700"
                       placeholder="Min. 8 characters">
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all outline-none font-semibold text-gray-700"
                       placeholder="Repeat new password">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-xl shadow-lg shadow-purple-500/30 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                    Update Security
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </div>
            
            <a href="javascript:history.back()" class="block text-center text-sm font-bold text-gray-500 hover:text-purple-600 transition-colors py-2">
                Cancel & Go Back
            </a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
