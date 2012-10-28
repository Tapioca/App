                <div class="app-content-header">
                    <h2 class="page-name"><?= __('tapioca.ui.title.admin_app'); ?></h2>

                    <div class="btn-group">
                        <a class="btn" href="<?= Uri::create('app/admin/app/new'); ?>">
                            <i class="icon-plus"></i>
                            <?= __('tapioca.ui.label.add_app'); ?>
                        </a>
                    </div>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="60"></th>
                                <th><?= __('tapioca.ui.label.app_name'); ?></th>
                                <th width="100"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="dialog-confirm" class="hide">
                        <p id="dialog-confirm-question"></p>
                    </div>
                </div>