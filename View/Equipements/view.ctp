<div class="partition">
    <button form="EquipementDropVariableForm" type="submit">Delete variable</button>
    <table>
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
    <h1><?php echo $equipement['Equipement']['hostname']; ?></h1>
</div>


<div class="partition">
    <?php
    echo $this->Html->link("Edit", array('controller' => 'equipements',
        'action' => 'edit',
        $equipement['Equipement']['id']));
    ?>
    <?php
    echo $this->Form->postLink(
            'Delete', array('action' => 'delete', $equipement['Equipement']['id']), array('confirm' => 'Are you sure?')
    );
    ?>
</div>