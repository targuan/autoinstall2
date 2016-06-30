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
        <li> <?php echo $this->Html->link("Equipements Templates",
                                        array('controller' => 'equipementTemplates', 
                                              'action' => 'index')); ?></li>
        <li> <?php echo $this->Html->link("Parameters",
                                        array('controller' => 'parameters', 
                                              'action' => 'index')); ?></li>
        <li> <?php echo $this->Html->link("Events",
                                        array('controller' => 'events', 
                                              'action' => 'index')); ?></li>
    </ul>
</div>
