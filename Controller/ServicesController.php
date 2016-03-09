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
class ServicesController extends AppController {

    public function index() {
       
        $status = exec("/usr/sbin/service isc-dhcp-server status",$output,$code);
        $this->set("status",$output);
        $this->set("result",$code);
    }
    
    private function exec($cmd) {
        if(!empty($cmd)) {
            exec($cmd['Service']['value'],$output,$code);
            if($code == 0) {
                $this->Flash->success('Service restarted');
            } else {
                $this->Flash->error('Service failed: '.implode($output));
            }
            return $output;
        } else {
            $this->Flash->error("Can't find parameters.");
        }
    }
    
    public function reloaddhcp() {
        $cmd = $this->Service->findByName('dhcpdreload');
        $this->exec($cmd);
        return $this->redirect(array('action' => 'index'));
        
    }
    
    public function reloadtftp() {
        $cmd = $this->Service->findByName('tftpdreload');
        $this->exec($cmd);
        return $this->redirect(array('action' => 'index'));
        
    }
    
    public function generatedhcp() {
        $service = $this->Service->findByName('dhcpdtemplate');
        if(empty($service)) {
            $this->Flash->error("I couldn't find the dhcp template");
            return $this->redirect(array('action' => 'index'));
        }
        $template = $service['Service']['value'];
        
        if(strpos($template,'##autoinstall') === false) {
            $this->Flash->error("I couldn't find ##autoinstall part in the template");
            return $this->redirect(array('action' => 'index'));
        }
        
        $services = $this->Service->findByName('tftpaddress');
        if(count($services) == 0) {
            $this->Flash->error("I didn't found the tftp address");
            return $this->redirect(array('action' => 'index'));
        }
        $tftp = $services['Service']['value'];
        
        $this->loadModel('Equipement');
        $equipements = $this->Equipement->find('all');
        $content = "";
        foreach($equipements as $equipement) {
            $ip = $equipement['Equipement']['ip'];
            $hostname = $equipement['Equipement']['hostname'];
            $mac = $equipement['Equipement']['mac'];
            $content .= "subclass \"switchs\" 1:$mac;\n";
        }
        $template = str_replace("##autoinstall", $content, $template);

        $service = $this->Service->findByName('dhcpdconffile');
        if(empty($service)) {
            $this->Flash->error("I couldn't find the dhcpd conf file path");
            return $this->redirect(array('action' => 'index'));
        }
        file_put_contents($service['Service']['value'], $template);
        return $this->redirect(array('action' => 'reloaddhcp'));
    }
    
    public function generatetftp() {
        $service = $this->Service->findByName('tftpdroot');
        
        if(empty($service)) {
            $this->Flash->error("I couldn't find the tftpd root path");
            return $this->redirect(array('action' => 'index'));
        }
        $tftpdroot = $service['Service']['value'];
         
        $boottemplateservice = $this->Service->findByName('boottemplate');
	if(empty($boottemplateservice)) {
            $this->Flash->error("I couldn't find the boottemplate");
            return $this->redirect(array('action' => 'index'));
        }
        $boottemplate = $boottemplateservice['Service']['value'];

        $this->loadModel('Equipement');
        $equipements = $this->Equipement->find('all');
        
        $network = "";
        
        foreach($equipements as $equipement) {
            $ip = $equipement['Equipement']['ip'];
            $network .= "ip host {$equipement['Equipement']['hostname']} {$ip}\n";
            
            $template_name = $equipement['Equipement']['template'];
            if(strpos($template_name,"slave")===0) {
                file_put_contents("$tftpdroot/{$equipement['Equipement']['hostname']}-confg",$boottemplate);
                continue;
            }
            
            $template = file_get_contents(WWW_ROOT . DS . "documents" . DS . $template_name);
            foreach($equipement['Equipement'] as $key=>$value) {
                $template = str_replace("<$key>",$value,$template);
            }
            foreach($equipement['Variable'] as $variable) {
                $template = str_replace("<{$variable['name']}>",$variable['value'],$template);
            }
            file_put_contents("$tftpdroot/fullconfig/{$equipement['Equipement']['hostname']}-confg",$template);
            
        }
        
        file_put_contents("$tftpdroot/network-confg", $network);
        
        $this->Flash->success('Files generated');
        return $this->redirect(array('action' => 'index'));
    }
    
    public function edit() {
        if ($this->request->is(array('post', 'put'))) {
            $this->Service->saveMany($this->request->data['Service']);
        }
        else {
            $services = $this->Service->find('all');
            foreach($services as $service) {
	        $this->request->data['Service'][] = $service['Service'];
            }
        }
    }
}
