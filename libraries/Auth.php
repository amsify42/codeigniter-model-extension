<?php 

class Auth {

	 private $table 	= 'users';
	 private $redirect	= 'admin/login';

	 function __construct($params = array()) {
	 	if(isset($params['table'])) {
	 		$this->table = $params['table'];
	 	}
	 	if(isset($params['redirect'])) {
	 		$this->redirect = $params['redirect'];
	 	}
	 	$this->CI = & get_instance();
	 	$this->CI->load->helper('basic');
	 }


	 public function filter($type, $redirect = '') {

	 	 $redirect = true;

	 	 if(sizeof($this->CI->session->userdata()) > 1) {
	 	 	if($type == $this->CI->session->userdata('type')) {
	 	 		$redirect = false;
	 	 	}
	 	 }
	 	 
	 	 if($redirect) { 
	 	 	$this->CI->session->set_flashdata('error', 'Please login to view '.$type.' section');
	 	 	redirect($this->redirect); 
	 	 }
	 }


	 public function redirect($redirect) {
	 	if(sizeof($this->CI->session->userdata()) > 1) {
	 	 	redirect($redirect);
	 	 }
	 }

	 public function attempt($credentials = array(), $table = '') {

 		if(sizeof($credentials) > 0) {
 			// If table name is passed from parameter
 			if($table != '') {
	 			$this->table = $table;
	 		}

	 		// If credentials are authenticated
	 		if($this->checkTable($credentials)) {
	 			return true;
	 		}
 		}
 		return false;
	 }


	 // Compare Credentials value with  
	 public function checkTable($credentials) {

	 	if(isset($credentials['password'])) {
	 		$credentials['password'] = md5($credentials['password']);
	 	}

	 	$result = $this->CI->db->get_where($this->table, $credentials)->row();

	 	if(sizeof($result) > 0) {
	 		$data 			= array();
	 		$data['type'] 	= $this->table;
	 		foreach($result as $key => $value) {
	 			if($key != 'password') {
	 				$data[$key] = $value;
	 			}
	 		}
	 		$this->CI->session->set_userdata($data);
	 		return true;
	 	}

	 	return false;
	 }


	 public function logOut() {
	 	$this->CI->session->sess_destroy();
	 }	

}
