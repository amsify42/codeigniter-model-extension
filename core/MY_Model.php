<?php
require_once 'Pagination.php';
class MY_Model extends Pagination {

protected   $table        = '';
protected   $data         = array();	
protected   $getRelation  = false;
protected   $resultType   = 'object';
protected   $rowType      = 'multiple';

   function __construct(){
      parent::__construct();
      if($this->table == '') {
      	$this->table = strtolower(get_called_class());
      }
    }

    public function getTable() {
      return $this->table;
    }

    public function rawQuery($query, $type = 'object') {
      if($type == 'array') {
        return $this->db->query($query)->result_array();  
      }
      return $this->db->query($query)->result();
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


    public function getWhereIDs($IDsArray, $column = 'id', $type = 'object') {
      if($IDsArray) {
        if(is_array($IDsArray)) {
           $query = $this->db->from($this->table)->where_in($column, $IDsArray)->get();
           if($type == 'array') {
            return $query->result_array();
           } else {
            return $query->result();
           }
        } 
      }
      return $this->data;
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



   /* Relational Methods  */

   public function relate() {
      $this->getRelation = true;
      return $this;
    }

   public function getResultIDsArray($result, $id = 'id', $column = '') {

      $IDs    = array();

      if(sizeof($result)> 0) {

        foreach($result as $res) {
          if(is_object($res)) {
            if(isset($res->$column)) {
              return $IDs;
            }
            $this->resultType = 'object';
            $IDs[] = $res->$id;
          }

          else if(is_array($res)) {
            if(isset($res[$column])) {
              return $IDs;
            }
            $this->resultType = 'array';
            $IDs[] = $res[$id];
          }

          else {
            $this->rowType = 'single';
            if(isset($result->$id)) {
              $IDs[] = $result->$id;
              break;
            }
            else if(isset($result[$id])) {
              $IDs[] = $result[$id];
              break;
            }
          }
        }
      }

      return $IDs;
    } 

   
    protected function addRelation($relation = array()) {
        if(sizeof($relation) > 0) {
          $this->relations[] = $relation;
        }
    } 

    protected function addRelations($relations = array()) {
        if(sizeof($relations) > 0) {
          foreach($relations as $relation) {
            $this->relations[] = $relation;
          }
        }
    } 

    protected function filterRelations($result) {

      if(!$this->getRelation) {
        return $result;
      }

      if(method_exists($this, 'setRelations') && is_callable(array($this, 'setRelations'))) {
          $this->setRelations();
      }

      if(sizeof($result) > 0) {
        if(sizeof($this->relations) > 0) {
          $result = $this->processRelations($result);
        }
      }

      return $result;
    }

    protected function processRelations($result) {
        if(sizeof($this->relations) > 0) {
          foreach($this->relations as $relation) {

            $primary    = isset($relation['primary'])? $relation['primary'] : 'id';
            $foriegn    = isset($relation['foriegn'])? $relation['foriegn'] : $this->table.'_id';
            $variable   = isset($relation['variable'])? $relation['variable'] : '';
            $column     = isset($relation['column'])? $relation['column'] : '';
            if($variable ==  '' && isset($relation['table'])) {
              $variable  = strtolower($relation['table']);
            }

            if(isset($relation['table'])) {
              $result = $this->attachRelation($result, $primary, $foriegn, $variable, $relation['table'], 'table', $column);
            }
            else if(isset($relation['model'])) {
              $result = $this->attachRelation($result, $primary, $foriegn, $variable, $relation['model'], 'model', $column);
            }
          }
        }
        return $result;
    }

    protected function attachRelation($result, $primary, $foriegn, $variable, $table, $type = 'table', $column = '') {

        $IDs = $this->getResultIDsArray($result, $primary, $variable);

        if(sizeof($IDs) > 0) {

          if($type == 'model') {
            $this->load->model($table);
            $rows   = $this->$table->getWhereIDs($IDs, $foriegn, $this->resultType);
          } else {
            $IDS    = implode(',', $IDs);
            $rows   = $this->rawQuery("SELECT * FROM {$table} WHERE {$foriegn} IN({$IDS})", $this->resultType);
          }

          foreach($result as $key => $res) {
            if($this->resultType == 'object') {
              if($this->rowType == 'single') {
                $result->{$variable} = $this->extractRelatedRows($rows, $result->$primary, $foriegn, $column);
              } else {
                $result[$key]->{$variable} = $this->extractRelatedRows($rows, $res->$primary, $foriegn, $column);
              }
            }
            else if($this->resultType == 'array') {
              if($this->rowType == 'single') {
                $result[$variable] = $this->extractRelatedRows($rows, $result[$primary], $foriegn, $column);
              } else {
                $result[$key][$variable] = $this->extractRelatedRows($rows, $res[$primary], $foriegn, $column);
              }
            }
          }
          
        }

        return $result;
        
    }


    protected function extractRelatedRows($rows, $primaryID, $foriegn, $column = '') {

      $result = new stdClass();
      if($this->resultType == 'array') {
        $result = array();  
      }
      if($column != '') {
        $result = '';
      }

      if(sizeof($rows)) {
        $i = 0;
        foreach($rows as $row) {
          if($this->resultType == 'object') {
            if($row->$foriegn == $primaryID) {
              if($column == '') {
                $result->{$i} = $row;
              } else {
                return $this->getColumnValue($row, $column);
              }
              $i++;
            }
          }
          else if($this->resultType == 'array') {
            if($row[$foriegn] == $primaryID) {
              if($column == '') {
                $result[$i] = $row;
              } else {
                return $this->getColumnValue($row, $column);
              }
              $i++;
            }
          }
        }
        return $result;
      }
      return NULL;
    }


    protected function getColumnValue($row, $column) {

        $value = '';

        if($this->resultType == 'object') {
          if(is_array($column)) {
            if(isset($column['modify'])) {
              $value = str_replace("_COL_", $row->$column['name'], $column['modify']);
            } else {
              $value = $row->$column['name'];
            }
          } else {
            $value = $row->$column;
          }
        }
        else if($this->resultType == 'array') {
          if(is_array($column)) {
            if(isset($column['modify'])) {
              $value = str_replace("_COL_", $row[$column['name']], $column['modify']);
            } else {
              $value = $row[$column['name']];
            }
          } else {
            $value = $row[$column];
          }
        }

        return $value;
    }

}
