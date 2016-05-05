<div class="partition">
    <h1><?php echo $equipement['Equipement']['name']; ?></h1>
    <button form="EquipementDropVariableForm" type="submit">Delete variable</button>
    <table class="editable">
        <thead>
            <tr>
                <th><input type="checkbox" class="checkall" /></th>
                <th>Key</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipement['Equipement'] as $key => $value): ?>
            <tr>
                <td></td>
                <td><?php echo $key; ?></td>
                <td><?php echo $value; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php echo $this->Form->create('Equipement',array('action'=>"dropVariable","method"=>"post")); ?>
        <?php echo $this->Form->hidden('id',array('value'=>$equipement['Equipement']['id'])); ?>
        <?php foreach ($equipement['Variable'] as $variable): ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo $variable['id'] ?>" /></td>
                <td><?php echo $variable['name'] ?></td>
                <td><?php echo $variable['value']; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php echo $this->Form->end(); ?>
        </tbody>
    </table>
</div>


<div class="partition">
    <?php
    echo $this->Html->link("Get configuration", array('controller' => 'equipements',
        'action' => 'get',
        $equipement['Equipement']['id']),
        array('class'=>'button'));
    ?>
    <?php
    echo $this->Html->link("Edit", array('controller' => 'equipements',
        'action' => 'edit',
        $equipement['Equipement']['id']),
        array('class'=>'button'));
    ?>
    <?php
    echo $this->Form->postLink(
            'Delete', array('action' => 'delete', $equipement['Equipement']['id']), array('confirm' => 'Are you sure?','class'=>'button')
    );
    ?>
    <?php
    echo $this->Html->link("View logs", array('controller' => 'equipements',
        'action' => 'logs',
        $equipement['Equipement']['id']),
        array('class'=>'button'));
    ?>
</div>
