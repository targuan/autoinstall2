<div class="partition">
    <?php
        echo $this->Form->create('Equipement', array('enctype' => 'multipart/form-data'));
        echo $this->Form->input("separator");
        echo $this->Form->input("basemac",array("type"=>"checkbox"));
        echo $this->Form->input("delta");
        echo $this->Form->file('File');
        echo $this->Form->end('Save Post');
?>
</div>
