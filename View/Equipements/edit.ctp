<div class="partition edit">
    <?php
        echo $this->Form->create('Equipement');
        echo $this->Form->hidden('id');
        echo $this->Form->input('hostname');
        echo $this->Form->input('mac');
        echo $this->Form->input('template');
        $key=0;
        foreach($this->data['Variable'] as $key=>$variable):
            echo $this->Form->hidden("Variable.$key.id");
            echo $this->Form->hidden("Variable.$key.equipement_id");
            ?> <div> <?php
            echo $this->Form->input("Variable.$key.name",array('label'=>false,'div'=>false));
            echo $this->Form->input("Variable.$key.value",array('label'=>false,"type"=>"text",'div'=>false));
            ?> </div> <?php
        endforeach;
        echo $this->Form->end('Save Equipement',array('div'=>false));
        
        $this->append('script',<<<EDOC
                <script>
                    lastkey=$key
                    function addVariable() {
                        lastkey = lastkey + 1
                        $("#EquipementEditForm .submit").before('<input type="hidden" name="data[Variable]['+lastkey+'][equipement_id]" value="{$this->data['Equipement']['id']}" id="Variable'+lastkey+'EquipementId">')
                        $("#EquipementEditForm .submit").before('<div> <input placeholder="Name" name="data[Variable]['+lastkey+'][name]" maxlength="255" type="text" value="" id="Variable'+lastkey+'Name"><input placeholder="value" name="data[Variable]['+lastkey+'][value]" maxlength="255" type="text" value="" id="Variable'+lastkey+'Value"> </div>')
                        console.log(lastkey);
                        return false;
                    }
                </script>
EDOC
        );
    ?>
    <button onclick="addVariable()">Add a variable</button>
    <?php echo $this->Html->link("Get variables from template",
                        array('controller' => 'equipements', 
                        'action' => 'getVariables',$this->data['Equipement']['id']),array("class"=>"button")); ?>
    <a href=""></a>
    
</div>

