<?php 
$es = array();
foreach($equipements as $equipement) {
    $e=array();
    foreach($equipement['Equipement'] as $k=>$v) {
        $e[$k] = $v;
    }
    foreach($equipement['Variable'] as $v) {
        $e[$v['name']] = $v['value'];
    }
    $eq[] = $e;
}

echo json_encode($eq);
?>
