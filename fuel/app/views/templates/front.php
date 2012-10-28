<!doctype html class="wf-loading">
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

    <?php echo Casset::render_css('app'); ?> 

    <script>
    
        // Dynamically add .app-loading to <html>
        // replace it by .app-active on-load
        (function( window, document, undefined )
        {
            docElement = document.documentElement;
            docElement.className = docElement.className + ' app-loading';
            window.onload = function()
            {
                docElement.className = docElement.className.replace(/\bapp-loading\b/, 'app-active');
            };
        })(this, this.document);
    
        // Stop paying your jQuery tax
        // http://samsaffron.com/archive/2012/02/17/stop-paying-your-jquery-tax
        window.q = [];
        window.$ = function(f)
        {
            q.push(f);
        };
    </script>
</head>
<body>

<?php echo Casset::render_js('app'); ?> 

<script>
// app config
var _config = <?= json_encode( $settings ); ?>;

// Kick start the app
$(function()
{
    $.Tapioca.bootstrap( _config )
});


<?php if(Fuel::$env == 'development'): ?>
// (function (d, t) {
//   var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
//   bh.type = 'text/javascript';
//   bh.src = '//www.bugherd.com/sidebarv2.js?apikey=4sqpdbkxx3j9orwbvqqtka';
//   s.parentNode.insertBefore(bh, s);
//   })(document, 'script');
<?php endif; ?>
</script>
</body>
</html>