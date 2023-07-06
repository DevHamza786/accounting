<?php $__env->startSection('page-title'); ?>
<?php echo e(__('Manage Recurring Payments')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
<li class="breadcrumb-item"><?php echo e(__('Recurring Payment')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-btn'); ?>
<div class="float-end">

    <!-- <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="<?php echo e(__('Filter')); ?>">
            <i class="ti ti-filter"></i>
        </a> -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create recurringpayment')): ?>
    <a href="#" data-url="<?php echo e(route('recurringpayment.create')); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" data-size="lg" data-title="<?php echo e(__('Create New Recurring Payment')); ?>" title="<?php echo e(__('Create')); ?>" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class=" multi-collapse mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    <?php echo e(Form::open(array('route' => array('recurringpayment.index'),'method' => 'GET','id'=>'recurringpayment_form'))); ?>

                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('date', __('Date'),['class'=>'text-type'])); ?>

                                        <?php echo e(Form::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1'))); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('account', __('Account'),['class'=>'text-type'])); ?>

                                        <?php echo e(Form::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select' ,'id'=>'choices-multiple'))); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('vender', __('Vendor'),['class'=>'text-type'])); ?>

                                        <?php echo e(Form::select('vender',$vender,isset($_GET['vender'])?$_GET['vender']:'', array('class' => 'form-control select','id'=>'choices-multiple1'))); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('category', __('Category'),['class'=>'text-type'])); ?>

                                        <?php echo e(Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'choices-multiple2'))); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('recurringpayment_form').submit(); return false;" data-toggle="tooltip" title="<?php echo e(__('Apply')); ?>" data-original-title="<?php echo e(__('apply')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="<?php echo e(route('recurringpayment.index')); ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Date')); ?></th>
                                <!-- <th><?php echo e(__('End Date')); ?></th>
                                <th><?php echo e(__('Period')); ?></th> -->
                                <th><?php echo e(__('Vendor')); ?></th>
                                <th><?php echo e(__('Policy Number')); ?></th>
                                <th><?php echo e(__('Tax')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Account')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Reference')); ?></th>
                                <th><?php echo e(__('Description')); ?></th>
                                <th><?php echo e(__('Payment Receipt')); ?></th>
                                <?php if(Gate::check('edit payment') || Gate::check('delete payment')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $recurringpayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recurringpayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $recurringpaymentpath=\App\Models\Utility::get_file('uploads/payment');
                            ?>
                            <tr class="font-style">
                                <td><?php echo e(Auth::user()->dateFormat($recurringpayment->recurring_date)); ?></td>
                                <!-- <td><?php echo e(Auth::user()->dateFormat($recurringpayment->end_date)); ?></td>
                                <td><?php echo e(Auth::user()->dateFormat($recurringpayment->period)); ?></td> -->
                                <td><?php echo e(!empty($recurringpayment->vender)?$recurringpayment->vender->name:'-'); ?></td>
                                <td><?php echo e(Auth::user()->priceFormat($recurringpayment->policy_number)); ?></td>
                                <td><?php echo e(!empty($recurringpayment->taxes)?$recurringpayment->taxes->name:'-'); ?></td>
                                <td><?php echo e(Auth::user()->priceFormat($recurringpayment->amount)); ?></td>
                                <td><?php echo e(!empty($recurringpayment->bankAccount)?$recurringpayment->bankAccount->bank_name.' '.$recurringpayment->bankAccount->holder_name:''); ?></td>
                                <td><?php echo e(!empty($recurringpayment->category)?$recurringpayment->category->name:'-'); ?></td>
                                <td><?php echo e(!empty($recurringpayment->reference)?$recurringpayment->reference:'-'); ?></td>
                                <td><?php echo e(!empty($recurringpayment->description)?$recurringpayment->description:'-'); ?></td>
                                <td>
                                    <?php if(!empty($recurringpayment->add_receipt)): ?>
                                    <a class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="<?php echo e($recurringpaymentpath . '/' . $recurringpayment->add_receipt); ?>" download="">
                                        <i class="ti ti-download text-white"></i>
                                    </a>
                                    
                                    <a href="<?php echo e($recurringpaymentpath . '/' . $recurringpayment->add_receipt); ?>" class="btn btn-sm btn-secondary btn-icon rounded-pill" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair"></i></span></a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <?php if(Gate::check('edit revenue') || Gate::check('delete revenue')): ?>
                                <td class="action text-end">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit payment')): ?>
                                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="<?php echo e(route('recurringpayment.edit',$recurringpayment->id)); ?>" data-ajax-popup="true" data-title="<?php echo e(__('Edit Recurring Payment')); ?>" data-size="lg" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-original-title="<?php echo e(__('Edit')); ?>">
                                            <i class="ti ti-edit text-white"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete payment')): ?>
                                    <div class="action-btn bg-danger ms-2">
                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['recurringpayment.destroy', $recurringpayment->id],'id'=>'delete-form-'.$recurringpayment->id]); ?>

                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Delete')); ?>" title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($recurringpayment->id); ?>').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        <?php echo Form::close(); ?>

                                    </div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/recurringpayment/index.blade.php ENDPATH**/ ?>