<?php
require_once 'Pagination.php';

/**
 *  This class must be extended by model classes to inherit the functionalities
 */
class MY_Model extends Pagination {

   /**
    * Name of the database table
    * @var string
    */
   protected   $table        = '';

   /**
    * Database table column names
    * @var array
    */
   protected   $fillable     = array();

   /**
    * Database table column names
    * @var array
    */
   protected   $hidden       = array();

   /**
    * Temporary array for returning
    * @var array
    */
	 protected   $data         = array();	

   /**
    * True, if relational data is being called
    * @var boolean
    */
   protected   $getRelation  = false;

   /**
    * For checking result type fetched by queries
    * @var string
    */
   protected   $resultType   = 'object';

   /**
    * For checking result fetched is single row or multiple
    * @var string
    */
   protected   $rowType      = 'multiple';

   /**
    * This will set automatic table name based on name of model class
    */
   function __construct(){
      parent::__construct();
      if($this->table == '') {
      	$this->table = strtolower(get_called_class());
      }
    }

    /**
     * For getting the name of table
     * @return string
     */
    public function getTable() {
      return $this->table;
    }

    /**
     * It will simply send the result of raw query passed
     * @param  string $query
     * @param  string $type
     * @return array
     */
    public function rawQuery($query, $type = 'object') {
      if($type == 'array') {
        return $this->db->query($query)->result_array();  
      }
      return $this->db->query($query)->result();
    }

    /**
     * count of all rows present in table 
     * @return integer
     */
    public function count_all() {
      return $this->db->count_all($this->table);
    }


    /**
     * get all rows present in table 
     * @return array
     */
    public function getAll() {
      return $this->db->select('*')->from($this->table)->get()->result();
    }


    /**
     * Get rows based on array of conditions passed
     * @param  array $conditions
     * @return aray or boolean
     */
    public function getWhere($conditions) {
      if(is_array($conditions)) {
        return $this->db->get_where($this->table, $conditions)->result();
      }
      return false;
    }

    /**
     * Get rows based on multiple Ids passed with column name
     * @param  array  $IDsArray
     * @param  string $column
     * @param  string $type
     * @return array
     */
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


    /**
     * Get single row based on id or condition is passed
     * @param  integer $id
     * @param  string  $column
     * @return array
     */
    public function find($id, $column = 'id') {
    	if($id) {
        if(is_array($id)) {
          return $this->db->get_where($this->table, $id)->row();
        } else {
    		  return $this->db->get_where($this->table, array($column => $id))->row();
        }
    	}
    	return $this->data;
    }


    /**
     * return maximum of column values
     * @param  $string $column
     * @return integer
     */
    public function max($column) {
      return $this->db->select_max($column)->get($this->table)->result()[0]->$column;
    }


    /**
     * return minimum of column values
     * @param  $string $column
     * @return integer
     */
    public function min($column) {
      return $this->db->select_min($column)->get($this->table)->result()[0]->$column;
    }


    /**
     * return average of column values
     * @param  $string $column
     * @return integer
     */
    public function avg($column) {
      return $this->db->select_avg($column)->get($this->table)->result()[0]->$column;
    }


    /**
     * return sum of column values
     * @param  $string $column
     * @return integer
     */
    public function sum($column) {
      return $this->db->select_sum($column)->get($this->table)->result()[0]->$column;
    }


    /**
     * For inserting single row in table
     * @param  array  $data [description]
     * @return integer or boolean
     */
    public function insert($data = array()) {
     	if(sizeof($data) > 0) {
        $data = $this->filterFillable($data);
    		$this->db->insert($this->table, $data);
        return $this->db->insert_id();
    	}
      	return false;
    }


    /**
     * For inserting multiple rows in table
     * @param  array  $data [description]
     * @return boolean
     */
    public function insertBatch($data = array()) {
      if(sizeof($data) > 0) {
        $data = $this->filterFillable($data, 'batch');
        return $this->db->insert_batch($this->table, $data);
      }
        return false;
    }


    /**
     * For updating a row in table by id or condition
     * @param  integer or array $id
     * @param  array   $data
     * @param  string  $column
     * @return boolean
     */
    public function update($id, $data = array(), $column = 'id') {
     	if($id && sizeof($data) > 0) {
        $data = $this->filterFillable($data);
        if(is_array($id)) {
     		 return $this->db->where($id)->update($this->table, $data);
        } else {
          return $this->db->where($column, $id)->update($this->table, $data);
        }
      }
      	return false;
    }

