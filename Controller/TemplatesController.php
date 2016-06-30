<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP TemplatesController
 * @author targuan
 */
class TemplatesController extends AppController {

    public function index() {
        if(isset ($this->request->params['equipements']) && $this->request->params['equipements']) {
            $subdir = DS . 'equipements';
            $this->set('controller','equipementTemplates');
        } else {
            $subdir = '';
            $this->set('controller','templates');
        }
        $templates = glob(WWW_ROOT . "documents" . $subdir . DS . "*.conf");
        $list = array();
        foreach ($templates as $template) {
            $pos = strrpos($template, "/");
            $list[] = substr($template, $pos + 1);
        }
        $this->set("templates", $list);
    }

    public function edit($name) {
        if(isset ($this->request->params['equipements']) && $this->request->params['equipements']) {
            $subdir = DS . 'equipements';
            $controller = 'equipementTemplates';
        } else {
            $subdir = '';
            $controller = 'templates';
        }
        $this->set('controller', $controller);
        $bname = basename($name);
        $template_file = WWW_ROOT . "documents" . $subdir . DS . $bname;
        if (!is_file($template_file)) {
            throw new NotFoundException("$template_file not found");
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->request->data['delete']) {
                unlink($template_file);
                return $this->redirect(array('controller'=>$controller, 'action' => 'index'));
            } elseif (isset($this->request->data['content'])) {
                $content = $this->request->data['content'];
                $this->set("content", $content);
                file_put_contents($template_file, $content);
                return $this->redirect(array('controller'=>$controller,'action' => 'index'));
            }
        }
        $content = file_get_contents($template_file);
        $this->set('name', $bname);
        $this->set("content", $content);
    }

    public function add() {
        if(isset ($this->request->params['equipements']) && $this->request->params['equipements']) {
            $subdir = DS . 'equipements';
            $controller = 'equipementTemplates';
        } else {
            $subdir = '';
            $controller = 'templates';
        }
        $this->set('controller', $controller);
        if ($this->request->is(array('post', 'put')) && isset($this->request->data['content'])) {
            $bname = basename($this->request->data['name']);
            $content = $this->request->data['content'];
            if (strlen($bname)<6) {
                $this->Flash->error("Template filename must end with .conf and can't be empty");
            }elseif(substr($bname,-5) != '.conf') {
                $this->Flash->error('Template filename must end with .conf');
            } else {
                $template_file = WWW_ROOT . "documents" . $subdir . DS . $bname;

                $this->set("content", $content);
                if(file_put_contents($template_file, $content)) {
                    $this->Flash->success('Template created');
                    return $this->redirect(array('controller'=>$controller,'action' => 'index'));
                } else {
                    $this->Flash->error('Template not created');
                }
            }
        }
        
        if(!isset($bname)) {
            $bname = "";
        } if(!isset($content)) {
            $content = "";
        }
        $this->set("content",$content);
        $this->set("name",$bname);
    }

    public function delete() {
        if(isset ($this->request->params['equipements']) && $this->request->params['equipements']) {
            $subdir = DS . 'equipements';
            $controller = 'equipementTemplates';
        } else {
            $subdir = '';
            $controller = 'templates';
        }
        $this->set('controller', $controller);

        if ($this->request->is('post')) {
            foreach($this->request->data['names'] as $name) {
                $bname = basename($name);
                $fname = WWW_ROOT . "documents" . $subdir . DS . $bname;
                if(file_exists($fname)) {
                    if(!unlink($fname)) {
                        $error = error_get_last();
                        $this->Flash->error('Template ' . $fname . ' not deleted: '.$error['name']);
                    }
                    else {
                        $this->Flash->success('Template ' . $fname . ' deleted');
                    }
                }
            }
        }
        return $this->redirect(array('controller'=>$controller, 'action' => 'index'));
    }


}
