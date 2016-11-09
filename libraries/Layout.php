<?php 


class Layout {

	 private $layout 	= 'frontend';
	 private $dir 		= array(
	 						'frontend' 		=> 'base',
	 						'superadmin' 	=> 'superadmin',
	 						'admin' 		  => 'admin',
	 						);

	 public $data 		= array(
	 						'header' => array(
	 							'title' => 'Some title'
	 						),
	 						'menu' 	 => array(
	 							'title' => 'home'
	 							),	
	 						'footer' => array()	
	 					);

	 function __construct($params = array()) {
	 	if(isset($params['layout'])) {
	 		$this->layout = $params['layout'];
	 	}
	 	$this->CI = & get_instance();
	 	$this->CI->load->model("basic");
	 }

	 public function view($file, $data = array(), $title = '', $menu = '') {

	 	// If login session is created
	 	if(sizeof($this->CI->session->userdata()) > 1) {
	 		$data['userdata']					          = $this->CI->session->userdata();
	 		$this->data['userdata'] 			      = $this->CI->session->userdata();
	 	 	$this->data['header']['userdata'] 	= $this->CI->session->userdata();
	 	 	$this->data['menu']['userdata'] 	  = $this->CI->session->userdata();
	 	 	$this->data['footer']['userdata'] 	= $this->CI->session->userdata();
	 	}
	 	$data['basicModel'] 			    = $this->CI->basic;
	 	$this->data['_render_body'] 	= $this->CI->load->view($this->dir[$this->layout].'/'.$file, $data, TRUE);
	 	$this->data['render_section'] = $this->CI;

	 	// Set title if passed
	 	if($title != '') {
	 		$this->data['header']['title'] = $title;
	 	}

	 	// Set Menu if passed
	 	if($menu != '') {
	 		$this->data['menu']['title']   = $menu;
	 	}

	 	$this->CI->load->view('layout/'.$this->layout, $this->data);
	 }	

}
