<?php if(!empty($customer)): ?>
    <div class="row">
        <div class="col-md-5">
            <h6><?php echo e(__('Bill to')); ?></h6>
            <div class="bill-to">
                <small>
                    <span><?php echo e($customer['billing_name']); ?></span><br>
                    <span><?php echo e($customer['billing_address']); ?></span><br>
                    <span><?php echo e($customer['billing_zip']); ?></span>
                    <span><?php echo e($customer['billing_city']</span><br>
                    <span>{{$customer['billing_state']','); ?></span>
                    <span><?php echo e($customer['billing_country']</span><br>
                    <span>{{$customer['billing_phone']); ?></span><br>
                </small>
            </div>
        </div>
        <div class="col-md-5">
            <h6><?php echo e(__('Ship to')); ?></h6>
            <div class="bill-to">
                <small>
                    <span><?php echo e($customer['shipping_name']); ?></span><br>
                    <span><?php echo e($customer['shipping_address']); ?></span><br>
                    <span><?php echo e($customer['shipping_zip']); ?></span>
                    <span><?php echo e($customer['shipping_city']); ?></span><br>
                    <span><?php echo e($customer['shipping_state']','); ?></span>
                    <span><?php echo e($customer['shipping_country']); ?></span><br>
                    <span><?php echo e($customer['shipping_phone']); ?></span><br>
                </small>
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm"><?php echo e(__(' Remove')); ?></a>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/invoice/customer_detail.blade.php ENDPATH**/ ?>