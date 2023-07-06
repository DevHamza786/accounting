<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: "<?php echo e(__('Unpaid')); ?>",
                        data: <?php echo json_encode($billChartData['data']['unpaid']); ?>

                    }, {
                        name: "<?php echo e(__('Paid')); ?>",
                        data: <?php echo json_encode($billChartData['data']['paid']); ?>

                    }, {
                        name: "<?php echo e(__('Partial Paid')); ?>",
                        data: <?php echo json_encode($billChartData['data']['partial']); ?>

                    }, {
                        name: "<?php echo e(__('Due')); ?>",
                        data: <?php echo json_encode($billChartData['data']['due']); ?>

                    },

                ],

                chart: {
                    height: 300,
                    type: 'area',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: <?php echo json_encode($billChartData['month']); ?>,
                    title: {
                        text: '<?php echo e(__("Months")); ?>'
                    }
                },
                colors: ['#6fd944', '#6fd944'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#ffa21d', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '<?php echo e(__("Amount")); ?>'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <!-- <li class="breadcrumb-item"><a href="<?php echo e(route('vender.dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li> -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-fill text-limit">
                                            <h6 class="progress-text mb-1 text-sm d-block text-limit">   <?php echo e(number_format($billChartData['progressData']['unpaidPr'], App\Models\Utility::getValByName('decimal_number'), '.', '').'%'); ?></h6>
                                            <div class="progress progress-xs mb-0">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo e($billChartData['progressData']['unpaidPr']); ?>%;" aria-valuenow="<?php echo e($billChartData['progressData']['unpaidPr']); ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between text-xs text-muted text-right mt-1">
                                                <div>
                                                    <span class="font-bold text-danger"><?php echo e(__('Unpaid')); ?></span>
                                                </div>
                                                <div>
                                                    <?php echo e($billChartData['progressData']['totalBill'] .'/'.$billChartData['progressData']['totalUnpaidBill']); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-fill text-limit">
                                            <h6 class="progress-text mb-1 text-sm d-block text-limit"> <?php echo e(number_format($billChartData['progressData']['paidPr'], App\Models\Utility::getValByName('decimal_number'), '.', '') .' %'); ?></h6>
                                            <div class="progress progress-xs mb-0">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($billChartData['progressData']['paidPr']); ?>%;" aria-valuenow="<?php echo e($billChartData['progressData']['paidPr']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between text-xs text-muted text-right mt-1">
                                                <div>
                                                    <span class="font-bold text-success"><?php echo e(__('Paid')); ?></span>
                                                </div>
                                                <div>
                                                    <?php echo e($billChartData['progressData']['totalBill'] .'/'.$billChartData['progressData']['totalPaidBill']); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-fill text-limit">
                                            <h6 class="progress-text mb-1 text-sm d-block text-limit"> <?php echo e(number_format($billChartData['progressData']['partialPr'], App\Models\Utility::getValByName('decimal_number'), '.', '').'%'); ?></h6>
                                            <div class="progress progress-xs mb-0">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e($billChartData['progressData']['partialPr']); ?>%;" aria-valuenow="<?php echo e($billChartData['progressData']['partialPr']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between text-xs text-muted text-right mt-1">
                                                <div>
                                                    <span class="font-bold text-info"><?php echo e(__('Partial Paid')); ?></span>
                                                </div>
                                                <div>
                                                    <?php echo e($billChartData['progressData']['totalBill'] .'/'.$billChartData['progressData']['totalPartialBill']); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-fill text-limit">
                                            <h6 class="progress-text mb-1 text-sm d-block text-limit"> <?php echo e(number_format($billChartData['progressData']['duePr'], App\Models\Utility::getValByName('decimal_number'), '.', '').'%'); ?></h6>
                                            <div class="progress progress-xs mb-0">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e($billChartData['progressData']['duePr']); ?>%;" aria-valuenow="<?php echo e($billChartData['progressData']['duePr']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between text-xs text-muted text-right mt-1">
                                                <div>
                                                    <span class="font-bold text-warning"><?php echo e(__('Due')); ?></span>
                                                </div>
                                                <div>
                                                    <?php echo e($billChartData['progressData']['totalBill'] .'/'.$billChartData['progressData']['totalDueBill']); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-xxl-12 mt-4">
            <h4 class="h4 font-400"><?php echo e(__('Current year').' - '.date('Y')); ?></h4>
            <div class="card mt-3">
                <div class="chart mt-3">
                    <div id="chart-sales" data-color="primary" data-height="280" class="p-3"></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/vender/dashboard.blade.php ENDPATH**/ ?>