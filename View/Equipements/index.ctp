<div class="partition">
    <?php echo $this->Html->link("Add",
                        array('controller' => 'equipements', 
                        'action' => 'add')); ?>
    <?php echo $this->Html->link("Import",
                        array('controller' => 'equipements', 
                        'action' => 'import')); ?>
    <a href="#">Delete</a>
    <?php echo $this->Form->create('Equipement',array('action'=>"purge","method"=>"post")); ?>
    <?php echo $this->Form->submit("Delete"); ?>
    <table>
        <thead>
            <tr>
                <th><input type="checkbox" class="checkall" /></th>
                <th>Hostname</th>
                <th>MAC</th>
                <th>Template</th>
            </tr>
        </thead>
        <tbody>
            
            <?php foreach ($equipements as $equipement) : ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo $equipement['Equipement']['id'] ?>" /></td>
                    <td>
                        <?php echo $this->Html->link($equipement['Equipement']['hostname'],
                                        array('controller' => 'equipements', 
                                              'action' => 'view', 
                                              $equipement['Equipement']['id'])); ?>
                    </td>
                    <td><?php echo $equipement['Equipement']['mac'] ?></td>
                    <td><?php echo $equipement['Equipement']['template'] ?></td>
                </tr>
            <?php endforeach; ?>
            
        </tbody>
    </table>
    <?php echo $this->Form->end(); ?>

</div>

