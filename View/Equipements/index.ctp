<div class="partition">
    <?php echo $this->Html->link("Add",
                        array('controller' => 'equipements', 
                        'action' => 'add'),array("class"=>"button")); ?>
    <?php echo $this->Html->link("Import",
                        array('controller' => 'equipements', 
                        'action' => 'import'),array("class"=>"button")); ?>
    <button form="EquipementPurgeForm" type="submit">Delete</button>
    <button form="EquipementPurgeForm" type="submit" formaction="<?php echo Router::url(array("action"=>"export")) ?>">Export</button>
    <button form="EquipementPurgeForm" type="submit" formaction="<?php echo Router::url(array("action"=>"getConfigurations")) ?>">Download</button>
    <button form="EquipementPurgeForm" type="submit" formaction="<?php echo Router::url(array("action"=>"resetStatus")) ?>">Reset status</button>
    <table class="editable">
        <thead>
            <tr>
                <th><input type="checkbox" class="checkall" /></th>
                <th>Hostname</th>
                <th>MAC</th>
                <th>Template</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->Form->create('Equipement',array('action'=>"purge","method"=>"post")); ?>
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
                    <td><?php echo $equipement['Equipement']['statusValue'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php echo $this->Form->end(); ?>
        </tbody>
    </table>
    

</div>

