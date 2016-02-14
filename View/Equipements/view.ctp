<div class="partition">
    <table>
        <?php foreach ($equipement['Equipement'] as $key => $value): ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $value; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($equipement['Variable'] as $variable): ?>
            <tr>
                <td><?php echo $variable['name'] ?></td>
                <td><?php echo $variable['value']; ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
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