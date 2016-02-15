<div class="partition">
    <?php
        echo $this->Form->create('Equipement', array('enctype' => 'multipart/form-data'));
        echo $this->Form->input("separator");
        echo $this->Form->file('File');
        echo $this->Form->end('Save Post');
?>
</div>
