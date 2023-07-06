<?php
use App\Models\Utility;

?>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Invoice Detail')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('css-page'); ?>
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }

    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script type="text/javascript">
        <?php if($invoice->getDue() > 0 && !empty($company_payment_setting) && $company_payment_setting['is_stripe_enabled'] == 'on' && !empty($company_payment_setting['stripe_key']) && !empty($company_payment_setting['stripe_secret'])): ?>

            var stripe = Stripe('<?php echo e($company_payment_setting['stripe_key']); ?>');
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            var style = {
            base: {
            // Add your base input styles here. For example:
            fontSize: '14px',
            color: '#32325d',
            },
            };

            // Create an instance of the card Element.
            var card = elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>.
                card.mount('#card-element');

                // Create a token or display an error when the form is submitted.
                var form = document.getElementById('payment-form');
                form.addEventListener('submit', function (event) {
                event.preventDefault();

                stripe.createToken(card).then(function (result) {
                if (result.error) {
                $("#card-errors").html(result.error.message);
                show_toastr('Error', result.error.message, 'error');
                } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
                }
                });
                });

                function stripeTokenHandler(token) {
                // Insert the token ID into the form so it gets submitted to the server
                var form = document.getElementById('payment-form');
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                // Submit the form
                form.submit();
                }

        <?php endif; ?>

        <?php if(isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on'): ?>
            $(document).on("click", "#pay_with_paystack", function () {
            console.log('pay_with_paystack');
            $('#paystack-payment-form').ajaxForm(function (res) {
            var amount = res.total_price;
            if (res.flag == 1) {
            var paystack_callback = "<?php echo e(url('customer/invoice/paystack')); ?>";

            var handler = PaystackPop.setup({
            key: '<?php echo e($company_payment_setting['paystack_public_key']); ?>',
            email: res.email,
            amount: res.total_price * 100,
            currency: res.currency,
            ref: 'pay_ref_id' + Math.floor((Math.random() * 1000000000) +
            1
            ),
            metadata: {
            custom_fields: [{
            display_name: "Email",
            variable_name: "email",
            value: res.email,
            }]
            },

            callback: function (response) {

                window.location.href = '<?php echo e(url("customer/invoice/paystack")); ?>'+'/'+'<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>'+'/'+amount+ '/' + response.reference;


            // window.location.href = paystack_callback + '/' + response.reference + '/' + '<?php echo e(encrypt($invoice->id)); ?>' +
            // '?amount=' + amount;

            },
            onClose: function () {
            alert('window closed');
            }
            });
            handler.openIframe();
            } else if (res.flag == 2) {
            toastrs('Error', res.msg, 'msg');
            } else {
            toastrs('Error', res.message, 'msg');
            }

            }).submit();
            });
        <?php endif; ?>

        <?php if(isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on'): ?>
            // Flaterwave Payment
            $(document).on("click", "#pay_with_flaterwave", function () {
            $('#flaterwave-payment-form').ajaxForm(function (res) {

            if (res.flag == 1) {
            var amount = res.total_price;
            var API_publicKey = '<?php echo e($company_payment_setting['flutterwave_public_key']); ?>';
            var nowTim = "<?php echo e(date('d-m-Y-h-i-a')); ?>";
            var flutter_callback = "<?php echo e(url('/customer/invoice/flaterwave')); ?>";
            var x = getpaidSetup({
            PBFPubKey: API_publicKey,
            customer_email: '<?php echo e($invoice->customer->email); ?>',
            amount: res.total_price,
            currency: '<?php echo e(App\Models\Utility::getValByName('site_currency')); ?>',
            txref: nowTim + '__' + Math.floor((Math.random() * 1000000000)) + 'fluttpay_online-' + '<?php echo e(date('Y-m-d')); ?>' + '?amount=' + amount,
            meta: [{
            metaname: "payment_id",
            metavalue: "id"
            }],
            onclose: function () {
            },
            callback: function (response) {
            var txref = response.tx.txRef;
            if (
            response.tx.chargeResponseCode == "00" ||
            response.tx.chargeResponseCode == "0"
            ) {
                window.location.href = '<?php echo e(url("customer/invoice/flaterwave")); ?>'+'/'+'<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>'+'/'+txref;

            // window.location.href = flutter_callback + '/' + txref + '/' +
            // '<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>'+ '?amount=' + amount;
            } else {
            // redirect to a failure page.
            }
            x.close(); // use this to close the modal immediately after payment.
            }
            });
            } else if (res.flag == 2) {
            toastrs('Error', res.msg, 'msg');
            } else {
            toastrs('Error', data.message, 'msg');
            }

            }).submit();
            });
        <?php endif; ?>

        <?php if(isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on'): ?>
            // Razorpay Payment
            $(document).on("click", "#pay_with_razorpay", function () {
            $('#razorpay-payment-form').ajaxForm(function (res) {
            if (res.flag == 1) {
            var amount = res.total_price;
            var razorPay_callback = '<?php echo e(url('/customer/invoice/razorpay')); ?>';
            var totalAmount = res.total_price * 100;
            var coupon_id = res.coupon;
            var options = {
            "key": "<?php echo e($company_payment_setting['razorpay_public_key']); ?>", // your Razorpay Key Id
            "amount": totalAmount,
            "name": 'Plan',
            "currency": '<?php echo e(App\Models\Utility::getValByName('site_currency')); ?>',
            "description": "",
            "handler": function (response) {
                 
                window.location.href = '<?php echo e(url("customer/invoice/razorpay")); ?>'+'/'+'<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>'+'/'+amount;

            // window.location.href = razorPay_callback + '/' + response.razorpay_payment_id + '/' +
            // '<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>' + '?amount=' + amount;
            },
            "theme": {
            "color": "#528FF0"
            }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            } else if (res.flag == 2) {
            toastrs('Error', res.msg, 'msg');
            } else {
            toastrs('Error', data.message, 'msg');
            }

            }).submit();
            });
        <?php endif; ?>


        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('Success', '<?php echo e(__('Link Copy on Clipboard')); ?>', 'success')
        });
    </script>
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
        $customer=$invoice->customer;
    ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send invoice')): ?>

        <?php if($invoice->status != 4): ?>
            
        <?php endif; ?>
    <?php endif; ?>

    <?php if($invoice->status != 0): ?>
    <div class="row justify-content-center align-items-center mb-3">
        <div class="col-10 d-flex align-items-center justify-content-between justify-content-md-end">
            <?php if(!empty($invoicePayment)): ?>
                <div class="all-button-box mx-2">
                    <a href="#" class="btn btn-xs btn-white btn-icon-only width-auto"
                        data-url="<?php echo e(route('invoice.credit.note', $invoice->id)); ?>" data-ajax-popup="true"
                        data-title="<?php echo e(__('Add Credit Note')); ?>">
                        <?php echo e(__('Add Credit Note')); ?>

                    </a>
                </div>
            <?php endif; ?>
            
            <div class="all-button-box mr-3">
                <a href="<?php echo e(route('invoice.pdf', Crypt::encrypt($invoice->id))); ?>" target="_blank" class="btn btn-primary"><?php echo e(__('Download')); ?></a>
                <?php if($invoice->getDue() > 0): ?>
                    <?php if(\Auth::guard('customer')->check()): ?>
                        


                        <a href="#" class="btn btn-xs btn-primary btn-icon-only width-auto" title="<?php echo e(__('Pay Now')); ?>" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-plus"></i> <?php echo e(__('Pay Now')); ?></a>
                    <?php endif; ?>
                <?php endif; ?>

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
                                    <h2><?php echo e(__('Invoice')); ?></h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">
                                        <?php echo e(Utility::invoiceNumberFormat($company_setting, $invoice->invoice_id)); ?></h3>
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
                                                <?php if(\Auth::check()): ?>
                                                <?php echo e(\Auth::user()->dateFormat($invoice->issue_date)); ?><br><br>
                                                <?php else: ?>
                                                <?php echo e(\App\Models\User::dateFormat($invoice->issue_date)); ?><br><br>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <small>
                                                <strong><?php echo e(__('Due Date')); ?> :</strong><br>
                                                <?php if(\Auth::check()): ?>
                                                <?php echo e(\Auth::user()->dateFormat($invoice->due_date)); ?><br><br>
                                                <?php else: ?>
                                                <?php echo e(\App\Models\User::dateFormat($invoice->due_date)); ?><br><br>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php if(!empty($customer->billing_name)): ?>
                                    <div class="col">
                                        <small class="font-style">
                                            <strong><?php echo e(__('Billed To')); ?> :</strong><br>
                                            <?php echo e(!empty($customer->billing_name) ? $customer->billing_name : ''); ?><br>
                                            <?php echo e(!empty($customer->billing_address) ? $customer->billing_address : ''); ?><br>
                                            <?php echo e(!empty($customer->billing_zip) ? $customer->billing_zip : ''); ?>

                                            <?php echo e(!empty($customer->billing_city) ? $customer->billing_city : ''); ?><br>
                                            <?php echo e(!empty($customer->billing_state) ? $customer->billing_state :''); ?>

                                            <?php echo e(!empty($customer->billing_country) ? $customer->billing_country : ''); ?>

                                            <?php echo e(!empty($customer->billing_phone) ? $customer->billing_phone : ''); ?><br>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                <?php if(App\Models\Utility::getValByName('shipping_display') == 'on'): ?>
                                    <div class="col">
                                        <small>
                                            <strong><?php echo e(__('Shipped To')); ?> :</strong><br>
                                            <?php echo e(!empty($customer->shipping_name) ? $customer->shipping_name : ''); ?><br>
                                            <?php echo e(!empty($customer->shipping_address) ? $customer->shipping_address : ''); ?><br>
                                            <?php echo e(!empty($customer->shipping_zip) ? $customer->shipping_zip : ''); ?>

                                            <?php echo e(!empty($customer->shipping_city) ? $customer->shipping_city : ''); ?><br>
                                            <?php echo e(!empty($customer->shipping_state) ? $customer->shipping_state : ''); ?>,<?php echo e(!empty($customer->shipping_country) ? $customer->shipping_country : ''); ?>

                                            <?php echo e(!empty($customer->shipping_phone) ? $customer->shipping_phone : ''); ?><br>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                    <div class="col">
                                        <div class="float-end mt-3">
                                            <?php echo DNS2D::getBarcodeHTML(route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)), 'QRCODE', 2, 2); ?>

                                        </div>
                                    </div>

                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong><?php echo e(__('Status')); ?> :</strong><br>
                                        <?php if($invoice->status == 0): ?>
                                            <span
                                                class="badge bg-primary p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 1): ?>
                                            <span
                                                class="badge bg-warning p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 2): ?>
                                            <span
                                                class="badge bg-danger p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 3): ?>
                                            <span
                                                class="badge bg-info p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 4): ?>
                                            <span
                                                class="badge bg-success p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>



                                <?php if(!empty($customFields) && count($invoice->customField) > 0): ?>
                                    <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col text-end">
                                            <small>
                                                <strong><?php echo e($field->name); ?> :</strong><br>
                                                <?php echo e(!empty($invoice->customField) ? $invoice->customField[$field->id] : '-'); ?>

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
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 ">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark"><?php echo e(__('Product')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Quantity')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Rate')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Discount')); ?></th>
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
                                                    <td><?php echo e(Utility::priceFormat($company_setting, $iteam->price)); ?></td>
                                                    <td>
                                                        
                                                            <?php echo e(Utility::priceFormat($company_setting, $iteam->discount)); ?>

                                                        
                                                    </td>
                                                    

                                                    <td>

                                                        <?php if(!empty($iteam->tax)): ?>
                                                            <table>
                                                                <?php
                                                                    $totalTaxRate = 0;
                                                                    $totalTaxPrice=0;
                                                                ?>
                                                                <?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                                    <?php
                                                                        $taxPrice=App\Models\Utility::taxRate($tax->rate,$iteam->price,$iteam->quantity,$iteam->discount) ;
                                                                        $totalTaxPrice+=$taxPrice;
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo e($tax->name .' ('.$tax->rate .'%)'); ?></td>
                                                                        <td><?php echo e(Utility::priceFormat($company_setting,$taxPrice)); ?></td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </table>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(!empty($iteam->description) ? $iteam->description : '-'); ?></td>
                                                    <td class="text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $iteam->price * $iteam->quantity)); ?>

                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    
                                                    <td><b><?php echo e(__('Total')); ?></b></td>
                                                    <td><b><?php echo e($totalQuantity); ?></b></td>
                                                    <td><b><?php echo e(Utility::priceFormat($company_setting, $totalRate)); ?></b></td>
                                                    <td>
                                                        
                                                            <b><?php echo e(Utility::priceFormat($company_setting, $totalDiscount)); ?></b>
                                                        
                                                    </td>
                                                    <td><b><?php echo e(Utility::priceFormat($company_setting, $totalTaxPrice)); ?></b></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b><?php echo e(__('Sub Total')); ?></b></td>
                                                    <td class="text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $invoice->getSubTotal())); ?>

                                                    </td>
                                                </tr>
                                                
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b><?php echo e(__('Discount')); ?></b></td>
                                                        <td class="text-end">
                                                            <?php echo e(Utility::priceFormat($company_setting, $invoice->getTotalDiscount())); ?>

                                                        </td>
                                                    </tr>
                                               
                                                
                                                    <?php if(!empty($taxesData)): ?>
                                                            <?php
                                                                $totalTaxRate = 0;
                                                                $totalTaxPrice=0;
                                                            ?>
                                                        <?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                            <?php
                                                                $taxPrice=App\Models\Utility::taxRate($tax->rate,$iteam->price,$iteam->quantity,$iteam->discount) ;
                                                                $totalTaxPrice+=$taxPrice;
                                                            ?>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="text-end"><b><?php echo e($tax->name); ?></b></td>
                                                                    <td class="text-end"><?php echo e(Utility::priceFormat($company_setting,$taxPrice)); ?></td>
                                                                
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endif; ?>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="blue-text text-end"><b><?php echo e(__('Total')); ?></b></td>
                                                    <td class="blue-text text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $invoice->getTotal())); ?>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b><?php echo e(__('Paid')); ?></b></td>
                                                    <td class="text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote())); ?>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b><?php echo e(__('Credit Note')); ?></b></td>
                                                    <td class="text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $invoice->invoiceTotalCreditNote())); ?>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b><?php echo e(__('Due')); ?></b></td>
                                                    <td class="text-end">
                                                        <?php echo e(Utility::priceFormat($company_setting, $invoice->getDue())); ?>

                                                    </td>
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
            <h5 class="h4 d-inline-block font-weight-400 mb-4"><?php echo e(__('Receipt Summary')); ?></h5>
            <div class="card">
                <div class="card-body table-border-style
