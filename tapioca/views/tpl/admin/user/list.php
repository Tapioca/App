                <div class="app-content-header">
                    <h2 class="page-name"><?= __('tapioca.ui.title.admin_user'); ?></h2>

                    <div class="btn-group">
                        <a class="btn" href="<?= Uri::create('app/admin/user/new'); ?>">
                            <i class="icon-plus"></i>
                            <?= __('tapioca.ui.label.add_user'); ?>
                        </a>
                    </div>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="60"></th>
                                <th><?= __('tapioca.ui.label.user_name'); ?></th>
                                <th><?= __('tapioca.ui.label.user_email'); ?></th>
                                <th width="150"><?= __('tapioca.ui.label.user_status'); ?></th>
                                <th width="100"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>