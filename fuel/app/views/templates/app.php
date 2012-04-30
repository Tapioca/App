<!doctype html>
<!--[if lt IE 7]> <html class="ie ie6 oldie" lang="fr"> <![endif]-->
<!--[if IE 7]>    <html class="ie ie7 oldie" lang="fr"> <![endif]-->
<!--[if IE 8]>    <html class="ie ie8 oldie" lang="fr"> <![endif]-->
<!--[if IE 9]>    <html class="ie ie9" lang="fr"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="fr"> <!--<![endif]-->
<head>


	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>Tapioca - Schema Driven Data Engine</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<link rel="stylesheet" href="/assets/css/styles.css">


</head>
<body>


	<header id="tapp">

		<a id="header-logo" href="javascript:void(0)">
			<img alt="tapioca logo" src="/assets/img/header-logo.png">
		</a><!-- /#header-logo -->

		<div id="user-shortcuts">
			<a href="#" class="avatar">
				<img src="<?= Gravy::from_email(Auth::user()->get('email'), 37, 'g', null, true); ?>" alt="" height="37" width="37">
			</a>
			<h5>Hello <strong><?= Auth::user()->get('name'); ?></strong></h5>
			<nav>

				<?= Html::anchor(Uri::create('#'), __('tapioca.ui.user_account')); ?>

				<?= Html::anchor(Uri::create('/log/out'), __('tapioca.ui.user_logout')); ?>

			</nav>
		</div><!-- /#user-shortcuts -->
	</header><!-- /#tapp -->


	<div id="main">

		<div id="apps-nav" class="pane nano">
			<div class="pane-content">
<?php
	
	foreach($app_settings['user']['groups'] as $group)
	{
?>
				<div class="app-nav">
					<a href="javascript:void(0)" class="app-nav-header" data-app-id="<?= $group['id']; ?>" data-app-slug="<?= $group['slug']; ?>">
						<span class="avatar">
							<img src="/dynamic/apps/dior-logo.jpg" alt="" />
						</span>
						<h5 class="app-nav-name"><?= $group['name']; ?></h5>
					</a><!-- /.app-nav-header -->
					<div class="app-nav-lists">
<?php
	if($group['is_admin'])
	{
?>
						<h6>Admin</h6>
						<ul>
							<li>
								<a href="#">application</a>
							</li>
							<li>
								<a href="#">utilisateurs</a>
							</li>
						</ul>
<?php
	} // if is_admin
?>
						<h6>Documents</h6>
						<ul id="app-nav-collections-<?= $group['slug']; ?>">
							<li>
								<span class="no-collection">Pas de collections</span>
							</li>
						</ul>

						<h6>Fichiers</h6>
						<ul id="app-nav-files-<?= $group['slug']; ?>">
							<li>
								<a href="#">Images</a>
							</li>
							<li>
								<a href="#">Videos</a>
							</li>
							<li>
								<a href="#">Autres</a>
							</li>
						</ul>
					</div><!-- /.app-nav-lists -->
				</div><!-- /.app-nav -->
<?php
	} // foreach $groups
?>
			</div><!-- /.pane-content -->
		</div><!-- /#apps-nav -->

		<div id="app-container" class="pane">

			<div id="app-subnav">
				<ul id="breadcrumb">
				</ul><!-- /#breadcrumb -->
			</div><!-- /#app-subnav -->
			<div id="app-content" class="pane nano">

			</div><!-- #app-content -->

		</div><!-- #app-container -->
	</div><!-- /#main -->

	<!-- Application source -->
	<script data-main="/tapioca/bootstrap" src="/assets/library/require/require.js"></script>
	<script>
	define('config', function()
	{
		return <?= json_encode($app_settings); ?>;
	});
	</script>
</body>
</html>
