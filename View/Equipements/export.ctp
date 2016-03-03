<?php
if (empty($equipements)) {
    
} else {
    echo implode(',',$variables);
    echo "\n";
    foreach($values as $value) {
        echo implode(',',$value)."\n";
    }
}