    /**
     * For deleting single row by id or conditions
     * @param  integer or array $id
     * @param  string  $column
     * @return boolean
     */
    public function delete($id, $column = 'id') {
    	if($id) {
        if(is_array($id)) {
          return $this->db->where($id)->delete($this->table);
        } else {
          return $this->db->where($column, $id)->delete($this->table);
        }
      }
      return false;
    }

    /**
     * For deleting multiple rows by conditions
     * @param  array  $conditions
     * @param  string $column
     * @return boolean
     */
    public function deleteBatch($conditions, $column = 'id') {
      if($conditions) {
        if(is_array($conditions)) {
          $IDsArray = $this->getIDsArray($conditions, $id);
          $this->db->where_in($column, $IDsArray);
          return $this->db->delete($this->table);
        } 
      }
      return false;
    }


    /**
     * For deleting multiple rows by Ids
     * @param  array  $conditions
     * @param  string $column
     * @return boolean
     */
    public function deleteIDs($IDs, $column = 'id') {
      if($IDs) {
        if(is_array($IDs)) {
          $this->db->where_in($column, $IDs);
          return $this->db->delete($this->table);
        } 
      }
      return false;
    }


    /**
     * For deleting all rows from the table
     * @return boolean
     */
    public function truncate() {
      return $this->db->truncate($this->table);
    }


    /**
     * For getting all the Ids of array of result
     * @param  array  $conditions
     * @param  string $id
     * @return array
     */
    public function getIDsArray($conditions, $id = 'id') {
      $result = $this->db->select($id)->get_where($this->table, $conditions)->result_array();
      $IDs    = array();
      if(sizeof($result)> 0) {
        foreach ($result as $res) {
          $IDs[] = $res[$id];
        }
      }
      return $IDs;
    }


    /**
     * Check if row existed with column value
     * @param  string  $column
     * @param  any     $value
     * @param  integer $id
     * @param  string  $primary
     * @return boolean
     */
    public function checkUnique($column, $value, $id = 0, $primary = 'id') {
        $row = array();
        if($id == 0) {
          $row = $this->db->get_where($this->table, array($column => $value))->num_rows();
        } else {
          $row = $this->db->get_where($this->table, array($primary.' !=' => $id, $column => $value))->num_rows();
        }

        if($row > 0) {
           return true;
        }

        return false;
    }



   /**
    * For converting data set into fillable column names
    * @param  array   $data
    * @param  boolean $batch
    * @return array
    */
   private function filterFillable($data, $batch = false) {
      if(sizeof($this->fillable) > 0) {
        if($batch) {
          foreach($data as $key => $row) {
            foreach($row as $col => $val) {
              if(!in_array($col, $this->fillable)) {
                unset($data[$key][$col]);
              }
            }
          }
        } else {
          foreach($data as $col => $val) {
            if(!in_array($col, $this->fillable)) {
              unset($data[$col]);
            }
          }
        }
      }
      return $data;
   }


   /**
    * For setting relations as true for fetching relational result
    * @return object
    */
   public function relate() {
      $this->getRelation = true;
      return $this;
    }

   /**
    * For getting all the Ids of the result
    * @param  array  $result
    * @param  string $id
    * @param  string $column
    * @return array
    */
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

   
   /**
    * For adding relation to database table
    * @param array $relation
    */
    protected function addRelation($relation = array()) {
        if(sizeof($relation) > 0) {
          $this->relations[] = $relation;
        }
    } 


    /**
     * For adding multiple relations to database table
     * @param array $relations
     */
    protected function addRelations($relations = array()) {
        if(sizeof($relations) > 0) {
          foreach($relations as $relation) {
            $this->relations[] = $relation;
          }
        }
    } 


    /**
     * This filtering will check all the settings did in model class
     * @param  array $result
     * @return array
     */
    protected function filterResults($result) {

      if(!$this->getRelation && sizeof($this->hidden) == 0) {
        return $result;
      }

      if(method_exists($this, 'setRelations') && is_callable(array($this, 'setRelations'))) {
          $this->setRelations();
      }

      if(sizeof($result) > 0) {
        if(sizeof($this->hidden) > 0) {
          $result = $this->hideData($result);
        }
        if(sizeof($this->relations) > 0) {
          $result = $this->processRelations($result);
        }
      }

      return $result;
    }


