<?php
require_once 'Pagination.php';
class MY_Model extends Pagination {

	 protected   $table  = '';
	 protected   $data   = array();	

   function __construct(){
      parent::__construct();
      if($this->table == '') {
      	$this->table = strtolower(get_called_class());
      }
    }

    public function getTable() {
      return $this->table;
    }

    public function count_all() {
      return $this->db->count_all($this->table);
    }

    public function getAll() {
      return $this->db->select('*')->from($this->table)->get()->result();
    }

    public function find($id) {
    	if($id) {
    		return $this->db->get_where($this->table, array('id' => $id))->row();
    	}
    	return $this->data;
    }

    public function insert($data = array()) {
     	if(sizeof($data) > 0) {
    		return $this->db->insert($this->table, $data);
    	}
      	return false;
    }

    public function update($id, $data = array()) {
     	if($id && sizeof($data) > 0) {
     		return $this->db->where('id', $id)->update($this->table, $data);
      }
      	return false;
    }

    public function delete($id) {
    	if($id) {
       	return $this->db->where('id', $id)->delete($this->table);
      }
      return false;
    }

}
