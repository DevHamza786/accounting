<?php echo e(Form::open(array('route' => array('customer.invoice.send.mail',$invoice_id)))); ?>

<div class="modal-body">

    <div class="row">
    <div class="form-group col-md-12">
        <?php echo e(Form::label('email', __('Email'))); ?>

        <?php echo e(Form::text('email', '', array('class' => 'form-control','required'=>'required'))); ?>

        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <span class="invalid-email" role="alert">
            <strong class="text-danger"><?php echo e($message); ?></strong>
        </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Create')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/customer/invoice_send.blade.php ENDPATH**/ ?>