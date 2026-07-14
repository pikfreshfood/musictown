<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:wrap;">
        <form method="GET" action="<?php echo e(route('admin.users')); ?>" style="display:flex;gap:8px;align-items:center;">
            <input name="q" value="<?php echo e($q ?? ''); ?>" placeholder="Search users by name, email, phone or referral code" style="padding:8px 10px;border-radius:6px;border:1px solid var(--line);width:320px;">
            <button class="button" type="submit" style="padding:8px 12px;">Search</button>
            <?php if(!empty($q)): ?> <a href="<?php echo e(route('admin.users')); ?>" style="margin-left:8px;color:var(--muted);">Clear</a> <?php endif; ?>
        </form>

        <div style="color:var(--muted);font-size:0.95rem;">Showing <?php echo e($users->total()); ?> users</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Tier</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Premium</th>
                    <th>Referrer</th>
                    <th>Referrals</th>
                    <th>Account</th>
                    <th>Joined</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo e($u->name); ?></td>
                        <td style="color:var(--muted);font-weight:700;"><?php echo e($u->tier ?? '—'); ?></td>
                        <td style="color:var(--muted);"><?php echo e($u->email); ?></td>
                        <td style="font-weight:700;color:var(--gold);">&#8358;<?php echo e(number_format($u->balance, 2)); ?></td>
                        <td><?php if($u->is_premium): ?> <span style="color:var(--green);font-weight:700;font-size:0.8rem;">YES</span> <?php else: ?> <span style="color:var(--muted);font-size:0.8rem;">No</span> <?php endif; ?></td>
                        <td style="color:var(--muted);font-size:0.85rem;"><?php if($u->referrer): ?> <?php echo e($u->referrer->name); ?> (<?php echo e($u->referrer->email); ?>) <?php else: ?> — <?php endif; ?></td>
                        <td style="display:flex;align-items:center;gap:8px;">
                            <span style="font-weight:700;color:var(--blue-soft);"><?php echo e($u->referrals->count()); ?></span>
                            <?php if($u->referrals->count() > 0): ?>
                                <a class="btn btn-secondary btn-sm" href="<?php echo e(route('admin.users.referrals', $u->id)); ?>">View</a>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--muted);font-size:0.85rem;"><?php if($u->paystackVirtualAccount): ?> <?php echo e($u->paystackVirtualAccount->bank_name); ?> — <?php echo e($u->paystackVirtualAccount->account_number); ?> (<?php echo e($u->paystackVirtualAccount->account_name); ?>) <?php else: ?> — <?php endif; ?></td>
                        <td style="color:var(--muted);font-size:0.85rem;"><?php echo e($u->created_at->format('M d, Y')); ?></td>
                        <td style="text-align:center;display:flex;justify-content:center;gap:6px;flex-wrap:wrap;">
                            <a class="btn btn-danger btn-sm" href="<?php echo e(route('admin.users.delete', $u->id)); ?>" onclick="return confirm('Delete user <?php echo e($u->name); ?>?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" style="padding:32px;text-align:center;color:var(--muted);">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($users->hasPages()): ?>
        <div class="pagination-wrap"><?php echo e($users->links('pagination::tailwind')); ?></div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\music\resources\views/admin/pages/users.blade.php ENDPATH**/ ?>