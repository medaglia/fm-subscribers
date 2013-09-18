

<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Subscribers', true), array('action' => 'index'));?></li>
		<li><?php echo $html->link(__('New Subscriber', true), array('action' => 'add')); ?></li>

		<li><?php echo $html->link(__('Export Subscribers', true), array('action' => 'export')); ?></li>
	</ul>
</div>
<div class="subscribers index">

<h2><?php __('Export Subscribers');?> 
</h2>


<? if(!empty($filepath)) {
    $url = '/' . CAKE . $filepath;
    echo "<p>Your export is complete. <a href='$url'>Download your CSV file here.</a></p>";

} else { 
?>

<?php echo $form->create('Subscriber', array('action'=>'export','method'=>'post')); ?>

<p style="vertical-align:base">
Export subscribers to recieve issue: &nbsp;

<?= $form->select('issue_end', $issues,null,null,false); ?>
</p>
<?= $form->submit('Go'); ?>

<?php echo $form->end(); ?>

<?
    }
?>
