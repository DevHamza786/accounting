






<?php echo e(Form::open(array('route' => array('invoice.payment', $invoice->id),'method'=>'post','enctype' => 'multipart/form-data'))); ?>

<div class="modal-body">

<div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('date', __('Date'),['class'=>'form-label'])); ?>

            <div class="form-icon-user">
                <?php echo e(Form::text('date', null, array('class' => 'form-control pc-datepicker-1','required'=>'required'))); ?>

            </div>
        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('amount', __('Amount'),['class'=>'form-label'])); ?>

            <div class="form-icon-user">
                <?php echo e(Form::text('amount',$invoice->getDue(), array('class' => 'form-control','required'=>'required'))); ?>

            </div>
        </div>
        <div class="form-group col-md-6">

                <?php echo e(Form::label('account_id', __('Account'),['class'=>'form-label'])); ?>

                <?php echo e(Form::select('account_id',$accounts,null, array('class' => 'form-control select2', 'required'=>'required'))); ?>


        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('reference', __('Reference'),['class'=>'form-label'])); ?>

            <div class="form-icon-user">
                <?php echo e(Form::text('reference', '', array('class' => 'form-control'))); ?>

            </div>
        </div>
        <div class="form-group  col-md-12">
            <?php echo e(Form::label('description', __('Description'),['class'=>'form-label'])); ?>

            <?php echo e(Form::textarea('description', '', array('class' => 'form-control','rows'=>3))); ?>

        </div>

            <div class="form-group col-md-12">
                <?php echo e(Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label'])); ?>

                <div class="choose-file form-group">
                    <label for="image" class="form-label">
                        <input type="file" name="add_receipt" id="files" class="form-control" accept="image/*, .txt, .rar, .zip" >
                    </label>
                    <p class="upload_file"></p>
                    <img id="image" class="mt-2" style="width:25%;"/>
                </div>
            </div>
    </div>

</div>
<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Add')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>


<script>
       document.querySelector(".pc-datepicker-1").flatpickr({
            mode: "range"
        });
</script>
<script>
    document.getElementById('files').onchange = function () {
    var src = URL.createObjectURL(this.files[0])
    document.getElementById('image').src = src
    }
</script><?php /**PATH /home/taim0or/Documents/accounting/resources/views/invoice/payment.blade.php ENDPATH**/ ?>