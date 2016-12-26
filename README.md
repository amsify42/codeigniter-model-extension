Codeigniter Model Extension
---------------------------

The main purpose of these files are to make queries to particular database table easier and create pagination with simple calls.

For Extending model classes you need put two files 
**MY_Model.php** 
**Pagination.php** 
in **application/core/**

Whenever you create model class, extend MY_Model instead of CI_Model like this

    class Users extends MY_Model {
    
    }

Now, when you load this mode in any controller, you can use like this

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
