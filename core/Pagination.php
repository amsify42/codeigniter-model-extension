<?php
class Pagination extends CI_Model {

	private    $page           = 1;
	private    $config         = array();
	private    $conditions     = false;
	private    $limit  	       = 1; 
	private    $customQuery;
  	protected  $CI_Conditions  = array();
  	protected  $relations      = array();
  	protected  $objectRelease  = array(
					'get',
					'result',
					'result_array',
					'result_object',
					'custom_result_object',
					'row',
					'unbuffered_row',
					'row_array',
					'row_object',
					'custom_row_object',
					'data_seek',
					'set_row',
					'next_row',
					'previous_row',
					'first_row',
					'last_row',
					'num_rows',
					'num_fields',
					'field_data',
					'free_result',
					'list_fields'
					); 		


	function __construct(){
	      parent::__construct();
	      $this->config['uri_segment']        = $this->getURISegment();
	      $this->config['base_url']           = $this->getCurrentURL();
	      $this->config['num_links']          = 5; 
	      $this->config['use_page_numbers']   = TRUE;
	      $this->config['page_query_string']  = FALSE;

	      $this->config['full_tag_open']      = '<ul class="pagination">';
	      $this->config['full_tag_close']     = '</ul>';

	      $this->config['first_link']         = 'First';
	      $this->config['first_tag_open']     = '<li>';
	      $this->config['first_tag_close']    = '</li>';

	      $this->config['prev_link']          = 'Prev';
	      $this->config['prev_tag_open']      = '<li>';
	      $this->config['prev_tag_close']     = '</li>';

	      $this->config['next_link']          = 'Next';
	      $this->config['next_tag_open']      = '<li>';
	      $this->config['next_tag_close']     = '</li>';

	      $this->config['cur_tag_open']       = '<li class="active"><a href="#">';
	      $this->config['cur_tag_close']      = '</a></li>';

	      $this->config['num_tag_open']       = '<li>';
	      $this->config['num_tag_close']      = '</li>';

	      $this->config['last_link']          = 'Last';
	      $this->config['last_tag_open']      = '<li>';
	      $this->config['last_tag_close']     = '</li>';

    }	


    public function __call($method, $args){
       if(method_exists($this->db, $method) && is_callable(array($this->db, $method))) {
          if(in_array($method, $this->objectRelease)) {
              $this->db->from($this->table);
              if($method == 'get') {
                return call_user_func_array(array($this->db, $method), $args);
              } else {
                return $this->filterRelations(call_user_func_array(array($this->db, $method), $args));
              }
          } else {
            $this->CI_Conditions[] = array('method' => $method, 'args' => $args);
            call_user_func_array(array($this->db, $method), $args);
            return $this;
          }
        }
        else if(in_array($method, $this->objectRelease)) {
            $this->db->from($this->table);
            return $this->filterRelations(call_user_func_array(array($this->db->get(), $method), $args));
        }
    }



    public function conditions($conditions, $query = false) {
        if($query) {
          return $conditions($query);
        } else {
          $this->db           = $conditions($this->db);
          $this->customQuery  = $conditions;
          $this->conditions   = true;
          return $this;
        }
    }




    public function paginate($limit = '', $page = '', $config = array()) {
        // Update Limit If supplied
        if($limit != '') {
          $this->limit = $limit;
        }

        // Update Config If supplied
        if(sizeof($config) > 0) {
          $this->config   = $this->exchangeUpdatedKeys($config);
        }

        if($page != '') {
            $this->page   = $page;
        } else {
          if($this->uri->segment($this->config['uri_segment'])) {
              $this->page   = (int)($this->uri->segment($this->config['uri_segment']));
          }
        }
        
        return $this->getPaginateResult($this->limit, $this->page);
    }


    public function getPaginateResult($limit, $page) {

        
        $this->db->from($this->table);

        if($page == 1) {
           $query   = $this->db->limit($limit)->get(); 
        } else {
           $page    = $page - 1;
           $page    = $page*$limit;
           $query   = $this->db->limit($limit, $page)->get(); 
        }

        if($this->conditions || sizeof($this->CI_Conditions)>0) {
          $this->config['total_rows'] = $this->countRows();
        } else {
          $this->config['total_rows'] = $this->count_all();
        }

        if(!$query->num_rows()){
          return $this->data;
        } else {
          return $this->filterRelations($query->result());
        }

    }

    public function countRows() {
        $this->db->trans_start();
        $this->db->select('id');
        $this->db->from($this->table);
        if(sizeof($this->CI_Conditions)>0) {
          foreach($this->CI_Conditions as $key => $CI_condition) {
              call_user_func_array(array($this->db, $CI_condition['method']), $CI_condition['args']);
          }
        }

        if($this->conditions) {
          $this->db = $this->conditions($this->customQuery, $this->db);
        }
        return $this->db->get()->num_rows();
    }


    public function getURISegment() {
      $last     = $this->uri->total_segments();
      $segment  = $this->uri->segment($last);
      
      if(!is_numeric($segment)) {
          $last++;
      } 
      return $last;
    } 


    public function getCurrentURL() {
       $currentURL = base_url(uri_string());  
       $segment    = $this->uri->segment($this->config['uri_segment']);

       if($segment) {
          $currentURL = rtrim($currentURL, '/'.(int)($segment));
        }
        return $currentURL;
    }



    public function getLinks() {        
        return $this->paginationLinks();
    }

    public function getTotalRows() {
		return $this->config['total_rows'];
    }


    public function paginationLinks() {
         
        $this->config['per_page']     = $this->limit;
        // Initializing codeignitor pagination library
        $this->load->library('pagination');
        $this->pagination->initialize($this->config); 

        return $this->pagination->create_links();
    }


    public function exchangeUpdatedKeys($config) {
      if(sizeof($config) > 0) {
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
      }
      return $this->config;
    }

}
