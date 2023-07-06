<?php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=App\Models\Utility::getValByName('company_logo');
?>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Forgot Password')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('custom-scripts'); ?>
    <?php if(env('RECAPTCHA_MODULE') == 'yes'): ?>
        <?php echo NoCaptcha::renderJs(); ?>

    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('auth-lang'); ?>
    <select class="btn btn-primary my-1 me-2 " onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
        <?php $__currentLoopData = Utility::languages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option <?php if($lang == $language): ?> selected <?php endif; ?> value="<?php echo e(route('customer.change.langPass',$language)); ?>"><?php echo e(Str::upper($language)); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="">
        <h2 class="mb-3 f-w-600"><?php echo e(__('Reset Password')); ?></h2>
        <small class="text-muted"><?php echo e(__('We will send a link to reset your password')); ?></small>
        <?php if(session('status')): ?>
            <small class="text-muted"><?php echo e(session('status')); ?></small>
        <?php endif; ?>
    </div>
    <form method="POST" action="<?php echo e(route('customer.password.email')); ?>">
        <?php echo csrf_field(); ?>
        <div class="">

            <div class="form-group mb-3">
                <label for="email" class="form-label"><?php echo e(__('E-Mail Address')); ?></label>
                <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="invalid-feedback" role="alert">
                    <small><?php echo e($message); ?></small>
                </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>


            <?php if(env('RECAPTCHA_MODULE') == 'yes'): ?>
                <div class="form-group mb-3">
                    <?php echo NoCaptcha::display(); ?>

                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="small text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php endif; ?>


            <div class="d-grid">
                <button type="submit" class="btn-login btn btn-primary btn-block mt-2"><?php echo e(__('Send Password Reset Link')); ?></button>
            </div>
            <p class="my-4 text-center"><?php echo e(__("Back to")); ?> <a href="<?php echo e(route('customer.login')); ?>" class="text-primary"><?php echo e(__('Login')); ?></a></p>

        </div>
    <?php echo e(Form::close()); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/auth/customerEmail.blade.php ENDPATH**/ ?>