
<?php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_favicon=\App\Models\Utility::getValByName('company_favicon');
    $SITE_RTL = env('SITE_RTL');
    $setting = \App\Models\Utility::colorset();
        $color = 'theme-3';
        if (!empty($setting['color'])) {
            $color = $setting['color'];
        }
        $SITE_RTL = 'theme-3';
        if (!empty($setting['SITE_RTL'])) {
            $SITE_RTL = $setting['SITE_RTL'];
        }
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo e($SITE_RTL == 'on'?'rtl':''); ?>">
<meta name="csrf-token" id="csrf-token" content="<?php echo e(csrf_token()); ?>">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?php echo e((\App\Models\Utility::getValByName('title_text')) ? App\Models\Utility::getValByName('title_text') : config('app.name', 'AccountGo')); ?> - <?php echo $__env->yieldContent('page-title'); ?></title>
    <link rel="icon" href="<?php echo e($logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')); ?>" type="image" sizes="16x16">


    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/animate.min.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/datepicker-bs5.min.css')); ?>">

    <!--bootstrap switch-->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/bootstrap-switch-button.min.css')); ?>">


    <!-- font css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/tabler-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/fontawesome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/material.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/main.css')); ?>">
    <!-- vendor css -->

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/flatpickr.min.css')); ?>">

    <?php if($SITE_RTL == 'on'): ?>
    
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-rtl.css')); ?>">
    <?php endif; ?>
    <?php if($setting['cust_darklayout'] == 'on'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-dark.css')); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/customizer.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>" id="main-style-link">
</head>


<body class="<?php echo e($color); ?>">
<div class="container-fluid container-application">
    <div class="main-content position-relative">
        <div class="page-content">
            <div class="page-title">
                <div class="row justify-content-between align-items-center">
                    <div class="col-xl-4 col-lg-4 col-md-4 d-flex align-items-center justify-content-between justify-content-md-start mb-3 mb-md-0">
                        <div class="d-inline-block">
                            <h5 class="h4 d-inline-block font-weight-400 mb-0 "><?php echo $__env->yieldContent('page-title'); ?></h5>
                        </div>
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-8 d-flex align-items-center justify-content-between justify-content-md-end">
                        <?php echo $__env->yieldContent('action-btn'); ?>
                    </div>
                </div>
            </div>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<!-- Required Js -->
<script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/jquery.form.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/perfect-scrollbar.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>


<script src="<?php echo e(asset('assets/js/plugins/datepicker-full.min.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/sweetalert2.all.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/simple-datatables.js')); ?>"></script>

<!-- sweet alert Js -->



<!--Botstrap switch-->
<script src="<?php echo e(asset('assets/js/plugins/bootstrap-switch-button.min.js')); ?>"></script>


<!-- Apex Chart -->
<script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/flatpickr.min.js')); ?>"></script>
<?php echo $__env->yieldPushContent('theme-script'); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>


<script>
    var toster_pos = "<?php echo e(env('SITE_RTL')=='on' ?'left' : 'right'); ?>";
</script>
<?php if(\App\Models\Utility::getValByName1('gdpr_cookie') == 'on'): ?>
    <script type="text/javascript">
        var defaults = {
            'messageLocales': {
                /*'en': 'We use cookies to make sure you can have the best experience on our website. If you continue to use this site we assume that you will be happy with it.'*/
                'en': "<?php echo e(\App\Models\Utility::getValByName1('cookie_text')); ?>"
            },
            'buttonLocales': {
                'en': 'Ok'
            },
            'cookieNoticePosition': 'bottom',
            'learnMoreLinkEnabled': false,
            'learnMoreLinkHref': '/cookie-banner-information.html',
            'learnMoreLinkText': {
                'it': 'Saperne di più',
                'en': 'Learn more',
                'de': 'Mehr erfahren',
                'fr': 'En savoir plus'
            },
            'buttonLocales': {
                'en': 'Ok'
            },
            'expiresIn': 30,
            'buttonBgColor': '#d35400',
            'buttonTextColor': '#fff',
            'noticeBgColor': '#051c4b',
            'noticeTextColor': '#fff',
            'linkColor': '#009fdd'
        };
    </script>
    <script src="<?php echo e(asset('assets/js/cookie.notice.js')); ?>"></script>
<?php endif; ?>

<?php if($message = Session::get('success')): ?>
    <script>
        show_toastr('Success', '<?php echo $message; ?>', 'success');
    </script>
<?php endif; ?>
<?php if($message = Session::get('error')): ?>
    <script>
        show_toastr('Error', '<?php echo $message; ?>', 'error');
    </script>
<?php endif; ?>
<?php echo $__env->yieldPushContent('script-page'); ?>
</body>
</html>
<?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/layouts/invoicepayheader.blade.php ENDPATH**/ ?>