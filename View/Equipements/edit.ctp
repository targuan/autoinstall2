<div class="partition">
    <?php
        echo $this->Form->create('Equipement');
        echo $this->Form->input('hostname');
        echo $this->Form->input('mac');
        echo $this->Form->input('template');
        echo $this->Form->end('Save Post');
?>
</div>

