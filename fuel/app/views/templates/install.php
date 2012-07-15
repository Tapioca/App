<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">

    <title>Tapioca - Schema Driven Data Engine</title>
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
	    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
	<script src="http://code.jquery.com/jquery-1.7.min.js"></script>
    
    <!-- Le styles -->
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/css/class.css">
    <link rel="stylesheet" href="/assets/css/install.css">
    

</head>
<body>
    
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#">Tapioca</a>
        </div>
    </div>
</div><!-- /topbar -->

<div class="container">

    <div class="content">
        <div class="page-header">
	        <h1>Config <?php

            $last = end($breadcrumb);
            foreach($breadcrumb as $path)
            {
                echo '<small';
                if($last == $path)
                {
                    echo ' class="active"';
                }

                echo '>/'.$path.'</small>';
            }

            ?></h1>
        </div>
        <div class="row">
<?= $view; ?>
        </div><!-- /row -->
    </div><!-- /content -->
    
    <footer>
    <p>&copy; Strict-Edge 2012</p>
    </footer>

</div> <!-- /container -->
</body>
</html>
