<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-label">Total Users</p>
            <p class="stat-number"><?php echo e($stats['users']); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Songs</p>
            <p class="stat-number"><?php echo e($stats['songs']); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending Payments</p>
            <p class="stat-number"><?php echo e($stats['pending_payments']); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total User Balance</p>
            <p class="stat-number">₦<?php echo e(number_format($stats['total_balance'], 2)); ?></p>
        </div>
    </div>

    <div class="admin-card">
        <p style="font-weight:700;margin:0 0 16px;font-size:1rem;">Recent Users</p>
        <?php if($recentUsers->count() > 0): ?>
            <div style="display:grid;gap:10px;">
                <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:10px 0;border-bottom:1px solid rgba(72,181,255,0.06);">
                        <div>
                            <strong style="font-size:0.95rem;"><?php echo e($u->name); ?></strong>
                            <small style="display:block;color:var(--muted);font-size:0.8rem;"><?php echo e($u->email); ?></small>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-weight:700;color:var(--gold);">₦<?php echo e(number_format($u->balance, 2)); ?></span>
                            <small style="display:block;color:var(--muted);font-size:0.75rem;"><?php echo e($u->created_at->format('M d, Y')); ?></small>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p style="color:var(--muted);margin:0;">No users registered yet.</p>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\music\resources\views/admin/pages/dashboard.blade.php ENDPATH**/ ?>