<div class="actions">
	<ul>
		<li><?php echo $html->link(__('New Subscriber', true), array('action' => 'add')); ?></li>
		<li><?php echo $html->link(__('Import', true), array('action' => 'import')); ?></li>
		<li><?php echo $html->link(__('Export', true), array('action' => 'export')); ?></li>
        <li>
            <?php echo $form->create('Subscriber',array('action'=>'index','class'=>'searchForm','type'=>'get'));
                echo $form->text('key', array('value'=>$searchTerm));
                echo $form->submit('Search');
                echo $form->end();
            ?>
        </li>
	</ul>
</div>


<div class="subscribers index">
<h2><?php __('Subscribers');?></h2>

<p>
<?  if($searchTerm != ''){ ?>
        Filtered results for <i><?= "'" . $searchTerm . "'" ?>.</i>
		<?php echo $html->link(__('Remove Filter', true), array('action' => 'index'));?>.<br/>
<? } ?>

<?php
echo $paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%.', true)
));
$paginator->options(array('url' => $this->passedArgs));
?></p>
<div class="paging">
	<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>



<table cellpadding="0" cellspacing="0">
<tr>
	<th class="actions"></th>
	<th>
        <?php echo $paginator->sort('firstname');?>/<?php echo $paginator->sort('lastname');?>
    </th>
	<th><?php echo $paginator->sort('company');?></th>
	<th><?php echo $paginator->sort('email');?></th>
	<th>    
        <?php echo $paginator->sort('address');?>/<?php echo $paginator->sort('postcode');?>/<?php echo $paginator->sort('country');?>
    </th>
	<th><?php echo $paginator->sort('source');?></th>
	<th><?php echo $paginator->sort('issue_start');?></th>
	<th><?php echo $paginator->sort('issue_end');?></th>
	<th>Last Issue Date</th>
	<th><?php echo $paginator->sort('order_id');?></th>
</tr>
<?php
$i = 0;
foreach ($subscribers as $subscriber):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="actions">
			<?php echo $html->link(__('View', true), array('action' => 'view', $subscriber['Subscriber']['id'])); ?><br/>
			<?php echo $html->link(__('Edit', true), array('action' => 'edit', $subscriber['Subscriber']['id'])); ?><br/>
			<?php echo $html->link(__('Delete', true), array('action' => 'delete', $subscriber['Subscriber']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $subscriber['Subscriber']['id'])); ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['firstname']; ?>
			<?php echo $subscriber['Subscriber']['lastname']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['company']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['email']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['address']; ?> <br/>
			<?php echo $subscriber['Subscriber']['city']; ?>, 
			<?php echo $subscriber['Subscriber']['state']; ?>, 
			<?php echo $subscriber['Subscriber']['postcode']; ?>, 
            <?php echo $subscriber['Subscriber']['country']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['source']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['issue_start']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['issue_end']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['last_issue_date']; ?>
		</td>
		<td>
			<?php echo $subscriber['Subscriber']['id']; ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
