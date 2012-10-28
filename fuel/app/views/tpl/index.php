
    <div id="main">
        <div id="apps-nav" class="pane nano">
            <a id="header-logo" href="<?= Uri::create('app'); ?>">
                <?= Asset::img('header-logo.png', array('alt' => 'tapioca logo')); ?>
            </a><!-- /#header-logo -->
            
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

        </div><!-- #app-container -->
    </div><!-- /#main -->
