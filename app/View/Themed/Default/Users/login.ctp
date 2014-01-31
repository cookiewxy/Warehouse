<div class="users form">
<?php echo $this->Form->create('User',array('class'=>'form-horizontal')); ?>
    <fieldset>
        <legend><?php echo __('Please enter your username and password'); ?></legend>
        <?php echo $this->Form->input('username');
        echo $this->Form->input('password');
        echo $this->Form->input('remember',array('type'=>'checkbox','label'=>__('Remember me')));
    ?>
    <div class="form-actions">
	<?php echo $this->Form->submit('<i class="icon-signin"></i> '.__('Login'),array( 'div'=>false,'class'=>'btn btn-info')); ?>
	&nbsp;&nbsp;<?php echo $this->Html->link(__('Forgot password? Click here'),array('controller'=>'users','action'=>'forgot')); ?>
	</div>
    </fieldset>
<?php echo $this->Form->end(); ?>
</div>
<script>
$(document).ready(function(){
	$('#UserUsername').focus();
});
</script>