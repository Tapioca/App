

                <a href="<?= Uri::create('app/account'); ?>" class="avatar">
                    <img src="{{ avatar }}" alt="" height="37" width="37">
                </a>
                <h5>Hello <strong>{{ name }}</strong></h5>
                <nav>

                    <?= Html::anchor(Uri::create('app/account'), __('tapioca.ui.user_account')); ?>

                    <?= Html::anchor(Uri::create('app/logout'), __('tapioca.ui.user_logout')); ?>

                </nav>
