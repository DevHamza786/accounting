<?php echo e(Form::open(array('url' => 'contract'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('subject', __('Subject'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::text('subject', '', array('class' => 'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('customer', __('Customer'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::select('customer', $customers,null, array('class' => 'form-control ','required'=>'required'))); ?>

        </div>
        
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Contract Type'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::select('type', $contractTypes,null, array('class' => 'form-control ','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('value', __('Contract Value'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::number('value', '', array('class' => 'form-control','required'=>'required','stage'=>'0.01'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('start_date', __('Start Date'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::date('start_date', '', array('class' => 'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('end_date', __('End Date'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::date('end_date', '', array('class' => 'form-control','required'=>'required'))); ?>

        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('description', __('Description'),['class' => 'col-form-label'])); ?>

            <?php echo Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']); ?>

        </div>
    </div>
   
    
</div>
<div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
        <?php echo e(Form::submit(__('Create'),array('class'=>'btn  btn-primary'))); ?>

    </div>
<?php echo e(Form::close()); ?>




<script src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>
<script>
    if ($(".multi-select").length > 0) {
              $( $(".multi-select") ).each(function( index,element ) {
                  var id = $(element).attr('id');
                     var multipleCancelButton = new Choices(
                          '#'+id, {
                              removeItemButton: true,
                          }
                      );
              });
         }
  </script><?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/contract/create.blade.php ENDPATH**/ ?>