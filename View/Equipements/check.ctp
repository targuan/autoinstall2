<div class="partition">
<table>
<?php foreach($result['warning'] as $w): ?>
	<tr><td><?php echo $w[0] ?></td><td class="warning"><?php echo $w[1]; ?> <?php echo implode(', ',$w[2]) ?></td></tr>
<?php endforeach; ?>
<?php foreach($result['OK'] as $w): ?>
	<tr><td><?php echo $w ?></td><td class="ok">OK</td></tr>
<?php endforeach; ?>
</table>
</div>

