<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Subscribers', true), array('action' => 'index'));?></li>
		<li><?php echo $html->link(__('New Subscriber', true), array('action' => 'add')); ?></li>
		<li><?php echo $html->link(__('Export', true), array('action' => 'export')); ?></li>
	</ul>
</div>
<div class="subscribers index">

<h2><?php __('Import Subscribers');?> 
    <? if($mode=='cart'){echo "- Cart Mode";} ?> 
</h2>


<? if(empty($lines) && empty($badlines)){ ?>

        <? if(!empty($usr_msg)){ ?>
            <p class="error"><?= $usr_msg ?></p>
        <? } ?>

		<div class="subscribers form">
		<?php echo $form->create('Subscriber', array('type'=>'file', 'action'=>'import') ); ?>
		<input type="hidden" name="mode" value="<?= $mode ?>"/>

		<div>
		Fields can be enclosed by double-quotes("), and escaped by 
		using backslash (\). Maxiumum of <?= $maxRows ?> rows allowed at a time.
        
        <? if($mode=='cart'){ ?> 
            <b>You are using 'Cart Mode'</b>. To import data normally, 
            use 
            <? echo $html->link(__('Normal Mode', true), array('action' => 'import')); ?>
        <? }else{ ?> 
            To import data from a shopping cart, use 
            <? echo $html->link(__('Cart Mode', true), array('action' => 'import','cart')); ?>
        <? } ?>
		</div>
        

		<div class="input">
		<label>Choose a CSV file to upload</label>
		<?php echo $form->file('TheFile', array('label'=>'Choose your file')); ?>
		</div>

		<?php echo $form->input('Delim', array('label'=>'Field Delimiter','options'=>$delims)); ?>

		<div>
		Please make sure that your csv file is formatted as shown below.
		</div>
		<table class="data">
			<tr>
				<? foreach ($fields as $f){ ?>
					<td><?= $f ?></td>
				<? } ?>
			</tr>
		</table>

		<?php echo $form->end('Continue');?>
		</div>

<? } else { ?>

	<? if(!empty($badlines)){ ?>
		<p class="error">
		<b>Error:</b><br/>
		There were some bad rows with your import. Mouse-over the rows in red 
		below to see the errors. If you continue with your import, bad rows will
		not be imported.
		</p>
	<? } ?>


	<? if(!empty($usr_msg)){ ?>
		<p class="error"><?= $usr_msg ?></p>
	<? } ?>


	<div class="subscribers form">
		Finish the import.
        Bad rows: <?= sizeof($badlines) ?>,
        Good rows: <?= sizeof($lines) ?>
		<?php echo $form->create('Subscriber', array('action'=>'import') ); ?>
		<?php echo $form->hidden('ingest');?>
		<?php echo $form->end('Import');?>
	</div>


    <table class="data">
	<tr>
		<? foreach ($fields as $f){ ?>
            <th><?= $f ?></th>
		<? } ?>
	</tr>


<? 
	$i = 0;
	foreach($badlines as $l){
		$altrow = ($i % 2 == 0)?'altrow':'';
		$error = array_pop($l);
		echo("<tr title=\"$error\" class=\"error $altrow\">\n");
			foreach($l as $c){
		   		echo("<td>$c</td>\n");
			}
		echo("</tr>\n");
		$i++;
	}

	foreach($lines as $l){
		$altrow = ($i % 2 == 0)?'altrow':'';
		echo("<tr class=\"$altrow\">\n");
			foreach($l as $c){
		   		echo("<td>$c</td>\n");
			}
		echo("</tr>\n");
		$i++;
	}
?>

<?php } ?>

</table>
