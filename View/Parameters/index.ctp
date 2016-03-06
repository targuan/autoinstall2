<div class="partition">
	<?php echo $this->Html->link('Edit', array('action' => 'edit'),array('class'=>'button')); ?>
	<?php echo $this->Html->link('Add', array('action' => 'add'),array('class'=>'button')); ?>
	<table>
		<thead>
			<tr><th>Name</th><th>Value</th></tr>
		</thead>
		<tbody>
			<?php foreach($parameters as $parameter): ?>
			<tr><td><?php echo $parameter['Service']['name']; ?></td><td><?php echo nl2br($parameter['Service']['value']); ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

