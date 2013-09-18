<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Delete', true), array('action' => 'delete', $form->value('Subscriber.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('Subscriber.id'))); ?></li>
		<li><?php echo $html->link(__('List Subscribers', true), array('action' => 'index'));?></li>
	</ul>
</div>


<div class="subscribers form">
<?php echo $form->create('Subscriber');?>
	<fieldset>
 		<legend><?php __('Edit Subscriber');?></legend>
	<?php
		echo $form->input('firstname');
		echo $form->input('lastname');
		echo $form->input('company');
		echo $form->input('email');
		echo $form->input('phone');
		echo $form->input('address');
		echo $form->input('city');
		echo $form->input('state');
		echo $form->input('country');
		echo $form->input('postcode');
		echo $form->input('source');
		echo $form->input('issue_start');
		echo $form->input('issue_end');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>

