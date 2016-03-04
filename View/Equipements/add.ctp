<div  class="partition edit">
    <?php
        echo $this->Form->create('Equipement');
        echo $this->Form->input('hostname',array("placeholder"=>"hostname"));
        echo $this->Form->input('mac',array("placeholder"=>"MAC address"));
        echo $this->Form->input('ip',array("placeholder"=>"IP address"));
        echo $this->Form->input('template',array("placeholder"=>"template name"));
        echo $this->Form->end('Save Equipement');
?>
</div>
