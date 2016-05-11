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
    public $components = array('Flash','RequestHandler');

    private static function getConfigurationFromTemplate($equipement) {
        $template = "";

        $bname = basename($equipement['Equipement']['template']);
        $template_file = WWW_ROOT . "documents" . DS . $bname;
        if (!is_file($template_file)) {
            throw new NotFoundException("$template_file not found");
        }
        $template = file_get_contents($template_file);
        foreach ($equipement['Equipement'] as $name => $value) {
            $template = str_replace("<$name>", $value, $template);
        }
        foreach ($equipement['Variable'] as $variable) {
            $template = str_replace("<{$variable['name']}>", $variable['value'], $template);
        }

        return $template;
    }

    public function index() {
        $equipements = $this->Equipement->find('all');
        $this->set('equipements', $equipements);
        #$this->set('_serialize', array('equipements'));
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
        $this->set('_serialize', array('equipement'));
    }

    public function add() {
        if ($this->request->is('post')) {
            $this->Equipement->create();
            $this->request->data['Equipement']['mac'] = strtolower($this->request->data['Equipement']['mac']);
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
            if ($this->Equipement->saveAssociated($this->request->data)) {
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

            $separator = $this->request->data['Equipement']['separator'];
            $equipements = array();
            $file = fopen($this->request->data['Equipement']['File']['tmp_name'], 'r');
            $keys = explode($separator, trim(fgets($file)));
            $n = 1;
            while ($line = fgets($file)) {
                $n++;
                $equipement = array();
                $variables = array();
                $values = explode($separator, trim($line));
                $variables = array_combine($keys, $values);
                $mac = strtolower(preg_replace('`[^a-f0-9]`i', '', $variables['mac']));
                if ($this->request->data['Equipement']['basemac']) {
                    $delta = $this->request->data['Equipement']['delta'];

                    $vmac = hexdec(substr($mac, 6));
                    $vmac += $delta;
                    $mac = substr($mac, 0, 6) . str_pad(dechex($vmac), 6, "0", STR_PAD_LEFT);
                }
                $variables['mac'] = substr(chunk_split($mac, 2, ':'), 0, 17);

                foreach (array('name', 'template', 'mac','status') as $key) {
                    if (!isset($variables[$key]) or empty($variables[$key])) {
                        if($key == 'status') continue;
                        throw new InternalErrorException("$key not found at line $n");
                    }
                    $equipement['Equipement'][$key] = $variables[$key];
                    unset($variables[$key]);
                }
                foreach ($variables as $name => $value) {
                    $equipement['Variable'][] = array('name' => $name,
                        "value" => $value);
                }

                $equipements[] = $equipement;
            }
            if ($this->Equipement->saveMany($equipements, array('deep' => true))) {
                $this->Flash->success(
                        __('%s have been been imported.', count($equipements))
                );
                return $this->redirect(array('action' => 'index'));
            } else {
                throw new InternalErrorException("Error importing");
            }
        }
    }

    public function purge() {
        if ($this->request->is('post')) {
            $this->Equipement->deleteAll(array(
                "Equipement.id" => $this->request->data['ids']
            ));
        }

        return $this->redirect(array('action' => 'index'));
    }
    
    public function resetStatus() {
        if ($this->request->is('post')) {
            $this->Equipement->updateAll(
		array('Equipement.status'=>"'init'"),
		array('Equipement.id'=>$this->request->data['ids'])
            );
        }

        return $this->redirect(array('action' => 'index'));
    }

    public function dropVariable() {
        $this->loadModel('Variable');
        if ($this->request->is('post')) {
            if ($this->Variable->deleteAll(array(
                        "Variable.id" => $this->request->data['ids'],
                        "Variable.equipement_id" => $this->request->data['Equipement']['id']
                    ))) {
                $this->Flash->success(
                        __('Variables have been deleted.')
                );
                return $this->redirect(array('action' => 'view', $this->request->data['Equipement']['id']));
            } else {
                $this->Flash->error(
                        __('An error has occured while deleting variables.')
                );
            }
        }
    }

    public function get($id) {
        if (!$id) {
            throw new NotFoundException(__('Invalid switch'));
        }
        $equipement = $this->Equipement->findById($id);
        if (!$equipement) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        $template = $this->getConfigurationFromTemplate($equipement);
        $this->layout = false;
        $this->set('template', $template);

        $this->response->type('text/plain');
    }

   public function getByName($name) {
        if(!$name) { 
            throw new NotFoundException(__('Invalid switch'));
        }
        $equipement = $this->Equipement->findByName($name);
        if (!$equipement) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        $template = $this->getConfigurationFromTemplate($equipement);
        $this->layout = false;
        $this->set('template', $template);
        $this->render('get');

        $this->response->type('text/plain');
   }

    public function getVariables($id) {
        if (!$id) {
            throw new NotFoundException(__('Invalid switch'));
        }
        $equipement = $this->Equipement->findById($id);
        if (!$equipement) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        $bname = basename($equipement['Equipement']['template']);
        $template_file = WWW_ROOT . "documents" . DS . $bname;
        if (!is_file($template_file)) {
            throw new NotFoundException("$template_file not found");
        }
        $template = file_get_contents($template_file);

        preg_match_all('`<([^>]+)>`', $template, $res);
        $variables = array_unique($res[1]);

        foreach ($equipement['Equipement'] as $name => $value) {
            if (($key = array_search($name, $variables)) !== false) {
                unset($variables[$key]);
            }
        }
        foreach ($equipement['Variable'] as $variable) {
            if (($key = array_search($variable['name'], $variables)) !== false) {
                unset($variables[$key]);
            }
        }
        foreach ($variables as $name) {
            $equipement['Variable'][] = array(
                "equipement_id" => $equipement['Equipement']['id'],
                'name' => $name, "value" => "");
        }

        if ($this->Equipement->saveAssociated($equipement)) {
            $this->Flash->success(__('Your equipement has been updated.'));
        } else {
            $this->Flash->error(__('Unable to update your equipement.'));
        }
        return $this->redirect(array('action' => 'edit', $id));
    }

    public function export() {
        if ($this->request->is('post') && isset($this->request->data['ids'])) {
            $equipements = $this->Equipement->find('all', array(
                "conditions" => array("Equipement.id" => $this->request->data['ids'])
            ));
            $variables = array();

            foreach ($equipements as $id => $equipement) {
                foreach ($equipement['Equipement'] as $key => $value) {
                    if(!$this->Equipement->hasField($key)) continue;
                    $equipements[$id]['Values'][$key] = $value;
                    $variables[] = $key;
                }
                foreach ($equipement['Variable'] as $variable) {
                    $equipements[$id]['Values'][$variable['name']] = $variable['value'];
                    $variables[] = $variable['name'];
                }
            }
            $variables = array_unique($variables);
            $values = array();
            foreach ($equipements as $equipement) {
                foreach ($variables as $variable) {
                    if (!isset($equipement['Values'][$variable])) {
                        $values[$equipement['Equipement']['id']][] = "";
                    } else {
                        $values[$equipement['Equipement']['id']][] = $equipement['Values'][$variable];
                    }
                }
            }
            $this->set('variables', $variables);
            $this->set('values', $values);
            $this->set("equipements", $equipements);
            $this->layout = false;
            $this->response->type('text/plain');
        } else {
            return $this->redirect(array('action' => 'index'));
        }
    }

    public function getConfigurations() {
        //debug($this->request->data);
        if ($this->request->is('post') && isset($this->request->data['ids'])) {
            $zip = new ZipArchive;
            $filename = TMP . "cache/" . uniqid("configurations") . ".zip";
            $res = $zip->open($filename, ZipArchive::CREATE);

            $equipements = $this->Equipement->find('all', array(
                "conditions" => array("Equipement.id" => $this->request->data['ids'])
            ));
            foreach ($equipements as $equipement) {
                $zip->addFromString("{$equipement['Equipement']['name']}-confg", self::getConfigurationFromTemplate($equipement));
            }
            
            if ($zip->close()) {
                $this->response->download('configurations.zip');
                $this->response->file($filename);
                return $this->response;
            } else {
                return $this->redirect(array('action' => 'index'));
            }
        } else {
            $this->Flash->error(__('Select the equipements to export.'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    
    public function updateStatus($id,$status) {
        $equipement = $this->Equipement->findById($id);
        $this->loadModel('Event');
        $event = array('source' => 'API',
                       'severity' => 20,
                       'date' => $this->Event->getDataSource()->expression('NOW()'),
                       'event' => $equipement['Equipement']['name'] . ': status changed to ' .$status); 
        $this->Event->save($event);
        $this->Equipement->id = (int)$id;
        $this->Equipement->saveField('status',$status);
        return $this->redirect(array('action' => 'view',$id));
    }

    public function check() {
        if ($this->request->is('post') && isset($this->request->data['ids'])) {
            $equipements = $this->Equipement->find('all', array(
                "conditions" => array("Equipement.id" => $this->request->data['ids'])
            ));
            $result = array('OK'=>array(),'warning'=>array());
            foreach ($equipements as $equipement) {
                if(preg_match('`slave\d+`',$equipement['Equipement']['template'])) {
                    $result['OK'][] = $equipement['Equipement']['name'];
                    continue;
                }
                try {
                    $empty = array();
                    foreach($equipement['Variable'] as $variable) {
                        if($variable['value'] == '') {
                            $empty[] = $variable['name']; 
                        }
                    }
                    if(!empty($empty)) {
                        $result['warning'][] = array($equipement['Equipement']['name'],'Variable empty',$empty);
                    }
                    $missing = array();
                    foreach(array('version','binary','binarymd5') as $variableName) {
                        foreach($equipement['Variable'] as $variable) {
                            if($variable['name'] == $variableName) continue 2;
                        }
                        $missing[] = $variableName;
                    }
                    if(!empty($missing)) {
                        $result['warning'][] = array($equipement['Equipement']['name'],'Static variable not found',$missing);
                    }
                    $configuration = self::getConfigurationFromTemplate($equipement);
                    if(preg_match_all('`<([^>]+)>`',$configuration,$res)) {
                        $result['warning'][] = array($equipement['Equipement']['name'],'Template variable not found',array_unique($res[1]));
                        $missing = array_merge($missing,array_unique($res[1]));
                    } 
                    if(empty($missing) && empty($empty)) {
                        $result['OK'][] = $equipement['Equipement']['name'];
                    }
                } catch(Exception $e) {
                    $result['warning'][] = array($equipement['Equipement']['name'],'template not found',array());
                }

            }
            $this->set('result',$result);
        } else {
            return $this->redirect(array('action' => 'index'));
        }
    }
    
    public function logs($id) {
        if (!$id) {
            throw new NotFoundException(__('Invalid switch'));
        }
        
        $equipement = $this->Equipement->find('first',
                                              array('conditions'=>array('id'=>$id),
                                                    'fields'=>array('Equipement.mac'),
                                                    'contain' => false)
                                             );
        if (!$equipement) {
            throw new NotFoundException(__('Invalid equipement'));
        }
        $mac = $equipement['Equipement']['mac'];
        $file = "/var/lib/autoinstall/archive-$mac";
        if(file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = '';
        }
        $this->set('content',$content);
    }

}
