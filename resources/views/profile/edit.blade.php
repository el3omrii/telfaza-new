@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Profile</h1>
        <p class="text-muted text-sm mt-0.5">Manage your account information and security</p>
    </div>
</div>

<div class="max-w-2xl flex flex-col gap-5">

    {{-- ── Avatar + name hero ── --}}
    <div class="bg-surface border border-border rounded-[10px] p-6 flex items-center gap-5">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-accent to-yellow-400 text-black font-display font-bold text-2xl flex items-center justify-center shrink-0 select-none">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <p class="font-display font-bold text-lg">{{ $user->name }}</p>
            <p class="text-muted text-sm">{{ $user->email }}</p>
            <p class="text-muted text-xs mt-1">Member since {{ $user->created_at->format('d M Y') }}</p>
        </div>
    </div>

    {{-- ── Profile info ── --}}
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <p class="font-semibold text-sm">Personal Information</p>
            <p class="text-muted text-xs mt-0.5">Update your name and email address</p>
        </div>
        <div class="p-5">
            @if(session('success') && !session()->has('_password_success') && !session()->has('_delete_success'))
                <div class="mb-4 px-4 py-3 rounded-[10px] text-sm bg-green-500/10 border border-green-500/30 text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                        Full Name
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full pl-10 pr-3.5 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 focus:outline-none transition-colors
                                      {{ $errors->has('name') ? 'border-red-500' : 'border-border focus:border-accent' }}">
                    </div>
                    @error('name')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full pl-10 pr-3.5 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 focus:outline-none transition-colors
                                      {{ $errors->has('email') ? 'border-red-500' : 'border-border focus:border-accent' }}">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Change password ── --}}
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <p class="font-semibold text-sm">Change Password</p>
            <p class="text-muted text-xs mt-0.5">Must be at least 8 characters with mixed case and numbers</p>
        </div>
        <div class="p-5">
            @if(session('_password_success'))
                <div class="mb-4 px-4 py-3 rounded-[10px] text-sm bg-green-500/10 border border-green-500/30 text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4"
                  id="passwordForm">
                @csrf @method('PUT')

                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                        Current Password
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="current_password"
                               class="w-full pl-10 pr-10 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 focus:outline-none transition-colors
                                      {{ $errors->has('current_password') ? 'border-red-500' : 'border-border focus:border-accent' }}"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePwd('current_password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                        New Password
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="password" id="newPassword"
                               class="w-full pl-10 pr-10 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 focus:outline-none transition-colors
                                      {{ $errors->has('password') ? 'border-red-500' : 'border-border focus:border-accent' }}"
                               placeholder="••••••••"
                               oninput="checkStrength(this.value)">
                        <button type="button" onclick="togglePwd('newPassword', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Strength bar --}}
                    <div class="mt-2 flex gap-1" id="strengthBars">
                        <div class="h-1 flex-1 rounded-full bg-border transition-colors" id="bar1"></div>
                        <div class="h-1 flex-1 rounded-full bg-border transition-colors" id="bar2"></div>
                        <div class="h-1 flex-1 rounded-full bg-border transition-colors" id="bar3"></div>
                        <div class="h-1 flex-1 rounded-full bg-border transition-colors" id="bar4"></div>
                    </div>
                    <p id="strengthLabel" class="text-xs text-muted mt-1"></p>
                    @error('password')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="password_confirmation" id="confirmPassword"
                               class="w-full pl-10 pr-10 py-2.5 bg-bg border border-border focus:border-accent rounded-[10px] text-sm text-gray-200 focus:outline-none transition-colors"
                               placeholder="••••••••"
                               oninput="checkMatch()">
                        <button type="button" onclick="togglePwd('confirmPassword', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <p id="matchLabel" class="text-xs mt-1.5 min-h-4"></p>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Danger zone ── --}}
    <div class="bg-surface border border-red-500/20 rounded-[10px] overflow-hidden">
        <div class="px-5 py-4 border-b border-red-500/20">
            <p class="font-semibold text-sm text-red-400">Danger Zone</p>
            <p class="text-muted text-xs mt-0.5">Permanently delete your account and all associated data</p>
        </div>
        <div class="p-5">
            <div id="deletePrompt" class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Delete Account</p>
                    <p class="text-xs text-muted mt-0.5">This action cannot be undone.</p>
                </div>
                <button type="button" onclick="showDeleteConfirm()"
                        class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-sm font-medium rounded-[10px] transition-colors">
                    Delete Account
                </button>
            </div>

            <div id="deleteConfirm" class="hidden">
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <p class="text-sm text-red-300 font-medium">⚠ Are you absolutely sure?</p>
                    <p class="text-xs text-muted mt-1">All your data will be permanently removed. Type <code class="text-red-400 font-mono">DELETE</code> below to confirm.</p>
                </div>
                <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-3">
                    @csrf @method('DELETE')
                    <input name="confirm_delete" placeholder="Type DELETE to confirm"
                           class="w-full px-3.5 py-2.5 bg-bg border border-red-500/40 focus:border-red-500 rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none transition-colors font-mono tracking-widest">
                    <div class="flex gap-2.5">
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-display font-bold text-sm rounded-[10px] transition-colors">
                            Delete My Account
                        </button>
                        <button type="button" onclick="hideDeleteConfirm()"
                                class="px-5 py-2.5 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<script>
// ── Password visibility toggle ─────────────────────────────────────────────
function togglePwd(inputId, btn) {
    const input = document.getElementById(inputId) ?? btn.closest('.relative').querySelector('input');
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('svg').style.opacity = isText ? '1' : '0.4';
}

// ── Password strength meter ────────────────────────────────────────────────
function checkStrength(val) {
    let score = 0;
    if (val.length >= 8)               score++;
    if (/[A-Z]/.test(val))             score++;
    if (/[0-9]/.test(val))             score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    const colors  = ['bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-green-400'];
    const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const txtCols = ['', 'text-red-400', 'text-orange-400', 'text-yellow-400', 'text-green-400'];

    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById(`bar${i}`);
        bar.className = `h-1 flex-1 rounded-full transition-colors ${i <= score ? colors[score - 1] : 'bg-border'}`;
    }

    const lbl = document.getElementById('strengthLabel');
    lbl.textContent  = score > 0 ? labels[score] : '';
    lbl.className    = `text-xs mt-1 ${txtCols[score]}`;

    checkMatch();
}

// ── Password match indicator ───────────────────────────────────────────────
function checkMatch() {
    const pw   = document.getElementById('newPassword').value;
    const conf = document.getElementById('confirmPassword').value;
    const lbl  = document.getElementById('matchLabel');
    if (!conf) { lbl.textContent = ''; return; }
    if (pw === conf) {
        lbl.textContent = '✓ Passwords match';
        lbl.className   = 'text-xs mt-1.5 text-green-400';
    } else {
        lbl.textContent = '✕ Passwords do not match';
        lbl.className   = 'text-xs mt-1.5 text-red-400';
    }
}

// ── Delete confirm toggle ──────────────────────────────────────────────────
function showDeleteConfirm() {
    document.getElementById('deletePrompt').classList.add('hidden');
    document.getElementById('deleteConfirm').classList.remove('hidden');
}
function hideDeleteConfirm() {
    document.getElementById('deleteConfirm').classList.add('hidden');
    document.getElementById('deletePrompt').classList.remove('hidden');
}
</script>
@endpush