    /**
     * Hide result data with column names against setting did in model class
     * @param  array $result
     * @return array
     */
    protected function hideData($result) {

      $type = '';

      foreach($result as $res) {

        if($type == '') {
          if(is_object($res)) {
            $type = 'object';
          }
          else if(is_array($res)) {
            $type = 'array';
          }
        }

        foreach($this->hidden as $hide) {
          if($type == 'object') {
            if(isset($res->$hide)) {
              unset($res->$hide);
            }
          }
          else if($type == 'array') {
           if(isset($res[$hide])) {
              unset($res[$hide]);
            }  
          }
        }

      }
      return $result;
    }


    /**
     * Process and find relations to the result data
     * @param  array $result
     * @return array
     */
    protected function processRelations($result) {
        if(sizeof($this->relations) > 0) {
          foreach($this->relations as $relation) {

            $primary    = isset($relation['primary'])? $relation['primary'] : 'id';
            $foreign    = isset($relation['foreign'])? $relation['foreign'] : $this->table.'_id';
            $variable   = isset($relation['variable'])? $relation['variable'] : '';
            $column     = isset($relation['column'])? $relation['column'] : '';
            if($variable ==  '' && isset($relation['table'])) {
              $variable  = strtolower($relation['table']);
            }

            if(isset($relation['table'])) {
              $result = $this->attachRelation($result, $primary, $foreign, $variable, $relation['table'], 'table', $column);
            }
            else if(isset($relation['model'])) {
              $result = $this->attachRelation($result, $primary, $foreign, $variable, $relation['model'], 'model', $column);
            }
          }
        }
        return $result;
    }



    /**
     * Function will add relational data to result data
     * @param  array  $result
     * @param  string $primary
     * @param  string $foreign
     * @param  string $variable
     * @param  string $table
     * @param  string $type
     * @param  string $column
     * @return array
     */
    protected function attachRelation($result, $primary, $foreign, $variable, $table, $type = 'table', $column = '') {

        $IDs = $this->getResultIDsArray($result, $primary, $variable);

        if(sizeof($IDs) > 0) {

          if($type == 'model') {
            $this->load->model($table);
            $rows   = $this->$table->getWhereIDs($IDs, $foreign, $this->resultType);
          } else {
            $IDS    = implode(',', $IDs);
            $rows   = $this->rawQuery("SELECT * FROM {$table} WHERE {$foreign} IN({$IDS})", $this->resultType);
          }

          foreach($result as $key => $res) {
            if($this->resultType == 'object') {
              if($this->rowType == 'single') {
                $result->{$variable} = $this->extractRelatedRows($rows, $result->$primary, $foreign, $column);
              } else {
                $result[$key]->{$variable} = $this->extractRelatedRows($rows, $res->$primary, $foreign, $column);
              }
            }
            else if($this->resultType == 'array') {
              if($this->rowType == 'single') {
                $result[$variable] = $this->extractRelatedRows($rows, $result[$primary], $foreign, $column);
              } else {
                $result[$key][$variable] = $this->extractRelatedRows($rows, $res[$primary], $foreign, $column);
              }
            }
          }
          
        }

        return $result;
    }


    /**
     * Find and get related rows from other tables
     * @param  array or object $rows
     * @param  integer $primaryID
     * @param  string $foreign
     * @param  string $column
     * @return array or object
     */
    protected function extractRelatedRows($rows, $primaryID, $foreign, $column = '') {

      $result = new stdClass();
      if($this->resultType == 'array') {
        $result = array();  
      }
      if($column != '') {
        $result = '';
      }

      if(sizeof($rows) > 0) {
        $i = 0;
        foreach($rows as $row) {
          if($this->resultType == 'object') {
            if($row->$foreign == $primaryID) {
              if($column == '') {
                $result->{$i} = $row;
              } else {
                return $this->getColumnValue($row, $column);
              }
              $i++;
            }
          }
          else if($this->resultType == 'array') {
            if($row[$foreign] == $primaryID) {
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


    /**
     * Get the column value based on type
     * @param  object $row    
     * @param  string $column 
     * @return string $column 
     */
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
