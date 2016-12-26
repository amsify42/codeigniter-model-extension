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

      public function getWhere($conditions) {
        if(is_array($conditions)) {
          return $this->db->get_where($this->table, $conditions)->result();
        }
        return false;
      }

      public function find($id) {
      	if($id) {
          if(is_array($id)) {
            return $this->db->get_where($this->table, $id)->row();
          } else {
      		return $this->db->get_where($this->table, array('id' => $id))->row();
          }
      	}
      	return $this->data;
      }

      public function insert($data = array()) {
       	if(sizeof($data) > 0) {
      		$this->db->insert($this->table, $data);
          return $this->db->insert_id();
      	}
        	return false;
      }

      public function insertBatch($data = array()) {
        if(sizeof($data) > 0) {
          return $this->db->insert_batch($this->table, $data);
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


      public function deleteBatch($conditions) {
        if($conditions) {
          if(is_array($conditions)) {
            $IDsArray = $this->getIDsArray($conditions);
            $this->db->where_in('id', $IDsArray);
            $this->db->delete($this->table);
          } 
        }
        return $this->data;
      }

      public function getIDsArray($conditions) {
        $result = $this->db->select('id')->get_where($this->table, $conditions)->result_array();
        $IDs    = array();
        if(sizeof($result)> 0) {
          foreach ($result as $res) {
            $IDs[] = $res['id'];
          }
        }
        return $IDs;
      }


      public function checkUnique($column, $value, $id = 0) {
          $row = array();
          if($id == 0) {
            $row = $this->db->get_where($this->table, array($column => $value))->num_rows();
          } else {
            $row = $this->db->get_where($this->table, array('id !=' => $id, $column => $value))->num_rows();
          }

          if($row > 0) {
             return true;
          }

          return false;
      }

  }
