<?php
    use App\Models\Utility;
    
?>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Bill Detail')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('click', '#shipping', function() {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax({
                url: url,
                type: 'get',
                data: {
                    'is_display': is_display,
                },
                success: function(data) {
                    // console.log(data);
                }
            });
        })
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <?php
        $vendor = $bill->vender;
    ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send bill')): ?>
        
    <?php endif; ?>

    <?php if(\Auth::check() && isset(\Auth::user()->type) && \Auth::user()->type == 'company'): ?>
        <?php if($bill->status != 0): ?>
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-10 offset-1 d-flex align-items-center justify-content-between justify-content-md-end">
                    <?php if(!empty($billPayment)): ?>
                        <div class="all-button-box mx-2">
                            <a href="#" data-url="<?php echo e(route('bill.debit.note', $bill->id)); ?>" data-ajax-popup="true"
                                data-title="<?php echo e(__('Add Debit Note')); ?>" class="btn btn-primary">
                                <?php echo e(__('Add Debit Note')); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="all-button-box mx-2">
                        <a href="<?php echo e(route('bill.resent', $bill->id)); ?>" class="btn btn-primary">
                            <?php echo e(__('Resend Bill')); ?>

                        </a>
                    </div>
                    <div class="all-button-box">
                        <a href="<?php echo e(route('bill.pdf', Crypt::encrypt($bill->id))); ?>" target="_blank"
                            class="btn btn-primary">
                            <?php echo e(__('Download')); ?>

                        </a>
                    </div>
                    
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="row justify-content-between align-items-center mb-3">
            <div class="col-10 offset-1 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="all-button-box mx-2">
                    <a href="#" data-url="<?php echo e(route('vender.bill.send', $bill->id)); ?>" data-ajax-popup="true"
                        data-title="<?php echo e(__('Send Bill')); ?>" class="btn btn-xs btn-white btn-icon-only width-auto">
                        <?php echo e(__('Send Mail')); ?>

                    </a>
                </div>
                <div class="all-button-box mx-2">
                    <a href="<?php echo e(route('bill.pdf', Crypt::encrypt($bill->id))); ?>" target="_blank"
                        class="btn btn-xs btn-white btn-icon-only width-auto">
                        <?php echo e(__('Download')); ?>

                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-10 offset-1">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h2><?php echo e(__('Bill')); ?></h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">
                                        <?php echo e(Utility::billNumberFormat($company_setting, $bill->bill_id)); ?></h3>

                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="me-4">
                                            <small>
                                                <strong><?php echo e(__('Issue Date')); ?> :</strong><br>
                                                
                                                <?php echo e(date($bill->bill_date)); ?><br><br>
                                            </small>
                                        </div>
                                        <div>
                                            <small>
                                                <strong><?php echo e(__('Due Date')); ?> :</strong><br>
                                                
                                                <?php echo e(date($bill->due_date)); ?><br><br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <?php if(!empty($vendor->billing_name)): ?>
                                    <div class="col">
                                        <small class="font-style">
                                            <strong><?php echo e(__('Billed To')); ?> :</strong><br>
                                            <?php echo e(!empty($vendor->billing_name) ? $vendor->billing_name : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_phone) ? $vendor->billing_phone : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_address) ? $vendor->billing_address : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_zip) ? $vendor->billing_zip : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_city) ? $vendor->billing_city : '' . ', '); ?>

                                            <?php echo e(!empty($vendor->billing_state) ? $vendor->billing_state : '', ', '); ?>

                                            <?php echo e(!empty($vendor->billing_country) ? $vendor->billing_country : ''); ?>

                                        </small>
                                    </div>
                                <?php endif; ?>
                                <?php if(App\Models\Utility::getValByName('shipping_display') == 'on'): ?>
                                    <div class="col">
                                        <small>
                                            <strong><?php echo e(__('Shipped To')); ?> :</strong><br>
                                            <?php echo e(!empty($vendor->shipping_name) ? $vendor->shipping_name : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_phone) ? $vendor->shipping_phone : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_address) ? $vendor->shipping_address : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_zip) ? $vendor->shipping_zip : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_city) ? $vendor->shipping_city : '' . ', '); ?>

                                            <?php echo e(!empty($vendor->shipping_state) ? $vendor->shipping_state : '', ', '); ?>

                                            <?php echo e(!empty($vendor->shipping_country) ? $vendor->shipping_country : ''); ?>

                                        </small>
                                    </div>
                                <?php endif; ?>
                                < <div class="col">
                                    <div class="float-end mt-3">
                                        <p> <?php echo DNS2D::getBarcodeHTML(
                                            route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)),
                                            'QRCODE',
                                            2,
                                            2,
                                        ); ?></p>
                                    </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <small>
                                    <strong><?php echo e(__('Status')); ?> :</strong><br>
                                    <?php if($bill->status == 0): ?>
                                        <span
                                            class="badge bg-primary p-2 px-3 rounded"><?php echo e(__(\App\Models\Bill::$statues[$bill->status])); ?></span>
                                    <?php elseif($bill->status == 1): ?>
                                        <span
                                            class="badge bg-warning p-2 px-3 rounded"><?php echo e(__(\App\Models\Bill::$statues[$bill->status])); ?></span>
                                    <?php elseif($bill->status == 2): ?>
                                        <span
                                            class="badge bg-danger p-2 px-3 rounded"><?php echo e(__(\App\Models\Bill::$statues[$bill->status])); ?></span>
                                    <?php elseif($bill->status == 3): ?>
                                        <span
                                            class="badge bg-info p-2 px-3 rounded"><?php echo e(__(\App\Models\Bill::$statues[$bill->status])); ?></span>
                                    <?php elseif($bill->status == 4): ?>
                                        <span
                                            class="badge bg-success p-2 px-3 rounded"><?php echo e(__(\App\Models\Bill::$statues[$bill->status])); ?></span>
                                    <?php endif; ?>
                                </small>
                            </div>


                            <?php if(!empty($customFields) && count($bill->customField) > 0): ?>
                                <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col text-end">
                                        <small>
                                            <strong><?php echo e($field->name); ?> :</strong><br>
                                            <?php echo e(!empty($bill->customField) ? $bill->customField[$field->id] : '-'); ?>

                                            <br><br>
                                        </small>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="font-weight-bold"><?php echo e(__('Product Summary')); ?></div>
                                <small><?php echo e(__('All items here cannot be deleted.')); ?></small>
                                <div class="table-responsive">
                                    <table class="table ">
                                        <tr>
                                            <th class="text-dark" data-width="40">#</th>
                                            <th class="text-dark"><?php echo e(__('Product')); ?></th>
                                            <th class="text-dark"><?php echo e(__('Quantity')); ?></th>
                                            <th class="text-dark"><?php echo e(__('Rate')); ?></th>
                                            <th class="text-dark">
                                                <?php echo e(__('Discount')); ?>


                                            </th>
                                            <th class="text-dark"><?php echo e(__('Tax')); ?></th>
                                            <th class="text-dark"><?php echo e(__('Description')); ?></th>
                                            <th class="text-end text-dark" width="12%"><?php echo e(__('Price')); ?><br>
                                                <small
                                                    class="text-danger font-weight-bold"><?php echo e(__('before tax & discount')); ?></small>
                                            </th>
                                        </tr>
                                        <?php
                                            $totalQuantity = 0;
                                            $totalRate = 0;
                                            $totalTaxPrice = 0;
                                            $totalDiscount = 0;
                                            $taxesData = [];
                                        ?>

                                        <?php $__currentLoopData = $iteams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $iteam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(!empty($iteam->tax)): ?>
                                                <?php
                                                    $taxes = App\Models\Utility::tax($iteam->tax);
                                                    $totalQuantity += $iteam->quantity;
                                                    $totalRate += $iteam->price;
                                                    $totalDiscount += $iteam->discount;
                                                    foreach ($taxes as $taxe) {
                                                        $taxDataPrice = App\Models\Utility::taxRate($taxe->rate, $iteam->price, $iteam->quantity);
                                                        if (array_key_exists($taxe->name, $taxesData)) {
                                                            $taxesData[$taxe->name] = $taxesData[$taxe->name] + $taxDataPrice;
                                                        } else {
                                                            $taxesData[$taxe->name] = $taxDataPrice;
                                                        }
                                                    }
                                                ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td><?php echo e($key + 1); ?></td>
                                                <td><?php echo e(!empty($iteam->product()) ? $iteam->product()->name : ''); ?></td>
                                                <td><?php echo e($iteam->quantity); ?></td>
                                                <td><?php echo e(utility::priceFormat($company_setting, $iteam->price)); ?></td>
                                                <td>
                                                    <?php echo e(utility::priceFormat($company_setting, $iteam->discount)); ?>


                                                </td>

                                                <td>
                                                    <?php if(!empty($iteam->tax)): ?>
                                                        <table>
                                                            <?php $totalTaxRate = 0;?>
                                                            <?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php
                                                                    $taxPrice = App\Models\Utility::taxRate($tax->rate, $iteam->price, $iteam->quantity);
                                                                    $totalTaxPrice += $taxPrice;
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo e($tax->name . ' (' . $tax->rate . '%)'); ?></td>
                                                                    <td><?php echo e(utility::priceFormat($company_setting, $taxPrice)); ?>

                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </table>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>

                                                <td><?php echo e(!empty($iteam->description) ? $iteam->description : '-'); ?></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $iteam->price * $iteam->quantity)); ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td><b><?php echo e(__('Total')); ?></b></td>
                                                <td><b><?php echo e($totalQuantity); ?></b></td>
                                                <td><b><?php echo e(utility::priceFormat($company_setting, $totalRate)); ?></b></td>
                                                <td>
                                                    <b><?php echo e(utility::priceFormat($company_setting, $totalDiscount)); ?></b>

                                                </td>
                                                <td><b><?php echo e(utility::priceFormat($company_setting, $totalTaxPrice)); ?></b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b><?php echo e(__('Sub Total')); ?></b></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->getSubTotal())); ?></td>
                                            </tr>

                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b><?php echo e(__('Discount')); ?></b></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->getTotalDiscount())); ?>

                                                </td>
                                            </tr>

                                            <?php if(!empty($taxesData)): ?>
                                                <?php $__currentLoopData = $taxesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxName => $taxPrice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b><?php echo e($taxName); ?></b></td>
                                                        <td class="text-end">
                                                            <?php echo e(utility::priceFormat($company_setting, $taxPrice)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="blue-text text-end"><b><?php echo e(__('Total')); ?></b></td>
                                                <td class="blue-text text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->getTotal())); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b><?php echo e(__('Paid')); ?></b></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->getTotal() - $bill->getDue() - $bill->billTotalDebitNote())); ?>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b><?php echo e(__('Debit Note')); ?></b></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->billTotalDebitNote())); ?>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b><?php echo e(__('Due')); ?></b></td>
                                                <td class="text-end">
                                                    <?php echo e(utility::priceFormat($company_setting, $bill->getDue())); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-10 offset-1">
        <h5 class="h4 d-inline-block font-weight-400 mb-4"><?php echo e(__('Payment Summary')); ?></h5>
        <div class="card">
            <div class="card-body table-border-style py-0">
                <div class="table-responsive m-0">
                    <table class="table ">
                        <tr>
                            <th class="text-dark"><?php echo e(__('Date')); ?></th>
                            <th class="text-dark"><?php echo e(__('Amount')); ?></th>
                            <th class="text-dark"><?php echo e(__('Account')); ?></th>
                            <th class="text-dark"><?php echo e(__('Reference')); ?></th>
                            <th class="text-dark"><?php echo e(__('Description')); ?></th>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete payment bill')): ?>
                                <th class="text-dark"><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        <?php $__empty_1 = true; $__currentLoopData = $bill->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(utility::dateFormat($company_setting, $payment->date)); ?></td>
                                <td><?php echo e(utility::priceFormat($company_setting, $payment->amount)); ?></td>
                                <td><?php echo e(!empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : ''); ?>

                                </td>
                                <td><?php echo e($payment->reference); ?></td>
                                <td><?php echo e($payment->description); ?></td>
                                <td class="text-dark">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete bill product')): ?>
                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-toggle="tooltip"
                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                            data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($payment->id); ?>').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        <?php echo Form::open([
                                            'method' => 'post',
                                            'route' => ['bill.payment.destroy', $bill->id, $payment->id],
                                            'id' => 'delete-form-' . $payment->id,
                                        ]); ?>

                                        <?php echo Form::close(); ?>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-dark">
                                    <p><?php echo e(__('No Data Found')); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-10 offset-1">
        <h5 class="h4 d-inline-block font-weight-400 mb-4"><?php echo e(__('Debit Note Summary')); ?></h5>
        <div class="card">
            <div class="card-body table-border-style py-0">
                <div class="table-responsive m-0">
                    <table class="table ">
                        <tr>
                            <th class="text-dark"><?php echo e(__('Date')); ?></th>
                            <th class="text-dark"><?php echo e(__('Amount')); ?></th>
                            <th class="text-dark"><?php echo e(__('Description')); ?></th>
                            <?php if(Gate::check('edit debit note') || Gate::check('delete debit note')): ?>
                                <th class="text-dark"><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        <?php $__empty_1 = true; $__currentLoopData = $bill->debitNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$debitNote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(utility::dateFormat($company_setting, $debitNote->date)); ?></td>
                                <td><?php echo e(utility::priceFormat($company_setting, $debitNote->amount)); ?></td>
                                <td><?php echo e($debitNote->description); ?></td>
                                <td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit debit note')): ?>
                                        <a data-url="<?php echo e(route('bill.edit.debit.note', [$debitNote->bill, $debitNote->id])); ?>"
                                            data-ajax-popup="true" data-title="<?php echo e(__('Add Debit Note')); ?>" href="#"
                                            class="mx-3 btn btn-sm align-items-center" data-toggle="tooltip"
                                            data-original-title="<?php echo e(__('Edit')); ?>">
                                            <i class="ti ti-edit text-white"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete debit note')): ?>
                                        <a href="#" class="mx-3 btn btn-sm align-items-center " data-toggle="tooltip"
                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                            data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($debitNote->id); ?>').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        <?php echo Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['bill.delete.debit.note', $debitNote->bill, $debitNote->id],
                                            'id' => 'delete-form-' . $debitNote->id,
                                        ]); ?>

                                        <?php echo Form::close(); ?>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-dark">
                                    <p><?php echo e(__('No Data Found')); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.invoicepayheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/taim0or/Documents/accounting/resources/views/bill/billpay.blade.php ENDPATH**/ ?>