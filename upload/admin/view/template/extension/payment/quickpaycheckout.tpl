<?php

echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="email-config-form" data-toggle="tooltip" title="<?php echo $button_submit; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <?php
                //display validation error if they exist
                if(isset($validation_errors)){
                    ?>
                <div class='alert alert-danger'>
                    <ul>
                    <?php 
                    foreach($validation_errors as $error){
                        echo "<li> $error</li>";
                    }
                    ?>
                    </ul>
                </div>
                <?php
                }
                ?>
                <!--Config Form -->
                <form id="email-config-form" enctype="multipart/form-data" method="POST" action="<?= $submit ?>">
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $environment ?></label> <br />
                        <label class="radio-inline">
                            <input type="radio" <?=  (($quickpaycheckout_environment == 0) ? "checked='checked'" : "") ?> name="quickpaycheckout_environment" id="quickpaycheckout_environment" value="0"> <?= $environment_test ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" <?=  (($quickpaycheckout_environment == 1) ? "checked='checked'" : "") ?> name="quickpaycheckout_environment" id="quickpaycheckout_environment2" value="1"> <?= $environment_live ?>
                        </label>
                        <span id="helpBlock2" class="help-block"><?= $environment_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $merchant_name ?></label>
                        <input type="text" value="<?= $quickpaycheckout_merchant_name ?>" required="" class="form-control" minlength="1" maxlength="30" id="merchant_name" name="quickpaycheckout_merchant_name" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $merchant_name_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $merchant_desc ?></label>
                        <input type="text" value="<?= $quickpaycheckout_merchant_desc ?>" required="" class="form-control" minlength="1" maxlength="50" id="quickpaycheckout_merchant_desc" name="quickpaycheckout_merchant_desc" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $merchant_desc_description ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $payment_button ?></label>
                        <input type="text" value="<?= $quickpaycheckout_payment_button ?>" required="" class="form-control" minlength="1" maxlength="20" id="quickpaycheckout_payment_button" name="quickpaycheckout_payment_button" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $payment_button_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $payment_icon ?></label>
                        <input type="file" class="form-control" id="payment_icon" name="payment_icon" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $payment_icon_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $public_key ?></label>
                        <input type="text" value="<?= $quickpaycheckout_public_key ?>" required="" class="form-control" minlength="1" maxlength="200" id="merchant_public_key" name="quickpaycheckout_public_key" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $public_key_desc ?></span>
                    </div> 
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $private_key ?></label>
                        <input type="text" value="<?= $quickpaycheckout_private_key ?>" required="" class="form-control" minlength="1" maxlength="200" id="merchant_private_key" name="quickpaycheckout_private_key" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $private_key_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $sort_order ?></label>
                        <input type="text" value="<?= $quickpaycheckout_sort_order ?>" required="" class="form-control" min="0" id="quickpaycheckout_sort_order" name="quickpaycheckout_sort_order" aria-describedby="helpBlock2">
                        <span id="helpBlock2" class="help-block"><?= $sort_order_desc ?></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $status ?></label>
                        <select name="quickpaycheckout_status" class="form-control">
                            <option value="1" <?php echo (($quickpaycheckout_status == 1) ? "selected='selected'" : "") ?>><?= $enabled ?></option>
                            <option value="0" <?php echo (($quickpaycheckout_status == 0) ? "selected='selected'" : "") ?>><?= $disabled ?></option>
                        </select>                        
                        <span id="helpBlock2" class="help-block"><?= $status_desc ?></span>
                    </div>    
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $completed_status ?></label>
                        <select name="quickpaycheckout_success_status" id="quickpaycheckout_order_status" class="form-control">
                    <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $quickpaycheckout_success_status) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                        </select>                      
                        <span id="helpBlock2" class="help-block"><?= $completed_status_desc ?></span>
                    </div> 
                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $declined_status ?></label>
                        <select name="quickpaycheckout_declined_status" id="quickpaycheckout_order_status" class="form-control">
                    <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $quickpaycheckout_declined_status) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                        </select>                      
                        <span id="helpBlock2" class="help-block"><?= $declined_status_desc ?></span>
                    </div> 

                    <div class="form-group">
                        <label class="control-label" for="inputSuccess1"><?= $failed_status ?></label>
                        <select name="quickpaycheckout_failed_status" id="quickpaycheckout_order_status" class="form-control">
                    <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $quickpaycheckout_failed_status) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                        </select>                      
                        <span id="helpBlock2" class="help-block"><?= $failed_status_desc ?></span>
                    </div> 


                </form>
                <!--End of Config Form -->


            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>

