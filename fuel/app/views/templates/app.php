<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<title>Tapioca</title>

	<!-- Application styles
	<link rel="stylesheet" href="/assets/css/index.css">
	-->
</head>

<body>

	<!-- Main container -->
	<div role="main" id="main">
		<div id="apps-nav" class="pane nano">
			<div class="pane-content">

			</div>
		</div>
	</div>

	<!-- Application source -->
	<script data-main="/tapioca_test/config" src="/assets/library/require/require.js"></script>
	<script>
		var tapp_settings = <?= json_encode($user); ?>;
	</script>
</body>
</html>
