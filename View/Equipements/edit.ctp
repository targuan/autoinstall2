<div class="partition edit">
    <?php
        echo $this->Form->create('Equipement');
        echo $this->Form->hidden('id');
        echo $this->Form->input('hostname');
        echo $this->Form->input('mac');
        echo $this->Form->input('template');
        foreach($this->data['Variable'] as $key=>$variable):
            echo $this->Form->hidden("Variable.$key.id");
            echo $this->Form->hidden("Variable.$key.equipement_id");
            ?> <div> <?php
            echo $this->Form->input("Variable.$key.name",array('label'=>false,'div'=>false));
            echo $this->Form->input("Variable.$key.value",array('label'=>false,"type"=>"text",'div'=>false));
            ?> </div> <?php
        endforeach;
        echo $this->Form->end('Save Post');
        
        $this->append('script',<<<EDOC
                <script>
                    lastkey=$key
                    function addVariable() {
                        lastkey = lastkey + 1
                        $("#EquipementEditForm").append('<input type="hidden" name="data[Variable]['+lastkey+'][equipement_id]" value="{$this->data['Equipement']['id']}" id="Variable'+lastkey+'EquipementId">')
                        console.log(lastkey);
                        return false;
                    }
                </script>;
EDOC
        );
    ?>
    <button onclick="addVariable()">a</button>
    
</div>

