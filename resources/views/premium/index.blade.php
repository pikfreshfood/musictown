@extends('layouts.user')

@section('title', 'Withdrawal')
@section('page-title', 'Withdrawal')

@section('content')
        <section style="max-width:700px;margin:0 auto;">

            {{-- Messages --}}
            @if (session('success'))
                <p class="form-message success-message">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <div class="form-message error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Withdrawal Form --}}
            <div class="section-heading">
                <p class="eyebrow">Payout</p>
                <h2 class="small-heading">Request withdrawal</h2>
            </div>

            <div class="auth-card" id="withdrawCard">
                <p style="color:var(--gold);font-size:0.85rem;font-weight:600;margin:0 0 4px;">&#9432; Verified account name will be used as payee name automatically</p>

                <label>
                    Account Number
                    <input type="text" id="account" maxlength="10" placeholder="10-digit account number" autocomplete="off">
                    <span id="verifyFeedback" style="font-size:0.8rem;color:var(--muted);font-weight:400;"></span>
                    <span id="verifiedBlock" style="display:none;align-items:center;gap:8px;padding:10px 14px;border-radius:40px;background:rgba(72,199,142,0.12);border:1px solid rgba(72,199,142,0.35);font-size:0.85rem;">
                        &#10003; Verified Payee: <strong id="verifiedAccountName" style="color:#60a5fa;"></strong>
                    </span>
                    <div id="manualNameWrap" style="display:none;margin-top:8px;padding:10px 14px;border-radius:8px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.3);">
                        <span style="color:var(--blue-soft);font-size:0.8rem;font-weight:600;display:block;margin-bottom:6px;">&#9432; Could not auto-verify. Enter name manually:</span>
                        <input type="text" id="manualName" placeholder="Full name as registered with bank" style="width:100%;background:rgba(2,6,14,0.82);border:1px solid rgba(59,130,246,0.3);border-radius:8px;color:white;min-height:44px;padding:0 14px;outline:0;">
                    </div>
                </label>

                <label>
                    Select Bank
                    <div class="custom-select-container" id="bankSelector">
                        <div class="custom-select-trigger" id="bankTrigger">
                            <span id="selectedBankText" style="color:var(--muted);font-weight:400;">Choose bank</span>
                            <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="custom-options" id="bankOptions">
                            <div class="search-box">
                                <input type="text" id="bankSearch" placeholder="Search bank...">
                            </div>
                            <div id="banksList"></div>
                        </div>
                    </div>
                    <input type="hidden" id="bankCodeHidden">
                    <input type="hidden" id="bankNameHidden">
                </label>

                <label>
                    Amount (₦)
                    <input type="number" id="amount" placeholder="Minimum ₦10,000" min="10000" max="{{ $user->balance }}" step="0.01">
                    <span style="font-size:0.8rem;color:var(--muted);font-weight:400;">Minimum withdrawal: ₦10,000</span>
                </label>

                <div class="withdrawal-info">
                    <span>Balance: <strong>₦{{ number_format($user->balance, 2) }}</strong></span>
                    @if ($user->tier !== 'tier0')
                        @php
                            $remaining = match ($user->tier) {
                                'tier1' => 1 - $user->withdrawals_used,
                                'tier2' => 2 - $user->withdrawals_used,
                                'tier3' => 3 - $user->withdrawals_used,
                                default => 0,
                            };
                        @endphp
                        <span>Withdrawals left: <strong>{{ max(0, $remaining) }}</strong></span>
                    @endif
                </div>

                <div id="errorMsg" class="form-message error-message" style="display:none;"></div>

                <div style="margin-top:4px;">
                    <button class="button auth-submit" id="withdrawBtn" type="button" style="width:100%;">Withdraw</button>
                </div>
            </div>

        {{-- Upgrade Modal --}}
        <div id="upgrade-modal" class="modal-overlay">
            <div class="modal-box">
                <h3>Upgrade Required</h3>
                <p>Your current account tier does not support withdrawals. Please upgrade to Tier 1 or higher to withdraw.</p>
                <div class="modal-actions">
                    <button class="button btn-outline" onclick="hideUpgradeModal()" style="flex:1;">Cancel</button>
                    <a class="button" href="{{ route('profile.upgrade.form') }}" style="flex:1;text-decoration:none;text-align:center;">Continue to Upgrade</a>
                </div>
            </div>
        </div>

        </section>

    <script>
        // ---------- CONFIG ----------
        const API_BANKS_URL = "{{ route('banks') }}";

        let banksData = [];
        let selectedBankObj = null;
        let verifiedAccountName = null;
        let verificationTimeout = null;

        const balance = {{ $user->balance }};
        const csrfToken = '{{ csrf_token() }}';
        const withdrawRoute = '{{ route('profile.withdraw') }}';
        const verifyRoute = '{{ route('verify.account') }}';

        const accountInput = document.getElementById('account');
        const amountInput = document.getElementById('amount');
        const withdrawBtn = document.getElementById('withdrawBtn');
        const errorDiv = document.getElementById('errorMsg');
        const verifiedBlock = document.getElementById('verifiedBlock');
        const verifiedNameSpan = document.getElementById('verifiedAccountName');
        const verifyFeedback = document.getElementById('verifyFeedback');
        const bankTrigger = document.getElementById('bankTrigger');
        const bankOptionsDiv = document.getElementById('bankOptions');
        const selectedBankSpan = document.getElementById('selectedBankText');
        const bankCodeHidden = document.getElementById('bankCodeHidden');
        const bankNameHidden = document.getElementById('bankNameHidden');
        const banksListContainer = document.getElementById('banksList');
        const bankSearchInput = document.getElementById('bankSearch');
        const manualNameWrap = document.getElementById('manualNameWrap');
        const manualNameInput = document.getElementById('manualName');

        function showError(msg) {
            errorDiv.style.display = 'block';
            errorDiv.innerHTML = '<p>' + msg + '</p>';
            setTimeout(function() {
                if (errorDiv.innerHTML.includes(msg)) errorDiv.style.display = 'none';
            }, 5000);
        }

        function clearError() {
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';
        }

        function resetVerification() {
            if (verificationTimeout) clearTimeout(verificationTimeout);
            verifiedAccountName = null;
            verifiedBlock.style.display = 'none';
            verifiedNameSpan.innerText = '';
            if (verifyFeedback) verifyFeedback.innerText = '';
            if (manualNameWrap) manualNameWrap.style.display = 'none';
            if (manualNameInput) manualNameInput.value = '';
        }

        async function callVerifyAccount(accountNumber, bankCode) {
            if (!accountNumber || accountNumber.length !== 10 || !bankCode) {
                resetVerification();
                return;
            }
            verifyFeedback.innerHTML = 'Verifying account details...';
            verifiedBlock.style.display = 'none';
            try {
                var response = await fetch(verifyRoute, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ account_number: accountNumber, bank_code: bankCode })
                });
                var data = await response.json();
                if (response.ok && data.status && data.account_name) {
                    verifiedAccountName = data.account_name;
                    verifiedNameSpan.innerText = data.account_name;
                    verifiedBlock.style.display = 'flex';
                    manualNameWrap.style.display = 'none';
                    manualNameInput.value = '';
                    verifyFeedback.innerHTML = 'Account verified successfully';
                    setTimeout(function() {
                        if (verifyFeedback.innerText === 'Account verified successfully')
                            verifyFeedback.innerText = '';
                    }, 2500);
                    clearError();
                } else {
                    verifyFeedback.innerHTML = 'Auto-verify unavailable. Enter name below or check details.';
                    verifiedAccountName = null;
                    verifiedBlock.style.display = 'none';
                    manualNameWrap.style.display = 'block';
                }
            } catch (err) {
                console.error('Verification network error:', err);
                verifyFeedback.innerHTML = 'Network error. Please try again.';
                verifiedAccountName = null;
                verifiedBlock.style.display = 'none';
            }
        }

        function triggerVerification(accountNum, bankCode) {
            if (verificationTimeout) clearTimeout(verificationTimeout);
            if (!bankCode || !accountNum || accountNum.length !== 10) {
                resetVerification();
                return;
            }
            verifyFeedback.innerText = 'Waiting, verifying in 5 sec...';
            verificationTimeout = setTimeout(function() {
                if (accountInput.value.length === 10 && selectedBankObj && selectedBankObj.code === bankCode) {
                    callVerifyAccount(accountNum, bankCode);
                } else {
                    resetVerification();
                    verifyFeedback.innerText = '';
                }
            }, 5000);
        }

        async function fetchBanksAndRender() {
            try {
                var response = await fetch(API_BANKS_URL, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Could not load banks');
                var data = await response.json();
                if (Array.isArray(data) && data.length > 0) {
                    banksData = data.sort(function(a, b) { return a.name.localeCompare(b.name); });
                    renderBankList(banksData);
                } else {
                    fallbackBanks();
                }
            } catch (err) {
                console.warn('API banks failed, using fallback', err);
                fallbackBanks();
            }
        }

        function fallbackBanks() {
            banksData = [
                { name: "Access Bank", code: "044" }, { name: "Access Diamond Bank", code: "063" },
                { name: "Citibank Nigeria", code: "023" }, { name: "Ecobank Nigeria", code: "050" },
                { name: "Fidelity Bank", code: "070" }, { name: "First Bank of Nigeria", code: "011" },
                { name: "First City Monument Bank (FCMB)", code: "214" }, { name: "Globus Bank", code: "001" },
                { name: "Guaranty Trust Bank (GTBank)", code: "058" }, { name: "Heritage Bank", code: "030" },
                { name: "Jaiz Bank", code: "301" }, { name: "Keystone Bank", code: "082" },
                { name: "Kuda Microfinance Bank", code: "50211" }, { name: "Lotus Bank", code: "303" },
                { name: "Moniepoint Microfinance Bank", code: "50515" }, { name: "OPay", code: "100004" },
                { name: "Paga", code: "100002" }, { name: "Palmpay", code: "100003" },
                { name: "Parallex Bank", code: "526" }, { name: "Polaris Bank", code: "076" },
                { name: "PremiumTrust Bank", code: "105" }, { name: "Providus Bank", code: "101" },
                { name: "Sparkle Microfinance Bank", code: "51310" }, { name: "Stanbic IBTC Bank", code: "221" },
                { name: "Standard Chartered Bank", code: "068" }, { name: "Sterling Bank", code: "232" },
                { name: "Suntrust Bank", code: "100" }, { name: "TAJ Bank", code: "302" },
                { name: "Titan Trust Bank", code: "102" }, { name: "UBA (United Bank for Africa)", code: "033" },
                { name: "Union Bank of Nigeria", code: "032" }, { name: "Unity Bank", code: "215" },
                { name: "VFD Microfinance Bank", code: "50468" }, { name: "Wema Bank", code: "035" },
                { name: "Zenith Bank", code: "057" }
            ];
            renderBankList(banksData);
        }

        function renderBankList(banks) {
            banksListContainer.innerHTML = '';
            banks.forEach(function(bank) {
                var optionDiv = document.createElement('div');
                optionDiv.className = 'option';
                var initials = bank.name.split(' ').map(function(w) { return w[0]; }).join('').slice(0, 2).toUpperCase();
                optionDiv.innerHTML =
                    '<div class="bank-logo">' + initials + '</div>' +
                    '<div style="flex:1">' +
                        '<div style="font-weight:600;font-size:14px;">' + bank.name + '</div>' +
                        '<div style="font-size:11px;color:var(--muted);">Code: ' + bank.code + '</div>' +
                    '</div>';
                optionDiv.addEventListener('click', function(e) {
                    e.stopPropagation();
                    selectBank(bank);
                    closeDropdown();
                });
                banksListContainer.appendChild(optionDiv);
            });
        }

        accountInput.addEventListener('input', function(e) {
            var val = e.target.value.replace(/\D/g, '').slice(0, 10);
            accountInput.value = val;
            if (val.length === 10 && selectedBankObj && selectedBankObj.code) {
                triggerVerification(val, selectedBankObj.code);
            } else {
                resetVerification();
                if (val.length > 0 && val.length < 10) verifyFeedback.innerText = 'Enter 10 digits';
                else if (val.length === 0) verifyFeedback.innerText = '';
                else if (val.length === 10 && !selectedBankObj) verifyFeedback.innerText = 'Select a bank first';
            }
        });

        manualNameInput.addEventListener('input', function() {
            var name = this.value.trim();
            if (name.length > 0) {
                verifiedAccountName = name;
                verifiedNameSpan.innerText = name;
                verifiedBlock.style.display = 'flex';
            } else {
                verifiedAccountName = null;
                verifiedBlock.style.display = 'none';
            }
        });

        function selectBank(bank) {
            selectedBankObj = bank;
            selectedBankSpan.innerText = bank.name;
            selectedBankSpan.style.color = 'white';
            selectedBankSpan.style.fontWeight = '600';
            bankCodeHidden.value = bank.code;
            bankNameHidden.value = bank.name;
            resetVerification();
            var accNum = accountInput.value;
            if (accNum.length === 10 && bank.code) {
                triggerVerification(accNum, bank.code);
            }
        }

        function toggleDropdown() {
            bankOptionsDiv.classList.toggle('active');
            var chevron = document.querySelector('#bankTrigger .chevron');
            if (chevron) chevron.style.transform = bankOptionsDiv.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
            if (bankOptionsDiv.classList.contains('active')) {
                bankSearchInput.focus();
                filterBanks('');
            }
        }

        function closeDropdown() {
            bankOptionsDiv.classList.remove('active');
            var chevron = document.querySelector('#bankTrigger .chevron');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }

        function filterBanks(query) {
            var filtered = banksData.filter(function(b) {
                return b.name.toLowerCase().includes(query.toLowerCase()) || b.code.toLowerCase().includes(query.toLowerCase());
            });
            renderBankList(filtered);
        }

        bankTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });

        document.addEventListener('click', function(e) {
            var selector = document.getElementById('bankSelector');
            if (selector && !selector.contains(e.target)) closeDropdown();
        });

        bankSearchInput.addEventListener('input', function(e) {
            filterBanks(e.target.value);
        });

        amountInput.addEventListener('input', function() {
            if (parseFloat(amountInput.value) > balance && amountInput.value !== '') {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<p>Amount exceeds available balance</p>';
            } else {
                if (errorDiv.innerHTML.includes('exceeds')) errorDiv.style.display = 'none';
            }
        });

        const userTier = '{{ $user->tier }}';

        function showUpgradeModal() {
            document.getElementById('upgrade-modal').classList.add('show');
        }
        function hideUpgradeModal() {
            document.getElementById('upgrade-modal').classList.remove('show');
        }

        document.getElementById('withdrawBtn').addEventListener('click', async function() {
            clearError();

            var account = accountInput.value.trim();
            var bankName = selectedBankObj ? selectedBankObj.name : '';
            var amount = parseFloat(amountInput.value.trim());
            if (!account || account.length !== 10) { showError('Valid 10-digit account number required.'); return; }
            if (!selectedBankObj) { showError('Please select a bank.'); return; }
            if (isNaN(amount) || amount < 10000) { showError('Minimum withdrawal is ₦10,000.'); return; }
            if (amount > balance) { showError('Insufficient balance.'); return; }
            if (!verifiedAccountName || verifiedAccountName.trim() === '') { showError('Account verification required.'); return; }

            if (userTier === 'tier0') {
                showUpgradeModal();
                return;
            }

            try {
                var res = await fetch(withdrawRoute, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        bank_name: bankName,
                        account_number: account,
                        account_name: verifiedAccountName,
                        amount: amount,
                    })
                });

                var data = await res.json();

                if (res.ok) {
                    if (data.receipt_url) {
                        window.location.href = data.receipt_url;
                    } else {
                        location.reload();
                    }
                } else {
                    showError(data.error || data.message || 'Withdrawal failed.');
                }
            } catch (e) {
                showError('Network error. Please try again.');
            }
        });

        fetchBanksAndRender();
    </script>

    <style>
        .balance-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 12px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(59,130,246,0.12);
            padding: clamp(24px, 4vw, 40px);
            text-align: center;
        }
        .balance-label {
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 8px;
        }
        .withdrawal-info {
            display:flex;justify-content:space-between;align-items:center;
            background:rgba(12,24,48,0.6);border:1px solid rgba(59,130,246,0.15);
            border-radius:8px;padding:12px 16px;margin:12px 0;font-size:0.85rem;
        }
        .withdrawal-info strong { color:var(--blue-soft); }
        .balance-amount {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin: 0;
            color: white;
        }
        .auth-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 8px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(59,130,246,0.12);
            display: grid;
            gap: 18px;
            padding: clamp(22px, 4vw, 34px);
        }
        .auth-card label {
            color: #dce7f8;
            display: grid;
            font-size: 0.9rem;
            font-weight: 800;
            gap: 9px;
        }
        .auth-card input {
            background: rgba(2, 6, 14, 0.82);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            color: white;
            min-height: 52px;
            outline: 0;
            padding: 0 16px;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .auth-card input:focus {
            border-color: rgba(59, 130, 246, 0.74);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.16);
        }
        .auth-submit {
            border: 0;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .success-message {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }
        .error-message p { margin: 0; }
        .error-message p + p { margin-top: 6px; }
        .history-item {
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .custom-select-container { position: relative; width: 100%; }
        .custom-select-trigger {
            background: rgba(2, 6, 14, 0.82);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            color: white;
            min-height: 52px;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .custom-select-trigger.active,
        .custom-select-trigger:hover {
            border-color: rgba(59, 130, 246, 0.74);
        }
        .custom-select-trigger .chevron { transition: transform 0.2s; }
        .custom-options {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: rgba(6, 14, 30, 0.98);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            max-height: 280px;
            overflow-y: auto;
            z-index: 50;
            display: none;
        }
        .custom-options.active { display: block; }
        .search-box {
            padding: 12px;
            border-bottom: 1px solid rgba(59,130,246,0.15);
            position: sticky;
            top: 0;
            background: rgba(6, 14, 30, 0.98);
            border-radius: 12px 12px 0 0;
        }
        .search-box input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 40px;
            border: 1px solid rgba(59,130,246,0.25);
            font-size: 14px;
            background: rgba(2,6,14,0.82);
            color: white;
        }
        .search-box input:focus {
            border-color: rgba(59, 130, 246, 0.74);
            outline: none;
        }
        .option {
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.1s;
            border-bottom: 1px solid rgba(59,130,246,0.06);
        }
        .option:hover { background: rgba(59,130,246,0.08); }
        .bank-logo {
            width: 36px;
            height: 36px;
            background: rgba(59,130,246,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #93c5fd;
            font-size: 13px;
            flex-shrink: 0;
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.show {
            display: flex;
        }
        .modal-box {
            background: linear-gradient(145deg, rgba(12,24,48,0.98), rgba(4,9,18,0.98));
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 14px;
            padding: 32px;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }
        .modal-box h3 {
            margin: 0 0 12px;
            font-size: 1.3rem;
        }
        .modal-box p {
            color: var(--muted);
            margin: 0 0 24px;
            line-height: 1.5;
        }
        .modal-actions {
            display: flex;
            gap: 12px;
        }
        .modal-actions .btn-outline {
            background: transparent !important;
            border: 1px solid rgba(59,130,246,0.4) !important;
            color: var(--blue-soft) !important;
            cursor: pointer;
        }
        .modal-actions .btn-outline:hover {
            background: rgba(59,130,246,0.1) !important;
            border-color: var(--blue) !important;
            color: white !important;
        }
        .modal-actions .button {
            border: 0;
            cursor: pointer;
            padding: 12px 20px;
            font-weight: 700;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
@endsection
