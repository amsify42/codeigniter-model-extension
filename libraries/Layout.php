<?php 


class Layout {

	 private $layout 	= 'frontend';
	 private $dir 		= array(
					'frontend' 		=> 'base',
					'admin' 		=> 'admin',
					'candidate' 	=> 'candidate',
					);

	 public $data 		= array(
					'header' => array(
						'title' => 'Panzer Solutions Online Test'
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
	 		$data['userdata']					= $this->CI->session->userdata();
	 		$this->data['userdata'] 			= $this->CI->session->userdata();
	 	 	$this->data['header']['userdata'] 	= $this->CI->session->userdata();
	 	 	$this->data['menu']['userdata'] 	= $this->CI->session->userdata();
	 	 	$this->data['footer']['userdata'] 	= $this->CI->session->userdata();
	 	}
	 	$data['basicModel'] 			= $this->CI->basic;
	 	$this->data['_render_body'] 	= $this->CI->load->view($this->dir[$this->layout].'/'.$file, $data, TRUE);
	 	$this->data['render_section'] 	= $this->CI;

	 	// Set title if passed
	 	if($title != '') {
	 		$this->data['header']['title'] = $title;
	 	}

	 	// Set Menu if passed
	 	if($menu != '') {
	 		$this->data['menu']['title']   = $menu;
	 	}


	 	// If call is ajax send only body section as html
	 	if($this->CI->input->is_ajax_request()) {
				$result = array(
							'status' => 'success',
							'html'	 => $this->data['_render_body']
							);
			return $this->CI->output->set_content_type('application/json')
				                ->set_output(json_encode($result));
		}

	 	$this->CI->load->view('layout/'.$this->layout, $this->data);

	 }


	 public function render($file, $data = array()) {
		return $this->CI->load->view($this->dir[$this->layout].'/'.$file, $data, TRUE);
	 }



	 public function response($status = 'error', $message = '', $redirect = '', $data = array())	{

	 	// If request is Ajax
	 	if($this->CI->input->is_ajax_request()) {
	 		$result = array('status' => $status, 'message' => $message);
	 		// If data is set
	 		if(sizeof($data) > 0) { 
	 			$result = array_merge($result, $data);
	 		}

			return $this->CI->output->set_content_type('application/json')
				                	->set_output(json_encode($result));

		// If request is not Ajax				                	
	 	} else {
	 		$type = 'message';
	 		if($status == 'error') {
	 			$type = 'error';
	 		}

	 		if($message) {
	 			$this->session->set_flashdata($type, $message);
	 		}

	 		if(isset($data['errors'])) {
	 			$this->session->set_flashdata('errors', $data['errors']);	
	 		}

	 		return redirect($redirect);
	 	}
	 }

}
