<div class="left-menu">
    <ul>
        <li class="title">Menu</li>
        <li> <?php echo $this->Html->link("Equipements",
                                        array('controller' => 'equipements', 
                                              'action' => 'index')); ?></li>
        <li> <?php echo $this->Html->link("Services",
                                        array('controller' => 'services', 
                                              'action' => 'index')); ?></li>
        <li> <?php echo $this->Html->link("Templates",
                                        array('controller' => 'templates', 
                                              'action' => 'index')); ?></li>
        <li>Parameters</li>
    </ul>
</div>