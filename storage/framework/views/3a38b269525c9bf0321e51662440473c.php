<?php $__env->startSection('title', 'Music'); ?>
<?php $__env->startSection('page-title', 'Music Management'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-card" style="margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Upload Music</p>
                <form method="POST" action="<?php echo e(route('admin.upload.music')); ?>" enctype="multipart/form-data" style="display:grid;gap:14px;">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">
                        Select music files (mp3, wav, ogg, aac, m4a, flac, wma)
                        <input type="file" name="music_files[]" multiple accept="audio/*" required class="input-field" style="padding-top:10px;min-height:auto;">
                    </label>
                    <p style="color:var(--muted);font-size:0.8rem;margin:0;">Title is auto-extracted from filename. Max 128MB per file.</p>
                    <button class="btn btn-primary" type="submit" style="justify-self:start;">Upload Music</button>
                </form>
            </div>

            <div style="display:flex;align-items:center;gap:10px;">
                <form method="POST" action="<?php echo e(route('admin.music.sync-jamendo')); ?>" style="display:flex;align-items:center;gap:8px;">
                    <?php echo csrf_field(); ?>
                    <select name="tag" class="input-field" style="min-height:auto;padding:6px 8px;font-size:0.85rem;width:auto;">
                        <option value="all">All Genres</option>
                        <?php $__currentLoopData = App\Services\JamendoService::getGenres(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($val !== 'all'): ?>
                                <option value="<?php echo e($val); ?>"><?php echo e($label); ?></option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="number" name="limit" value="50" min="1" max="200" class="input-field" style="min-height:auto;padding:6px 8px;font-size:0.85rem;width:70px;" title="Number of tracks">
                    <button type="submit" class="btn btn-primary" style="font-size:0.85rem;padding:6px 14px;">Sync from Jamendo</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.music.delete-all')); ?>" onsubmit="return confirm('Delete all music and their files?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-danger">Delete All Music</button>
                </form>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Duration</th>
                    <th>Audio</th>
                    <th>Uploaded</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $songs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $song): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $mins = intdiv($song->duration, 60); $secs = $song->duration % 60; ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo e($song->title); ?></td>
                        <td style="color:var(--muted);"><?php echo e($song->artist); ?></td>
                        <td style="color:var(--muted);"><?php echo e($mins); ?>:<?php echo e(str_pad($secs, 2, '0')); ?></td>
                        <td><?php if($song->audio_url): ?> <span style="color:var(--green);font-size:0.8rem;font-weight:700;">Yes</span> <?php else: ?> <span style="color:var(--muted);font-size:0.8rem;">No</span> <?php endif; ?></td>
                        <td style="color:var(--muted);font-size:0.85rem;"><?php echo e($song->created_at->format('M d, Y')); ?></td>
                        <td style="text-align:center;">
                            <a class="btn btn-danger btn-sm" href="<?php echo e(route('admin.music.delete', $song->id)); ?>" onclick="return confirm('Delete <?php echo e($song->title); ?>?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--muted);">No songs uploaded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($songs->hasPages()): ?>
        <div class="pagination-wrap"><?php echo e($songs->links('pagination::tailwind')); ?></div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\music\resources\views/admin/pages/music.blade.php ENDPATH**/ ?>