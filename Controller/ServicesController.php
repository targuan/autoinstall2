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
    
    public function reloaddhcp() {
        exec("sudo /usr/sbin/service isc-dhcp-server restart 2>&1",$output,$code);
        if($code == 0) {
            $this->Flash->success('Service restarted');
        } else {
            $this->Flash->error('Service failed: '.implode($output));
        }
        return $this->redirect(array('action' => 'index'));
        
    }
    
    public function reloadtftp() {
        exec("sudo /usr/sbin/service atftpd restart 2>&1",$output,$code);
        if($code == 0) {
            $this->Flash->success('Service restarted');
        } else {
            $this->Flash->error('Service failed: '.implode($output));
        }
        return $this->redirect(array('action' => 'index'));
        
    }
    
    public function generatedhcp() {
        $template = file_get_contents("/etc/dhcp/dhcpd.conf.template");
        $this->loadModel('Equipement');
        $equipements = $this->Equipement->find('all');
        $services = $this->Service->findByName('tftpaddress');
        if(count($services) == 0) {
            $this->Flash->error("I didn't found the tftp address");
            return $this->redirect(array('action' => 'index'));
        }
        $tftp = $services['Service']['value'];
        
        $content = "";
        foreach($equipements as $equipement) {
            $ip = null;
            foreach($equipement['Variable'] as $variable) {
                if($variable['name'] == "ip") {
                    $ip = $variable['value'];
                }
            }
            if($ip == null) {
                continue;
            }
            $hostname = $equipement['Equipement']['hostname'];
            $mac = $equipement['Equipement']['mac'];
            $content .= "host $hostname { hardware ethernet $mac; fixed-address $ip; option option-150 $tftp;}\n";
        }
        $template = str_replace("##autoinstall", $content, $template);
        file_put_contents("/etc/dhcp/dhcpd.conf", $template);
        return $this->redirect(array('action' => 'reloaddhcp'));
    }
    
    public function generatetftp() {
        $this->loadModel('Equipement');
        $equipements = $this->Equipement->find('all');
        
        $network = "";
        
        foreach($equipements as $equipement) {
            $template_name = $equipement['Equipement']['template'];
            $template = file_get_contents(WWW_ROOT . DS . "documents" . DS . $template_name);
            $ip = "";
            foreach($equipement['Equipement'] as $key=>$value) {
                $template = str_replace("<$key>",$value,$template);
            }
            foreach($equipement['Variable'] as $variable) {
                $template = str_replace("<{$variable['name']}>",$variable['value'],$template);
                if($variable['name'] == "ip") {
                    $ip = $variable['value'];
                }
            }
            file_put_contents("/srv/tftp/{$equipement['Equipement']['hostname']}-confg",$template);
            
            $network .= "ip host {$equipement['Equipement']['hostname']} {$ip}\n";
        }
        
        file_put_contents("/srv/tftp/network-confg", $network);
        
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
