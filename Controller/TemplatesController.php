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
        $templates = glob(WWW_ROOT . "documents" . DS . "*.conf");
        $list = array();
        foreach ($templates as $template) {
            $pos = strrpos($template, "/");
            $list[] = substr($template, $pos + 1);
        }
        $this->set("templates", $list);
    }

    public function edit($name) {
        $bname = basename($name);
        $template_file = WWW_ROOT . "documents" . DS . $bname;
        if (!is_file($template_file)) {
            throw new NotFoundException("$template_file not found");
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->request->data['delete']) {
                unlink($template_file);
                return $this->redirect(array('action' => 'index'));
            } elseif (isset($this->request->data['content'])) {
                $content = $this->request->data['content'];
                $this->set("content", $content);
                file_put_contents($template_file, $content);
                return $this->redirect(array('action' => 'index'));
            }
        }
        $content = file_get_contents($template_file);
        $this->set('name', $bname);
        $this->set("content", $content);
    }

    public function add() {
        if ($this->request->is(array('post', 'put')) && isset($this->request->data['content'])) {
            $bname = basename($this->request->data['name']);
            $content = $this->request->data['content'];
            if (strlen($bname)<6) {
                $this->Flash->error("Template filename must end with .conf and can't be empty");
            }elseif(substr($bname,-5) != '.conf') {
                $this->Flash->error('Template filename must end with .conf');
            } else {
                $template_file = WWW_ROOT . "documents" . DS . $bname;

                $this->set("content", $content);
                if(file_put_contents($template_file, $content)) {
                    $this->Flash->success('Template created');
                    return $this->redirect(array('action' => 'index'));
                }
                $this->Flash->error('Template not created');
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

}
