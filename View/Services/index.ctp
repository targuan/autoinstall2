<div class="partition">
    <h1>DHCP server</h1>
    <?php if($result != 0) : ?>
        <?php echo implode('<br />',$status) ?>
    <?php else : ?>
        <?php echo implode('<br />',$status) ?>
    <?php endif; ?>
    <p>
        <?php echo $this->Html->link('Reload', array('action' => 'reloaddhcp'),array("class"=>"button")); ?>
        <?php echo $this->Html->link('Generate', array('action' => 'generatedhcp'),array("class"=>"button")); ?>
    </p>
</div>

