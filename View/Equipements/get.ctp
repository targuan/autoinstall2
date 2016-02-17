<?php

foreach($equipement['Equipement'] as $name=>$value) {
    $template = str_replace("<$name>",$value,$template);
}
foreach($equipement['Variable'] as $variable) {
    $template = str_replace("<{$variable['name']}>",$variable['value'],$template);
}

echo $template;
