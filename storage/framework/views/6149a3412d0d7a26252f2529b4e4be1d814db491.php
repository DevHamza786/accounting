<?php echo e(Form::model($contractType, array('route' => array('contractType.update', $contractType->id), 'method' => 'PUT'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('name', __('Name'),['class' => 'col-form-label'])); ?>

            <?php echo e(Form::text('name', null, array('class' => 'form-control','required'=>'required'))); ?>

        </div>
    </div>
   
</div>

<div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
        <?php echo e(Form::submit(__('Update'),array('class'=>'btn  btn-primary'))); ?>

    </div>

<?php echo e(Form::close()); ?>

<?php /**PATH /media/disk/server/q-boomverzorger/public_html/accounting/resources/views/contractType/edit.blade.php ENDPATH**/ ?>