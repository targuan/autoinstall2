<?php

App::uses('AppController', 'Controller');

/**
 * CakePHP ServicesController
 * @author targuan
 */
class ParametersController extends AppController {

    public function index() {
        $this->loadModel('Service');
        $parameters = $this->Service->find('all');
        $this->set('parameters',$parameters);
    }

    public function add() {
        $this->loadModel('Service');
        
        if ($this->request->is('post')) {
            $this->Service->create();
            if ($this->Service->save($this->request->data)) {
                $this->Flash->success(__('Your parameter has been saved.'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Flash->error(__('Unable to add your parameter.'));
        }
    }
    
    public function edit() {
        $this->loadModel('Service');
        if ($this->request->is(array('post', 'put'))) {
            if($this->Service->saveMany($this->request->data['Service'])) {
                $this->Flash->success(__('Your parameters have been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('Unable to save your parameters.'));
            }
        }
        else {
            $services = $this->Service->find('all');
            foreach($services as $service) {
                $this->request->data['Service'][] = $service['Service'];
            }
        }
    }
}
