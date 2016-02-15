<div class="partition">
    <?php
        echo $this->Form->create('Equipement', array('enctype' => 'multipart/form-data'));
        echo $this->Form->input("separator",array('default'=>";"));
        echo $this->Form->input("basemac",array("type"=>"checkbox","default"=>false));
        echo $this->Form->input("delta",array('default'=>71));
        echo $this->Form->file('File',array('accept' => '.csv'));
        echo $this->Form->end('Save Post');
?>
</div>
