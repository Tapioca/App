
                <div class="app-content-header">
                    <h2 class="page-name"><?= __('tapioca.ui.label.app_library'); ?></a></h2>
                    <div class="btn-group">
                        <a class="btn upload-trigger" href="javascript:;">
                            <i class="icon-plus"></i>
                            <?= __('tapioca.ui.label.add_file'); ?>
                        </a>
                    </div>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active">
                    <div id="files-list">
                        {{> library-list}}
                    </div><!-- /#files-list -->
                </div><!-- /.pane-content -->