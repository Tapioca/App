<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Login</title>

</head>
<body>
<?php
$errors = $validation->error();

foreach($errors as $key => $error)
{
	echo $key.' = '.$error.'<br />';
}

echo $auth_error;
?>
<?= Form::open('log'); ?>
	<fieldset>
		<legend>Login</legend>
		<p>
			<label>Email</label>
			<?= Form::input('email', $email, array('type' => 'email')); ?>
			<?= $validation->errors('email'); ?>
		</p>
		<p>
			<label>Password</label>
			<?= Form::password('password'); ?>
			<?= $validation->errors('password'); ?>
		</p>
		<p>
			<label>
				<?= Form::checkbox('remember', '1'); ?>
				Remember me ?
			</label>
		</p>
	</fieldset>
	<p>
		<button type="submit">Submit</button>
	</p>
<?= Form::close(); ?>
</body>
</html>
