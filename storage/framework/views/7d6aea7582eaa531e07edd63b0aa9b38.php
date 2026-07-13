<?php $__env->startSection('title', 'Upgrade Account'); ?>
<?php $__env->startSection('page-title', 'Upgrade Account'); ?>
<?php $__env->startSection('meta-description', 'Upgrade your Music Town account tier for higher withdrawal limits.'); ?>

<?php $__env->startSection('content'); ?>
        <section style="max-width:700px;margin:0 auto;">
            <div class="section-heading">
                <p class="eyebrow">Account Tier</p>
                <h2 style="font-size:1.1rem;">Upgrade Your Account</h2>
                <p style="color:var(--muted);margin-top:8px;">
                    Current: <strong style="color:var(--blue-soft);text-transform:uppercase;"><?php echo e($user->tier); ?></strong>
                    &middot; Balance: <strong style="color:var(--gold);">₦<?php echo e(number_format($user->balance, 2)); ?></strong>
                </p>
            </div>

            <?php if(session('success')): ?>
                <p class="form-message success-message"><?php echo e(session('success')); ?></p>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="form-message error-message">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php $currentLevel = (int) substr($user->tier, 4); ?>

            
            <?php if($pendingFunding && $virtualAccount): ?>
                <?php
                    $fundTier = null;
                    foreach ($tiers as $k => $v) { if ($v['cost'] == $pendingFunding->amount) { $fundTier = $k; break; } }
                    $tierNames = ['tier1' => 'Tier 1', 'tier2' => 'Tier 2', 'tier3' => 'Tier 3'];
                    $tierLabel = $tierNames[$fundTier] ?? 'Upgrade';
                ?>
                <div class="payment-card">
                    <div class="payment-card-head">
                        <span class="eyebrow">Pending Payment</span>
                        <strong style="font-size:1.2rem;">₦<?php echo e(number_format($pendingFunding->amount, 2)); ?></strong>
                    </div>
                    <p style="color:var(--muted);margin:4px 0 16px;">Transfer the exact amount above to this account:</p>

                    <div class="account-detail">
                        <span>Bank</span>
                        <strong><?php echo e($virtualAccount->bank_name); ?></strong>
                    </div>
                    <div class="account-detail">
                        <span>Account Number</span>
                        <div class="copy-row">
                            <strong><?php echo e($virtualAccount->account_number); ?></strong>
                            <button type="button" class="copy-btn" onclick="copyText('<?php echo e($virtualAccount->account_number); ?>')">Copy</button>
                        </div>
                    </div>
                    <div class="account-detail">
                        <span>Account Name</span>
                        <strong><?php echo e($virtualAccount->account_name); ?></strong>
                    </div>

                    <button class="button" id="check-upgrade-btn" style="width:100%;margin-top:16px;" onclick="checkUpgradePayment()">I Have Paid</button>
                    <p id="check-upgrade-msg" style="margin-top:8px;font-size:0.85rem;color:var(--muted);text-align:center;"></p>
                </div>
            <?php endif; ?>

            <div style="display:grid;gap:16px;margin-top:24px;">

                <div class="tier-card <?php echo e($user->tier === 'tier0' ? '' : 'disabled'); ?>">
                    <div class="tier-head">
                        <h3>Tier 0</h3>
                        <span class="tier-badge current">Current</span>
                    </div>
                    <p class="tier-desc">Default account for new users.</p>
                    <div class="tier-limit">Withdrawals: <strong style="color:var(--muted);">Not allowed</strong></div>
                    <div class="tier-cost">Free</div>
                </div>

                <?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tierKey => $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $targetLevel = (int) substr($tierKey, 4);
                        $isUpgrade = $targetLevel > $currentLevel;
                        $isCurrent = $tierKey === $user->tier;
                    ?>
                    <div class="tier-card <?php echo e(($isUpgrade || $isCurrent) ? '' : 'disabled'); ?>">
                        <div class="tier-head">
                            <h3><?php echo e(ucfirst($tierKey)); ?></h3>
                            <?php if($isCurrent): ?>
                                <span class="tier-badge current">Current</span>
                            <?php endif; ?>
                        </div>
                        <p class="tier-desc"><?php echo e($tier['desc']); ?></p>
                        <div class="tier-limit">Max withdrawal: <strong><?php echo e($tier['max']); ?></strong> &middot; Withdrawals: <strong><?php echo e($tier['count']); ?></strong></div>
                        <div class="tier-cost">Upgrade: <strong>₦<?php echo e(number_format($tier['cost'])); ?></strong></div>
                        <?php if($isUpgrade): ?>
                            <form method="POST" action="<?php echo e(route('profile.upgrade')); ?>" style="margin-top:16px;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="tier" value="<?php echo e($tierKey); ?>">
                                <input type="hidden" name="method" value="bank">
                                <button class="button" type="submit" style="width:100%;">Upgrade Now (₦<?php echo e(number_format($tier['cost'])); ?>)</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
        </section>

    <script>
        function copyText(text) {
            navigator.clipboard.writeText(text).then(function() {
                document.querySelectorAll('.copy-btn').forEach(function(b) {
                    var orig = b.textContent;
                    b.textContent = 'Copied!';
                    setTimeout(function() { b.textContent = orig; }, 2000);
                });
            }).catch(function() {
                alert('Press Ctrl+C to copy');
            });
        }

        function checkUpgradePayment() {
            var btn = document.getElementById('check-upgrade-btn');
            var msg = document.getElementById('check-upgrade-msg');
            if (!btn || !msg) return;
            btn.disabled = true;
            btn.textContent = 'Checking...';
            msg.textContent = '';

            fetch('<?php echo e(route('profile.upgrade.check')); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.status === 'confirmed') {
                    msg.style.color = '#60a5fa';
                    msg.textContent = data.message;
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    msg.style.color = 'var(--muted)';
                    msg.textContent = data.message;
                    btn.disabled = false;
                    btn.textContent = 'I Have Paid';
                }
            }).catch(function() {
                msg.textContent = 'Something went wrong. Try again.';
                btn.disabled = false;
                btn.textContent = 'I Have Paid';
            });
        }
    </script>

    <style>
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
        .error-message {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #f87171;
        }
        .payment-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .payment-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .account-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(59,130,246,0.1);
        }
        .account-detail:last-of-type {
            border-bottom: none;
        }
        .account-detail span {
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 700;
        }
        .account-detail strong {
            font-size: 0.95rem;
        }
        .copy-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .copy-btn {
            background: transparent;
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 6px;
            color: var(--blue-soft);
            cursor: pointer;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 700;
            transition: border-color 180ms, color 180ms;
        }
        .copy-btn:hover {
            border-color: var(--blue);
            color: white;
        }
        .tier-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 12px;
            padding: 24px;
        }
        .tier-card.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .tier-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .tier-head h3 {
            margin: 0;
            font-size: 1.2rem;
            text-transform: capitalize;
        }
        .tier-badge {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .tier-badge.current {
            background: rgba(59,130,246,0.2);
            color: var(--blue-soft);
            border: 1px solid rgba(59,130,246,0.3);
        }
        .tier-desc {
            color: var(--muted);
            margin: 0 0 12px;
        }
        .tier-limit {
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        .tier-cost {
            font-size: 0.9rem;
            color: var(--blue-soft);
        }
        .btn-outline {
            background: transparent !important;
            border: 1px solid rgba(59,130,246,0.4) !important;
            color: var(--blue-soft) !important;
        }
        .btn-outline:hover {
            background: rgba(59,130,246,0.1) !important;
            border-color: var(--blue) !important;
            color: white !important;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\music\resources\views/profile/upgrade.blade.php ENDPATH**/ ?>