">
                    <div class="table-responsive">
                        <table class="table ">
                            <tr>
                                <th class="text-dark"><?php echo e(__('Date')); ?></th>
                                <th class="text-dark"><?php echo e(__('Amount')); ?></th>
                                <th class="text-dark"><?php echo e(__('Payment Type')); ?></th>
                                <th class="text-dark"><?php echo e(__('Account')); ?></th>
                                <th class="text-dark"><?php echo e(__('Reference')); ?></th>
                                <th class="text-dark"><?php echo e(__('Description')); ?></th>
                                <th class="text-dark"><?php echo e(__('Receipt')); ?></th>
                                <th class="text-dark"><?php echo e(__('OrderId')); ?></th>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete payment invoice')): ?>
                                    <th class="text-dark"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(Utility::dateFormat($company_setting, $payment->date)); ?></td>
                                    <td><?php echo e(Utility::priceFormat($company_setting, $payment->amount)); ?></td>
                                    <td><?php echo e($payment->payment_type); ?></td>
                                    <td><?php echo e(!empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '--'); ?>

                                    </td>
                                    <td><?php echo e(!empty($payment->reference) ? $payment->reference : '--'); ?></td>
                                    <td><?php echo e(!empty($payment->description) ? $payment->description : '--'); ?></td>
                                    <td><?php if(!empty($payment->receipt)): ?><a href="<?php echo e($payment->receipt); ?>" target="_blank"> <i class="ti ti-file"></i></a><?php else: ?> -- <?php endif; ?></td>
                                    <td><?php echo e(!empty($payment->order_id) ? $payment->order_id : '--'); ?></td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete invoice product')): ?>
                                        <td>
                                            <a href="#" class="mx-3 btn btn-sm align-items-center" data-toggle="tooltip"
                                                data-original-title="<?php echo e(__('Delete')); ?>"
                                                data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                data-confirm-yes="document.getElementById('delete-form-<?php echo e($payment->id); ?>').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            <?php echo Form::open(['method' => 'post', 'route' => ['invoice.payment.destroy', $invoice->id, $payment->id], 'id' => 'delete-form-' . $payment->id]); ?>

                                            <?php echo Form::close(); ?>

                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(Gate::check('delete invoice product') ? '9' : '8'); ?>"
                                        class="text-center text-dark">
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
            <h5 class="h4 d-inline-block font-weight-400 mb-4"><?php echo e(__('Credit Note Summary')); ?></h5>
            <div class="card">
                <div class="card-body table-border-style
