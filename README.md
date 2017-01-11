Codeigniter Model Extension
---------------------------

The main purpose of these files is to make queries to database tables easier and create pagination with simple calls.

For extending model classes you need to put two files 

 1. **MY_Model.php**
 2. **Pagination.php** 
    
in **application/core/**

Whenever you create model class, extend MY_Model instead of CI_Model like this

```php
    class Users extends MY_Model {
    
    }
```

Now, when you load this model in any controller, you can use like this

```php
    class Base extends CI_Controller {
          function __construct() {
    		parent::__construct();	
    		$this->load->model('users');
    	}

		public function home() {
		 $data['users'] = $this->users->getAll();
		}
    }
```

As you can see, we are calling getAll() method which will get all the rows from database table **users**.

If your database table name is different than model class name then you can add table name in protected property of your model class like this

```php		
     class Users extends MY_Model {
    	 protected $table  = 'app_users';    
      }
```

Now, all methods called using this model will load data from table **app_users** instead of **users**

Just like we are using **getAll()** method to get all rows from table users, you can also use below methods

### Get

To get count of all rows

```php
    $this->users->count_all();
```

To get the rows with conditions passed to it

```php
    $conditions = array('active' => 1);
    $this->users->getWhere($conditions);
```

Get single row with particular ID or Conditions

```php
    $this->users->find(1);
    // or
    $conditions = array('id' => 1, 'active' => 1);
    $this->users->find($conditions);
```

Alternatively, you can pass 2nd parameter as column name if you don't want to check with column **id**

```php
    $this->users->find(1, 'userid');
```

For getting maximum, minimum, average and sum of values, you can use these below methods. The only parameter used here is the name of table column name and this calls will directly get the result value rather than array or object.

```php
    $this->users->max('points');
    $this->users->min('points');
    $this->users->avg('points');
    $this->users->sum('points');
```

### Insert/Update

To insert single row in table

```php
    $row = array('name' => 'some name', 'active' => 1);
    $this->users->insert($row);
```

To insert multiple rows in table

```php
    $rows = array(
          array('name' => 'some name', 'active' => 1),
          array('name' => 'some name 2', 'active' => 0)
          );
    $this->users->insertBatch($rows);
```

To update single row in table

```php  
    $id  = 1;
    $row = array('name' => 'some name', 'active' => 1);
    $this->users->update($id, $row);
```
If the value your passing for id is not of column name **id** then you can pass 3rd parameter as column name
```php  
    $this->users->update($id, $row, 'userid');
```

### Delete

To delete row in table

```php  
    $id  = 1;
    $this->users->delete($id);
```
If the value your passing for id is not of column name **id** then you can pass 2nd parameter as column name
```php  
    $this->users->delete($id, 'userid');
```

To delete multiple rows in table by conditions

```php
    $conditions = array('active' => 0);
    $this->users->deleteBatch($conditions);
```
If primary key of the table is not **id** then you can pass 2nd parameter as column name because during the execution, function will delete the rows by primary keys
```php
    $this->users->deleteBatch($conditions, 'userid');
```

To delete multiple rows by their IDs

```php
    $IDs = array(1, 2, 3);
    $this->users->deleteIDs($IDs);
```
If primary key of the table is not **id** then you can pass 2nd parameter as column name.
```php
    $this->users->deleteIDs($IDs, 'userid');
```

To truncate the table

```php
    $this->users->truncate();
```

### Checking

To check value already exist of particular column

```php  
    $email = 'some@mail.com';
    $this->users->checkUnique('email', $email);
```
If you want to check value exist except for particular id of the table, you can pass 3rd parameter as id which you want to skip while checking value like this
```php
    $email = 'some@mail.com';
    $id    = 2;
    $this->users->checkUnique('email', $email, $id);
```
If primary key of the table is not **id** then you can pass 4th parameter as column name.
```php
    $this->users->checkUnique('email', $email, $id, 'userid');
```
Now, while checking for unique value it will skip the row with this id.


## Pagination

If you want to get paginated result of this same table with limit 10, you can use function like this

```php
    $this->users->paginate(10);
```

If you want to get paginated result with some conditions, you can do by couple of ways

1) You can use codeigniter predefined function along with pagination method something like this

```php
    $this->users->where('active', 1)->order_by('id', 'DESC')->paginate(10);
```

2) You can pass callback function as a parameter with conditions in it. Even here you can use predefined functions to extend the query.  Make sure you use parameter as query and return it.

```php
	$conditions = function($query) {
		return $query->where('active', 1)->order_by('id', 'DESC');
	}
    $this->users->conditions($conditions)->paginate(10);
```

3) For changing the pagination config you can pass config as 3rd parameter to the method paginate. 2nd parameter is page no. to use while getting pagination through Ajax but for now you can pass it as null value.

```php
    $config['uri_segment'] = 3;
    $config['base_url']    = 'index.php/users';

    $users   = $this->users->paginate(10, '', $config);
    $links   = $this->users->getLinks();
```
**getLinks()** method will get pagination links with some default settings. Pass custom config settings to get the desired results for both pagination result and links. You can also check the constructor method of **Pagination.php** to see what default settings it is using.

**Note:** For exisitng and custom config of pagination, do not forget to create respective routes in routes.php


## Using Pre defined methods

You can use all the pre defined db methods of codeigniter as a chain as well to get the result. Below are the examples

```php
 $this->users->order_by('id', 'DESC')->get()->result();
 $this->users->order_by('id', 'DESC')->get()->result_array();
 $this->users->order_by('id', 'DESC')->get()->row();
 $this->users->where('id', 1)->get()->row();
```

You can also skip get() method and fetch the results. As get() method will automatically be called based on functions.

```php
 $this->users->order_by('id', 'DESC')->result();
 $this->users->order_by('id', 'DESC')->result_array();
 $this->users->order_by('id', 'DESC')->row();
 $this->users->where('id', 1)->row();
```


## Hidden column values

We can make the results set to hide the column values from rows. For example, If we have a table **users** and we don't want to allow **password** and **token** to be available whenever user information is fetched. We can define protected array variable with name **hidden** in model class like this

```php		
     class Users extends MY_Model {
    	 protected $hidden = array('password', 'token');   
      }
```

**Note:** You can achieve this only by calling methods from the model object not by using **$this->db** separately somehwere

## Fillable columns

This option will not let any query to add any column data we pass for saving or updating column values of table. Let's say, We want users to allow only **email**, **name** and **password** to be inserted or updated and rest of the column values must be ignored even if they pass it. We can define protected array variable with name **fillable** in model class like this

```php		
     class Users extends MY_Model {
    	 protected $fillable = array('email', 'name', 'password');   
      }
```

**Note:** You can achieve this only by calling methods from the model object not by using **$this->db** separately somehwere

## Relations

For getting rows or single column value from other database tables based on relations, you can define one protected function in your model class with name **setRelations**

```php		
     class Users extends MY_Model {
    	 protected function setRelations() {}
      }
```

There are four ways, we can attach data to our results

### 1. Attaching multiple rows

Let's say, we have **books** table which is having foriegn key **user_id** of **users** table. Now for getting number of books belongs to users along with users result data, we can add the relation like this

```php
	protected function setRelations() {
		$this->addRelation(array(
			'primary'   => 'id',      
			'table'     => 'books', 
			'foreign'   => 'user_id',
			'variable'  => 'books',
		));
	}
```

### These are the options we can pass
#### a) primary 	- Name of the primary key of table. If not passed, it will take **id** as default
#### b) table   	- Name of the table from which rows needs to fetched.
#### c) model   	- Instead of table you can put **model** name which is created the way it is mentioned above.
#### d) variable   	- Name of the variable that needs to be added in each row of result set. If not passed, it will take foreign table name(if set) in lower case.

The result of setting the relation mentioned above will the attach the n number of books to users result set.

```php
	foreach($users as $user) {
	    foreach($users->books as $book){
	   }
	}
```
As you can see, one more array is attached with name **books** to every row.

### 2. Attaching single row
For, attaching directly a single row to key, you can pass **single** as true like this

```php
	protected function setRelations() {
		$this->addRelation(array(
			'primary'   => 'id',      
			'table'     => 'images', 
			'foreign'   => 'user_id',
			'variable'  => 'image',
			'single'    => true
		));
	}
```

#### e) single 	- If passed as true, single row will be attached

The result of setting the relation mentioned above will the attach single image row to users result set.

```php
	foreach($users as $user) {
	   	$user->image->id;
		$user->image->name;
	}
```

### 3. Attaching single column value

For getting only particular column value from table, you can pass one more option as **column**
```php
	protected function setRelations() {
		$this->addRelation(array(
			'primary'   => 'id',      
			'table'     => 'images', 
			'foreign'   => 'user_id',
			'variable'  => 'image',
			'column'    => 'name'	
		));
	}
```
Result will simply get the **name** column value from table images to key **image** in every row of users.

```php
	foreach($users as $user) {
	   	$user->image;
	}
```

**Note:** When passed column value, single is not necessary

### 4. Attaching single column value with some modification

For making some modification iwth column value, you can same **column** option as array with **two keys** like this
```php
	protected function setRelations() {
		$this->addRelation(array(
			'primary'   => 'id',      
			'table'     => 'images', 
			'foreign'   => 'user_id',
			'variable'  => 'image',
			'column'    => array(
			      'name'    => 'name',
			      'modify'  => base_url('images/').'_COL_'
			   ),	
		));
	}
```
As you can see, we are passing two key **name** and **modify**. modify having some value along with text **_COL_**. Whatever the value come from table column name. It will contain the extra value we passing along with **_COL_**

Lets say, if image name is **flower.png** , value will be http://yoursite.com/images/flower.png

```php
	foreach($users as $user) {
	   	$user->image; // http://yoursite.com/images/flower.png
	}
```
