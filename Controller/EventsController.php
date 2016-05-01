<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP ServicesController
 * @author targuan
 */
class EventsController extends AppController {
    public $helpers = array('Html', 'Form', 'Flash');
    public $components = array('Flash','RequestHandler');

    public function index() {
        $conditions = array();
        if(isset($this->request->query['search'])) {
                $this->request->data['Filter']['search'] = $this->request->query['search'];
                $split = preg_split('`[\s]+`',$this->request->query['search'],-1,PREG_SPLIT_NO_EMPTY);
                $conditions['OR'] = array();
                foreach($split as $search) {
                	$conditions['OR'][] = array('event like'=> $search);
                }
        }
        $events = $this->Event->find('all',array('conditions' => $conditions,"order"=>"date desc"));
        $this->set('events', $events);
    }
}
