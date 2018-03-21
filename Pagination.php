<?php
/**
 *  This class is extending CI_Model and being extended by MY_Model.
 *  All the pagination functionalities are in this class
 *  _call which will filter results based on settings of model class
 */
class Pagination extends CI_Model {

  /**
   * Pagination page number
   * @var integer
   */
  private    $page           = 1;

  /**
   * This is the array of pagination config
   * @var array
   */
  private    $config         = array();

  /**
   * Set true if conditions passed for pagination
   * @var boolean
   */
  private    $conditions     = false;

  /**
   * Pagination limit per page
   * @var integer
   */
  private    $limit          = 1; 

  /**
   * Query created based on conditions passed
   * @var string
   */
  private    $customQuery;

  /**
   * This is a collection of conditions passed to pagination
   * @var array
   */
  protected  $CI_Conditions  = array();

  /**
   * This is a collection of relations added in model class
   * @var array
   */
  protected  $relations      = array();

  /**
   * This is a collection of CI db method names which release results
   * @var array
   */
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


  /**
   * __construct contains the default config of pagination
   */
  function __construct(){
      parent::__construct();
      $this->config['uri_segment']        = $this->getURISegment();
      $this->config['base_url']           = $this->getCurrentURL();
      $this->config['num_links']          = 5; 
      $this->config['use_page_numbers']   = TRUE;
      $this->config['page_query_string']  = TRUE;

      $this->config['full_tag_open']      = '<ul class="pagination">';
      $this->config['full_tag_close']     = '</ul>';

      $this->config['first_link']         = 'First';
      $this->config['first_tag_open']     = '<li class="page-item">';
      $this->config['first_tag_close']    = '</li>';

      $this->config['prev_link']          = 'Prev';
      $this->config['prev_tag_open']      = '<li class="page-item">';
      $this->config['prev_tag_close']     = '</li>';

      $this->config['next_link']          = 'Next';
      $this->config['next_tag_open']      = '<li class="page-item">';
      $this->config['next_tag_close']     = '</li>';

      $this->config['cur_tag_open']       = '<li class="page-item active"><a class="page-link" href="#">';
      $this->config['cur_tag_close']      = '</a></li>';

      $this->config['num_tag_open']       = '<li class="page-item">';
      $this->config['num_tag_close']      = '</li>';

      $this->config['last_link']          = 'Last';
      $this->config['last_tag_open']      = '<li class="page-item">';
      $this->config['last_tag_close']     = '</li>';

      $this->config['anchor_class']       = 'class="page-link" ';
    } 


    /**
     * __call will filer results based on settings passed in model class
     * @param  string $method
     * @param  array $args
     * @return array
     */
    public function __call($method, $args){
       if(method_exists($this->db, $method) && is_callable(array($this->db, $method))) {
          if(in_array($method, $this->objectRelease)) {
              $this->db->from($this->table);
              if($method == 'get') {
                return call_user_func_array(array($this->db, $method), $args);
              } else {
                return $this->filterResults(call_user_func_array(array($this->db, $method), $args));
              }
          } else {
            $this->CI_Conditions[] = array('method' => $method, 'args' => $args);
            call_user_func_array(array($this->db, $method), $args);
            return $this;
          }
        }
        else if(in_array($method, $this->objectRelease)) {
            $this->db->from($this->table);
            return $this->filterResults(call_user_func_array(array($this->db->get(), $method), $args));
        }
    }


    /**
     * Set conditions for pagination
     * @param  object  $conditions
     * @param  boolean $query
     * @return object
     */
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



    /**
     * Get paginated result
     * @param  string $limit
     * @param  string $page
     * @param  array  $config
     * @return array
     */
    public function paginate($limit = '', $page = '', $config = array()) {
        // Update Limit If supplied
        if($limit != '') {
          $this->limit = $limit;
        }

        // Update Config If supplied
        if(sizeof($config) > 0) {
          $this->config   = $this->paginationConfig($config);
        }

        if($page != '') {
            $this->page   = $page;
        } else {
          if($this->config['page_query_string'] && isset($_GET['per_page'])) {
              $this->page   = $this->input->get('per_page', TRUE);
          } else if($this->uri->segment($this->config['uri_segment'])) {
              $this->page   = (int)($this->uri->segment($this->config['uri_segment']));
          }
        }
        
        return $this->getPaginateResult($this->limit, $this->page);
    }


    /**
     * Get paginated result
     * @param  integer $limit
     * @param  integer $page
     * @return array
     */
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
          $this->config['total_rows'] = $this->countAll();
        }

        if(!$query->num_rows()){
          return $this->data;
        } else {
          return $this->filterResults($query->result());
        }
    }



    /**
     * Count rows of result
     * @return integer
     */
    public function countRows() {
        $this->db->trans_start();
        $this->db->select($this->primaryKey);
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


    /**
     * Get uri segment
     * @return integer
     */
    public function getURISegment() {
      $last     = $this->uri->total_segments();
      $segment  = $this->uri->segment($last);
      
      if(!is_numeric($segment)) {
          $last++;
      } 
      return $last;
    } 


    /**
     * Get current url
     * @return string
     */
    public function getCurrentURL() {
       $currentURL = base_url(uri_string());  
       $segment    = $this->uri->segment($this->config['uri_segment']);

       if($segment) {
          $currentURL = rtrim($currentURL, '/'.(int)($segment));
        }
        return $currentURL;
    }


    /**
     * Generate Pagination links
     * @return string
     */
    public function getLinks() {        
        return $this->paginationLinks();
    }


    /**
     * Get total rows of result
     * @return integer
     */
    public function getTotalRows() {
    return $this->config['total_rows'];
    }



    /**
     * Generate Pagination links
     * @return string
     */
    public function paginationLinks() {
         
        $this->config['per_page']     = $this->limit;
        // Initializing codeignitor pagination library
        $this->load->library('pagination');
        $this->pagination->initialize($this->config); 

        return $this->pagination->create_links();
    }


    /**
     * For setting config for pagination
     * @param  array $config
     * @return array
     */
    public function paginationConfig($config) {
      if(sizeof($config) > 0) {
        foreach($config as $key => $value) {
            $this->config[$key] = $value;
        }
      }
      return $this->config;
    }

}