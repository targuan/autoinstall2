<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP SwitchController
 * @author targuan
 */
class EquipementsController extends AppController {

    public $helpers = array('Html', 'Form', 'Flash');
    public $components = array('Flash');

    public function index() {
        $equipements = $this->Equipement->find('all');
        $this->set('equipements', $equipements);
    }

    public function view($id) {
        if (!$id) {
            throw new NotFoundException(__('Invalid switch'));
        }
        $equipement = $this->Equipement->findById($id);
        if (!$equipement) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        $this->set('equipement', $equipement);
    }

    public function add() {
        if ($this->request->is('post')) {
            $this->Equipement->create();
            if ($this->Equipement->save($this->request->data)) {
                $this->Flash->success(__('Your equipement has been saved.'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Flash->error(__('Unable to add your equipement.'));
        }
    }

    public function edit($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid equipement'));
        }

        $switch = $this->Equipement->findById($id);
        if (!$switch) {
            throw new NotFoundException(__('Invalid equipement'));
        }

        if ($this->request->is(array('post', 'put'))) {
            $this->Equipement->id = $id;
            if ($this->Equipement->save($this->request->data)) {
                $this->Flash->success(__('Your equipement has been updated.'));
                return $this->redirect(array('action' => 'view', $id));
            }
            $this->Flash->error(__('Unable to update your equipement.'));
        }

        if (!$this->request->data) {
            $this->request->data = $switch;
        }
    }

    public function delete($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        
        if ($this->Equipement->delete($id)) {
            $this->Flash->success(
                    __('The equipement with id: %s has been deleted.', h($id))
            );
        } else {
            $this->Flash->error(
                    __('The equipement with id: %s could not be deleted.', h($id))
            );
        }

        return $this->redirect(array('action' => 'index'));
    }
    
    public function import() {
        if ($this->request->is('post')) {
            debug($this->request->data['Equipement']);
            $separator = $this->request->data['Equipement']['separator'];
            $equipements = array();
            $file = fopen($this->request->data['Equipement']['File']['tmp_name'],'r');
            $keys = explode($separator,trim(fgets($file)));
            $n = 1;
            while($line = fgets($file)) {
                $n++;
                $equipement = array();
                $variables = array();
                $values = explode($separator,trim($line));
                $variables = array_combine($keys, $values);
                $mac = preg_replace('`[a-f0-9]`i','',$variables['mac']);
                if($this->request->data['Equipement']['basemac']) {
                    $delta = $this->request->data['Equipement']['delta'];
                    
                    $vmac = hexdec(substr($mac,6));
                    $vmac += $delta;
                    $mac = substr($mac,6) . dechex($vmac);
                    
                    debug($mac);
                    debug($vmac);
                    debug($variables['mac']);
                }
                $variables['mac'] = $mac;
                
                foreach(array('hostname','template','mac') as $key) {
                    if(!isset($variables[$key]) or empty($variables[$key])) {
                        throw new InternalErrorException("$key not found at line $n");
                    }
                    $equipement['Equipement'][$key] = $variables[$key];
                    unset($variables[$key]);
                }
                foreach($variables as $name=>$value) {
                    $equipement['Variable'][] = array('name'=>$name,
                            "value"=>$value);
                }
                
                $equipements[] = $equipement;
            }
            if($this->Equipement->saveMany($equipements,array('deep'=>true))) {
                
            } else {
                throw new InternalErrorException("Error importing");
            }
        } else {
            $this->request->data['Equipement']['separator'] = ',';
            $this->request->data['Equipement']['basemac'] = true;
            $this->request->data['Equipement']['delta'] = '71';
        }
    }
    
    public function purge() {
        
        if ($this->request->is('post')) {
            $this->Equipement->deleteAll(array(
                "Equipement.id"=>$this->request->data['ids']
            ));
        }
        
        return $this->redirect(array('action' => 'index'));
    }

}
