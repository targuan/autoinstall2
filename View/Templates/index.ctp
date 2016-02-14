<div class="partition">
    <ul>
        <?php foreach ($templates as $template) : ?>
        <li>
            <?php echo $this->Html->link($template, array('action' => 'edit',$template)); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php echo $this->Html->link("Add", array('action' => 'add')); ?>
</div>
