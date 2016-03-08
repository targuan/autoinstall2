<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppModel', 'Model');

/**
 * CakePHP SwitchModel
 * @author targuan
 */
class Equipement extends AppModel {
    public $validate = array(
        'hostname' => array(
            'rule' => 'notBlank'
        ),
        'mac' => array(
            'rule' => 'notBlank'
        ),
        'template' => array(
            'rule' => 'notBlank'
        )
    );
    
    public $hasMany = 'Variable';

    public function afterFind($results,$primary=false) {
        $values = ['init','ping','ssh','version','binary','download','md5','copy','error','finished'];
        foreach($results as $key=>$result) {
            if(isset($results[$key]['Equipement']['status'])) { $results[$key]['Equipement']['statusValue'] = $values[$results[$key]['Equipement']['status']]; }
        }
        return $results;
    }
}
