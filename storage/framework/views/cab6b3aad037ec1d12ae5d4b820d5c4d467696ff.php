<?php echo e(Form::model($vender,array('route' => array('vender.update', $vender->id), 'method' => 'PUT'))); ?>

<div class="modal-body">

    <h5 class="sub-title"><?php echo e(__('Basic Info')); ?></h5>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('name',__('Name'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <span><i class="ti ti-address-card"></i></span>
                    <?php echo e(Form::text('name',null,array('class'=>'form-control','required'=>'required'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('contact',__('Contact'),['class'=>'form-label'])); ?>

                <div class="form-icon-user">
                    <span><i class="ti ti-mobile-alt"></i></span>
                    <?php echo e(Form::text('contact',null,array('class'=>'form-control','required'=>'required'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('tax_number',__('Tax Number'),['class'=>'form-label'])); ?>

                <div class="form-icon-user">
                    <span><i class="ti ti-crosshairs"></i></span>
                    <?php echo e(Form::text('tax_number',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('bank_account',__('Bank Account'),['class'=>'form-label'])); ?>

                <div class="form-icon-user">
                    <span><i class="ti ti-crosshairs"></i></span>
                    <?php echo e(Form::text('bank_account',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('bic_number',__('BIC Number'),['class'=>'form-label'])); ?>

                <div class="form-icon-user">
                    <span><i class="ti ti-crosshairs"></i></span>
                    <?php echo e(Form::text('bic_number',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <?php if(!$customFields->isEmpty()): ?>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    <?php echo $__env->make('customFields.formBuilder', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <h5 class="sub-title"><?php echo e(__('BIlling Address')); ?></h5>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_name',__('Name'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_name',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_country',__('Country'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_country',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_state',__('State'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_state',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_city',__('City'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_city',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_phone',__('Phone'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_phone',null,array('class'=>'form-control'))); ?>

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('billing_zip',__('Zip Code'),array('class'=>'form-label'))); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('billing_zip',null,array('class'=>'form-control','placeholder'=>__('Enter User Email')))); ?>

                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('billing_address',__('Address'),array('class'=>'form-label'))); ?>

                <div class="input-group">
                    <?php echo e(Form::textarea('billing_address',null,array('class'=>'form-control','rows'=>3))); ?>

                </div>
            </div>
        </div>
    </div>

    <?php if(App\Models\Utility::getValByName('shipping_display')=='on'): ?>
        <div class="col-md-12 text-end">
            <input type="button" id="billing_data" value="<?php echo e(__('Shipping Same As Billing')); ?>" class="btn btn-primary">
        </div>
        <h5 class="sub-title"><?php echo e(__('Shipping Address')); ?></h5>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_name',__('Name'),array('class'=>'form-label'))); ?>

                    <div class="form-icon-user">
                        <?php echo e(Form::text('shipping_name',null,array('class'=>'form-control'))); ?>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_country',__('Country'),array('class'=>'form-label'))); ?>

                    <div class="form-icon-user">
                        <?php echo e(Form::text('shipping_country',null,array('class'=>'form-control'))); ?>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_state',__('State'),array('class'=>'form-label'))); ?>

                    <div class="form-icon-user">
                        <?php echo e(Form::text('shipping_state',null,array('class'=>'form-control'))); ?>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_city',__('City'),array('class'=>'form-label'))); ?>

                        <?php echo e(Form::text('shipping_city',null,array('class'=>'form-control'))); ?>

                    </div>
                </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_phone',__('Phone'),array('class'=>'form-label'))); ?>

                    <div class="form-icon-user">
                        <?php echo e(Form::text('shipping_phone',null,array('class'=>'form-control'))); ?>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_zip',__('Zip Code'),array('class'=>'form-label'))); ?>

                    <div class="form-icon-user">
                        <?php echo e(Form::text('shipping_zip',null,array('class'=>'form-control','placeholder'=>__('Enter User Email')))); ?>

                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo e(Form::label('shipping_address',__('Address'),array('class'=>'form-label'))); ?>

                    <div class="input-group">
                        <?php echo e(Form::textarea('shipping_address',null,array('class'=>'form-control','rows'=>3))); ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Update')); ?>" class="btn btn-primary">
</div>

<?php echo e(Form::close()); ?>

<?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/vender/edit.blade.php ENDPATH**/ ?>