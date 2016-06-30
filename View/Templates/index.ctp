<div class="partition">
    <?php echo $this->Html->link("Add", array('controller'=>$controller,'action' => 'add'),array("class"=>"button")); ?>
    <button form="TemplatesDeleteForm" type="submit">Delete</button>
    <table class="editable">
    <thead>
        <tr>
            <th><input type="checkbox" class="checkall" /></th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <?php echo $this->Form->create('Templates',array('url'=>array('controller'=>$controller,'action'=>"delete"),'action'=>'delete',"method"=>"post")); ?>
        <?php foreach ($templates as $template) : ?>
        <tr>
            <td><input type="checkbox" name="names[]" value="<?php echo $template ?>" /></td><td><?php echo $this->Html->link($template, array('controller'=>$controller,'action' => 'edit',$template)); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php echo $this->Form->end(); ?>
    </tbody>
    </table>
</div>

