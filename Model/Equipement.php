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
        'name' => array(
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
    
    public $actsAs = array('Containable');

}
