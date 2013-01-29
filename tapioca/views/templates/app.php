<?php
$uri_base	= Uri::base(false);
?><!doctype html>
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
	<?= Casset::render_css('app'); ?> 


</head>
<body>

	<div id="main">
		<div id="apps-nav" class="pane nano">
			<a id="header-logo" href="<?= Uri::create('app'); ?>">
				<img alt="tapioca logo" src="<?= $uri_base;?>assets/img/header-logo.png">
			</a><!-- /#header-logo -->

			<div id="user-shortcuts">
				<a href="#" class="avatar">
					<img src="<?= Gravy::from_email(Tapioca::user()->get('email'), 37, 'g', null, true); ?>" alt="" height="37" width="37">
				</a>
				<h5>Hello <strong><?= Tapioca::user()->get('name'); ?></strong></h5>
				<nav>

					<?= Html::anchor(Uri::create('#'), __('tapioca.ui.user_account')); ?>

					<?= Html::anchor(Uri::create('log/out'), __('tapioca.ui.user_logout')); ?>

				</nav>
			</div><!-- /#user-shortcuts -->
<?php
	
	$nbGroups = count($app_settings['user']['groups']);

	echo '			<div class="pane-content';
	if($nbGroups == 1)
		echo ' pane-content-one-app';
	echo '">';

	foreach($app_settings['user']['groups'] as $group)
	{
?>
				<div class="app-nav <?php
					if($nbGroups == 1)
					{
						echo ' app-nav-active';
					}
				?>">
					<a href="javascript:void(0)" class="app-nav-header" data-app-id="<?= $group['id']; ?>" data-app-slug="<?= $group['slug']; ?>">
						<!--span class="avatar">
							<img src="/dynamic/apps/dior-logo.jpg" alt="" />
						</span-->
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
							<li class="app-nav-collections-empty">
								<span class="no-collection">Pas de collections</span>
							</li>
<?php
	if($group['is_admin'])
	{
?>
							<li class="divider"></li>
							<li>
								<a href="app/<?= $group['slug']; ?>/collections/new" class="admin-action">
									<i class="icon-plus"></i>
									Ajouter une collection
								</a>
							</li>
<?php
	} // if is_admin
?>
						</ul>

						<h6>Fichiers</h6>
						<ul id="app-nav-files-<?= $group['slug']; ?>">
							<li data-namespace="library">
								<a href="app/<?= $group['slug']; ?>/file">Library</a>
							</li>
							<li class="divider"></li>
							<li>
								<a href="app/<?= $group['slug']; ?>/file/upload" class="admin-action">
									<i class="icon-plus"></i>
									Ajouter un fichier
								</a>
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
				<form action="#" id="search-form">
					<p>
						<input type="text" id="search-query" class="disabled" disabled="disabled">
					</p>
				</form><!-- /#search-form -->
			</div><!-- /#app-subnav -->
			<!-- <div id="app-content" class="pane nano">

			</div>#app-content -->

		</div><!-- #app-container -->
	</div><!-- /#main -->
	<div id="ref-popin" class="wtwui-dialog wtwui-element fade overlay" data-content="">
		<div class="close">×</div>
		<div id="ref-popin-content" class="pane nano"><div class="pane-content"></div></div>
	</div>

	<!-- Application source -->
	<script data-main="<?= $uri_base;?>tapioca/bootstrap" src="<?= $uri_base;?>assets/library/require/require.js"></script>
	<script>
	define('config', function()
	{
		return <?= json_encode($app_settings); ?>;
	});
	</script>
</body>
</html>
