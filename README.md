Codeigniter Model Extension
---------------------------

This package contains couple of files which are helpful when extended from models of Codeigniter. It also contain custom library files which are handy and simple to authenticate with multiple tables.

The main purpose of this files are to make queries to particular database table easier and create pagination with simple calls.

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
