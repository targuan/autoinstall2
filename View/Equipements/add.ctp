<div  class="partition edit">
    <?php
        echo $this->Form->create('Equipement');
        echo $this->Form->input('name',array("placeholder"=>"name"));
        echo $this->Form->input('mac',array("placeholder"=>"MAC address"));
        echo $this->Form->input('template',array("placeholder"=>"template name"));
        echo $this->Form->end('Save Equipement');
?>
</div>
