<div class="partition">
    <h1>DHCP server</h1>
    <?php if($result != 0) : ?>
        <?php echo implode('<br />',$status) ?>
    <?php else : ?>
        <?php echo implode('<br />',$status) ?>
    <?php endif; ?>
    <p>
        <?php echo $this->Html->link('Reload', array('action' => 'reloaddhcp')); ?>
        <?php echo $this->Html->link('Generate', array('action' => 'generatedhcp')); ?>
    </p>
</div>

<div class="partition">
    <h1>TFTP server</h1>
    <p>
        <?php echo $this->Html->link('Reload', array('action' => 'reloadtftp')); ?>
        <?php echo $this->Html->link('Generate', array('action' => 'generatetftp')); ?>
    </p>
</div>