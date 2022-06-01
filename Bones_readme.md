## Bones
Bones is based on a concept written by Dan Horrigan called Sammy. Bones was then adapted - by Tim Juravich - to create a simple PHP and CouchDB application, covered in the book below :

"CouchDB and PHP Web Development Beginner's Guide", ed. Packt Publishing

https://www.packtpub.com/product/web-development/9781849513586

Bones is a quick attempt at a PHP sinatra-ish environment. 

## Github 
The original repo of Bones is : 
https://github.com/timjuravich/bones

## Implementation
The implementation of Bones in DBTulbox2 is not exactly the same of the implementation of Tim Juravich, because I added some new features :
 * grouping functions GET, POST, PUT, DELETE into an abstract class "microFmw" (as static methods)
 * addition of an interface on the Bones class to improve the robustness of the code
 * injection of a database connector
 * injection of Javascript code (optional) which will be injected in the DOMCONTENTLOADED event of the layout page
 * injection of optional parameters (like a list of servers for example)
 
## Example
Bones is a simple lib that you can add to a php file that will allow the following
```PHP
	<?php
	include 'lib/bones.php';

	microFmw::get('/', function($app) {
	    $app->render('home');
	});

	microFmw::get('/hello/:name', function($app) {
		$app->set('name', $app->request("name"));
	    $app->render('hello');
	});

	microFmw::post('/hello', function($app) {
		$app->set('name', $app->form('name'));
	    $app->render('hello');
	});
```
Example of form :
```PHP
Home Page <br /><br />

<form action="<?php echo $this->make_route('myform'); ?>" method="post">
	<label for="name">Name</label>
	<input id="name" name="name" type="text" value="<?php echo $this->form('name'); ?>">
	<label for="age">Age</label>
	<input id="age" name="age" type="text" value="<?php echo $this->form('age'); ?>">
	<input type="Submit" value="Submit">
</form>

<?php 
if ($this->get_method() == 'POST') {
	echo $this->form('name');
}
?>
```

