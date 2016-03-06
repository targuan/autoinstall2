<div class="partition edit">
    <?php
        echo $this->Form->create('Service');
        $key=0;
        foreach($this->data['Service'] as $key=>$service):
            echo $this->Form->hidden("Service.$key.id");
            ?> <div> <?php
            echo $this->Form->input("Service.$key.name",array('label'=>false,'div'=>false));
            echo $this->Form->textarea("Service.$key.value",array('label'=>false,"type"=>"text",'div'=>false));
            ?> </div> <?php
        endforeach;
        echo $this->Form->end('Save',array('div'=>false));
    ?>
