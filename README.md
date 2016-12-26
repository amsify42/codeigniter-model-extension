Codeigniter Model Extension
---------------------------

The main purpose of these files is to make queries to database tables easier and create pagination with simple calls.

For Extending model classes you need put two files 
**MY_Model.php** 
**Pagination.php** 
in **application/core/**

Whenever you create model class, extend MY_Model instead of CI_Model like this

    class Users extends MY_Model {
    
    }

Now, when you load this model in any controller, you can use like this

    class Base extends CI_Controller {
          function __construct() {
    		parent::__construct();	
    		$this->load->model('users');
    	}

		public function home() {
		 $data['users] = $this->users->getAll();
		}
    }

As you can see, we are calling getAll() method which will get all the rows from database table **users**.

If your database table name is different than model class name then you can add table name in protected property of your model class like this
		
     class Users extends MY_Model {
    	 protected $table  = 'app_users';    
      }

Now, all methods called using this model will load data from table **app_users** instead of **users**

Just like we are using **getAll()** method to get all rows from table users, you can also use below methods


To get count of all rows

    $this->users->count_all();

To get the rows with conditions passed to it

    $conditions = array('active' => 1);
    $this->users->getWhere($conditions);

Get single row with particular ID or Conditions

    $this->users->find(1);
    // or
    $conditions = array('id' => 1, 'active' => 1);
    $this->users->find($conditions);

To insert single row in table

    $row = array('name' => 'some name', 'active' => 1);
    $this->users->insert($row);
To insert multiple rows in table

    $rows = array(
          array('name' => 'some name', 'active' => 1),
          array('name' => 'some name 2', 'active' => 0)
          );
    $this->users->insertBatch($rows);

To update single row in table

  
    $id  = 1;
    $row = array('name' => 'some name', 'active' => 1);
    $this->users->update($id, $row);
To delete row in table

  
    $id  = 1;
    $this->users->delete($id);

To delete multiple rows in table

    $conditions = array('active' => 0);
    $this->users->deleteBatch($conditions);

To check value already exist of multiple column

  
    $email = 'some@mail.com';
    $this->users->checkUnique('email', $email);

If you want to check value exist except for particular id of the table, you can pass 3rd parameter as id which you want to skip while checking value like this

    $email = 'some@mail.com';
    $id    = 2;
    $this->users->checkUnique('email', $email, $id);

Now, while checking for unique value it will skip the row with this id.

**Note:** Wherever the functions checking for ID will look for column **id** of that particular table not any other column name like **user_id** or **userid** or something else...
