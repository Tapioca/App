                <div class="app-content-header">
                    <h2 class="page-name"><?= __('tapioca.ui.label.app_library'); ?></h2>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active" style="padding-top: 70px">

                        <div class="row-fluid">
                            <div class="span2" id="library-filters">
                                <h4><?= __('tapioca.ui.label.categories'); ?></h4>
                                <ul id="category-list">
                                    <li class="active" data-category="all"><?= __('tapioca.ui.label.library_all_files'); ?></li>
                                    <li data-category="image"><?= __('tapioca.ui.label.library_image'); ?></li>
                                    <li data-category="video"><?= __('tapioca.ui.label.library_video'); ?></li>
                                    <li data-category="document"><?= __('tapioca.ui.label.library_document'); ?></li>
                                    <li data-category="other"><?= __('tapioca.ui.label.library_other'); ?></li>
                                </ul>
                                <h4><?= __('tapioca.ui.label.tags'); ?></h4>
                                <ul id="tags-list">
                                    <li data-tag="all" class="active"><?= __('tapioca.ui.label.all_tags'); ?></li>
                                </ul>
                            </div>
                            <div class="span10">
                                <table class="table table-striped" id="table-file-list">
                                    <thead>
                                        <tr>
                                            <th><?= __('tapioca.ui.label.filename'); ?></th>
                                            <th><?= __('tapioca.ui.label.category'); ?></th>
                                            <th width="100"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="file-collections-empty">
                                            <td colspan="3"><?= __('tapioca.ui.label.no_file'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                </div>