">
                    <div class="table-responsive">
                        <table class="table ">
                            <tr>
                                <th class="text-dark"><?php echo e(__('Date')); ?></th>
                                <th class="text-dark" class=""><?php echo e(__('Amount')); ?></th>
                                <th class="text-dark" class=""><?php echo e(__('Description')); ?></th>
                                <?php if(Gate::check('edit credit note') || Gate::check('delete credit note')): ?>
                                    <th class="text-dark"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->creditNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$creditNote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(Utility::dateFormat($company_setting, $creditNote->date)); ?></td>
                                    <td class="">
                                        <?php echo e(Utility::priceFormat($company_setting, $creditNote->amount)); ?></td>
                                    <td class=""><?php echo e($creditNote->description); ?></td>
                                    <td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit credit note')): ?>
                                            <a data-url="<?php echo e(route('invoice.edit.credit.note', [$creditNote->invoice, $creditNote->id])); ?>"
                                                data-ajax-popup="true" data-title="<?php echo e(__('Add Credit Note')); ?>"
                                                data-toggle="tooltip" data-original-title="<?php echo e(__('Credit Note')); ?>" href="#"
                                                class="mx-3 btn btn-sm align-items-center" data-toggle="tooltip"
                                                data-original-title="<?php echo e(__('Edit')); ?>">
                                                <i class="ti ti-edit text-white"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete credit note')): ?>
                                            <a href="#" class="mx-3 btn btn-sm align-items-center " data-toggle="tooltip"
                                                data-original-title="<?php echo e(__('Delete')); ?>"
                                                data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                data-confirm-yes="document.getElementById('delete-form-<?php echo e($creditNote->id); ?>').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['invoice.delete.credit.note', $creditNote->invoice, $creditNote->id], 'id' => 'delete-form-' . $creditNote->id]); ?>

                                            <?php echo Form::close(); ?>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <p class="text-dark"><?php echo e(__('No Data Found')); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <?php if($invoice->getDue() > 0): ?>
        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel"><?php echo e(__('Add Payment')); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="">
                            <section class="">

                                <?php if((isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on') ||
        (isset($company_payment_setting['is_paypal_enabled']) && $company_payment_setting['is_paypal_enabled'] == 'on') ||
        (isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on') ||
        (isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on') ||
        (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on') ||
        (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on') ||
        (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on') ||
        (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on') ||
        (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on') ||
        (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')||
        (isset($company_payment_setting['is_paymentwall_enabled']) && $company_payment_setting['is_paymentwall_enabled'] == 'on') ): ?>

        <ul class="nav nav-pills  mb-3" id="pills-tab"  role="tablist">
                                        <?php if($company_payment_setting['is_stripe_enabled'] == 'on' && !empty($company_payment_setting['stripe_key']) && !empty($company_payment_setting['stripe_secret'])): ?>
                                            <li class="nav-item mb-2">
                                                <button class=" active btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#stripe-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Stripe')); ?></button>
                                            </li>
                                        <?php endif; ?>
                                        <?php if($company_payment_setting['is_paypal_enabled'] == 'on' && !empty($company_payment_setting['paypal_client_id']) && !empty($company_payment_setting['paypal_secret_key'])): ?>
                                            <li class="nav-item mb-2">
                                                <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#paypal-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Paypal')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if($company_payment_setting['is_paystack_enabled'] == 'on' && !empty($company_payment_setting['paystack_public_key']) && !empty($company_payment_setting['paystack_secret_key'])): ?>
                                            <li class="nav-item mb-2">
                                            
                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#paystack-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Paystack')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#flutterwave-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Flutterwave')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#razorpay-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Razorpay')); ?></button>
                                            </li>
                                        <?php endif; ?>


                                        <?php if(isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#mercado-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Mercado')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#paytm-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Paytm')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#mollie-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Mollie')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#skrill-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Skrill')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if(isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on'): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#coingate-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('Coingate')); ?></button>
                                            </li>
                                        <?php endif; ?>
                                        <?php if($company_payment_setting['is_paymentwall_enabled'] == 'on' && !empty($company_payment_setting['paymentwall_public_key']) && !empty($company_payment_setting['paymentwall_secret_key'])): ?>
                                            <li class="nav-item mb-2">

                                                    <button class=" btn btn-outline-success btn-sm" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#paymentwall-payment" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?php echo e(__('PaymentWall')); ?></button>
                                            </li>
                                        <?php endif; ?>

                                    </ul>
                                <?php endif; ?>
                                <div class="tab-content">
                                    <?php if(!empty($company_payment_setting) && ($company_payment_setting['is_stripe_enabled'] == 'on' && !empty($company_payment_setting['stripe_key']) && !empty($company_payment_setting['stripe_secret']))): ?>
                                        <div class="tab-pane fade active show" id="stripe-payment" role="tabpanel"
                                            aria-labelledby="stripe-payment">
                                            <form method="post"
                                                action="<?php echo e(route('customer.invoice.payment', $invoice->id)); ?>"
                                                class="require-validation" id="payment-form">
                                                <?php echo csrf_field(); ?>
                                                <div class="row">
                                                    <div class="col-sm-8">
                                                        <div class="custom-radio">
                                                            <label
                                                                class="font-16 font-weight-bold"><?php echo e(__('Credit / Debit Card')); ?></label>
                                                        </div>
                                                        <p class="mb-0 pt-1 text-sm">
                                                            <?php echo e(__('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.')); ?>

                                                        </p>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="card-name-on"><?php echo e(__('Name on card')); ?></label>
                                                            <input type="text" name="name" id="card-name-on"
                                                                class="form-control required"
                                                                placeholder="<?php echo e($invoice->customer->name); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div id="card-element">

                                                        </div>
                                                        <div id="card-errors" role="alert"></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <br>
                                                        <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                        <div class="input-group">
                                                            <span class="input-group-prepend"><span
                                                                    class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
                                                                value="<?php echo e($invoice->getDue()); ?>" min="0" step="0.01"
                                                                max="<?php echo e($invoice->getDue()); ?>" id="amount">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="error" style="display: none;">
                                                            <div class='alert-danger alert'>
                                                                <?php echo e(__('Please correct the errors and try again.')); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <button class="btn btn-sm btn-primary m-r-10"
                                                        type="submit"><?php echo e(__('Make Payment')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && ($company_payment_setting['is_paypal_enabled'] == 'on' && !empty($company_payment_setting['paypal_client_id']) && !empty($company_payment_setting['paypal_secret_key']))): ?>
                                        <div class="tab-pane fade " id="paypal-payment" role="tabpanel"
                                            aria-labelledby="paypal-payment">
                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="<?php echo e(route('customer.invoice.with.paypal', $invoice->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                        <div class="input-group">
                                                            <span class="input-group-prepend"><span
                                                                    class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
                                                                value="<?php echo e($invoice->getDue()); ?>" min="0" step="0.01"
                                                                max="<?php echo e($invoice->getDue()); ?>" id="amount">
                                                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                <span class="invalid-amount" role="alert">
                                                                    <strong><?php echo e($message); ?></strong>
                                                                </span>
                                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <button class="btn btn-sm btn-primary m-r-10" name="submit"
                                                        type="submit"><?php echo e(__('Make Payment')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on' && !empty($company_payment_setting['paystack_public_key']) && !empty($company_payment_setting['paystack_secret_key'])): ?>
                                        <div class="tab-pane fade " id="paystack-payment" role="tabpanel"
                                            aria-labelledby="paypal-payment">
                                            <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                                id="paystack-payment-form"
                                                action="<?php echo e(route('customer.invoice.pay.with.paystack')); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input class="btn btn-sm btn-primary m-r-10"
                                                        id="pay_with_paystack" type="button"
                                                        value="<?php echo e(__('Make Payment')); ?>">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on' && !empty($company_payment_setting['paystack_public_key']) && !empty($company_payment_setting['paystack_secret_key'])): ?>
                                        <div class="tab-pane fade " id="flutterwave-payment" role="tabpanel"
                                            aria-labelledby="paypal-payment">
                                            <form role="form"
                                                action="<?php echo e(route('customer.invoice.pay.with.flaterwave')); ?>"
                                                method="post" class="require-validation" id="flaterwave-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input class="btn btn-sm btn-primary m-r-10"
                                                        id="pay_with_flaterwave" type="button"
                                                        value="<?php echo e(__('Make Payment')); ?>">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade " id="razorpay-payment" role="tabpanel"
                                            aria-labelledby="paypal-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.razorpay')); ?>"
                                                method="post" class="require-validation" id="razorpay-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input class="btn btn-sm btn-primary m-r-10"
                                                        id="pay_with_razorpay" type="button"
                                                        value="<?php echo e(__('Make Payment')); ?>">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade " id="mercado-payment" role="tabpanel"
                                            aria-labelledby="mercado-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.mercado')); ?>"
                                                method="post" class="require-validation" id="mercado-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" id="pay_with_mercado"
                                                        value="<?php echo e(__('Make Payment')); ?>"
                                                        class="btn btn-sm btn-primary m-r-10">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade" id="paytm-payment" role="tabpanel"
                                            aria-labelledby="paytm-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.paytm')); ?>"
                                                method="post" class="require-validation" id="paytm-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="flaterwave_coupon"
                                                            class=" text-dark"><?php echo e(__('Mobile Number')); ?></label>
                                                        <input type="text" id="mobile" name="mobile"
                                                            class="form-control mobile" data-from="mobile"
                                                            placeholder="<?php echo e(__('Enter Mobile Number')); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" id="pay_with_paytm"
                                                        value="<?php echo e(__('Make Payment')); ?>"
                                                        class="btn btn-sm btn-primary m-r-10">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade " id="mollie-payment" role="tabpanel"
                                            aria-labelledby="mollie-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.mollie')); ?>"
                                                method="post" class="require-validation" id="mollie-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" id="pay_with_mollie"
                                                        value="<?php echo e(__('Make Payment')); ?>"
                                                        class="btn btn-sm btn-primary m-r-10">
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>


                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade " id="skrill-payment" role="tabpanel"
                                            aria-labelledby="skrill-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.skrill')); ?>"
                                                method="post" class="require-validation" id="skrill-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <?php
                                                    $skrill_data = [
                                                        'transaction_id' => md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id'),
                                                        'user_id' => 'user_id',
                                                        'amount' => 'amount',
                                                        'currency' => 'currency',
                                                    ];
                                                    session()->put('skrill_data', $skrill_data);

                                                ?>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" id="pay_with_skrill"
                                                        value="<?php echo e(__('Make Payment')); ?>"
                                                        class="btn btn-sm btn-primary m-r-10">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on'): ?>
                                        <div class="tab-pane fade " id="coingate-payment" role="tabpanel"
                                            aria-labelledby="coingate-payment">
                                            <form role="form" action="<?php echo e(route('customer.invoice.pay.with.coingate')); ?>"
                                                method="post" class="require-validation" id="coingate-payment-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id"
                                                    value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                <div class="form-group col-md-12">
                                                    <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?></span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                            min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>"
                                                            id="amount">

                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" id="pay_with_coingate"
                                                        value="<?php echo e(__('Make Payment')); ?>"
                                                        class="btn btn-sm btn-primary m-r-10">
                                                </div>

                                            </form>
                                        </div>
                                    <?php endif; ?>


                                        <?php if(!empty($company_payment_setting) && isset($company_payment_setting['is_paymentwall_enabled']) && $company_payment_setting['is_paymentwall_enabled'] == 'on' && !empty($company_payment_setting['paymentwall_public_key']) && !empty($company_payment_setting['paymentwall_secret_key'])): ?>
                                            <div class="tab-pane fade " id="paymentwall-payment" role="tabpanel"
                                                 aria-labelledby="paypal-payment">
                                                <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                                      id="paymentwall-payment-form"
                                                      action="<?php echo e(route('customer.invoice.paymentwallpayment')); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="invoice_id"
                                                           value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)); ?>">

                                                    <div class="form-group col-md-12">
                                                        <label for="amount"><?php echo e(__('Amount')); ?></label>
                                                        <div class="input-group">
                                                        <span class="input-group-prepend">
                                                            <span class="input-group-text"><?php echo e(App\Models\Utility::getValByName('site_currency')); ?>

                                                            </span>
                                                        </span>
                                                            <input class="form-control" required="required" min="0"
                                                                   name="amount" type="number" value="<?php echo e($invoice->getDue()); ?>"
                                                                   min="0" step="0.01" max="<?php echo e($invoice->getDue()); ?>" id="amount">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input class="btn btn-sm btn-primary m-r-10"
                                                               id="pay_with_paymentwall" type="submit"
                                                               value="<?php echo e(__('Make Payment')); ?>">
                                                    </div>

                                                </form>
                                            </div>
                                        <?php endif; ?>


                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.invoicepayheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/invoice/invoicepay.blade.php ENDPATH**/ ?>