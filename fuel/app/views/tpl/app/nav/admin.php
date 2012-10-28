
                    <a href="<?= Uri::create('app/admin'); ?>" class="app-nav-header">
                        <h5 class="app-nav-name">Tapioca</h5>
                    </a>
                    <div class="app-nav-lists">
                        <h6><?= __('tapioca.ui.title.admin'); ?></h6>
                        <ul>
                            <li>
                                <a href="<?= Uri::create('app/admin/app'); ?>">
                                    <?= __('tapioca.ui.label.app_settings'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= Uri::create('app/admin/user'); ?>">
                                    <?= __('tapioca.ui.label.app_users'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>