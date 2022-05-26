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
