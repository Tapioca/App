<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Login</title>

</head>
<body>
<h3>upload</h3>
///test/upload/do
<?= Form::open(array('enctype' => 'multipart/form-data', 'action' => '/api/happyend/file')); ?>

	<p><input type="file" name="tappfile[]" id="tappfile" multiple /></p>
    
    <p><label for="tags">séparez les tags par des virgules</label><input id="tags" type="text" name="tags" /></p>
    
    <p><button type="submit" id="btn" class="button">Upload Files!</button></p>

<?= Form::close(); ?>
</body>
</html>
