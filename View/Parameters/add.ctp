<div  class="partition edit">
    <?php
        echo $this->Form->create('Service');
        echo $this->Form->input('name',array("placeholder"=>"Name"));
        echo $this->Form->input('value',array("placeholder"=>"Value"));
        echo $this->Form->end('Save parameter');
?>
</div>
