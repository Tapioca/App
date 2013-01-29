                <div class="app-content-header">
                    <h2 class="page-name">{{ name }}</h2>
                    <div class="dropdown btn-group" id="locale-switch">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
                            {{ locale.label }}
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            {{{localeSwitcher appslug baseUri }}}
                        </ul>
                    </div>
                    <div class="btn-group">
                        <a class="btn" href="<?= Uri::create('app/'); ?>{{ appslug }}/{{ namespace }}/new?l={{ locale.key }}">
                            <i class="icon-plus"></i>
                            <?= __('tapioca.ui.label.add_document'); ?>
                        </a>
                        {{#if isAppAdmin}}
                        <a href="#" data-toggle="dropdown" class="btn dropdown-toggle">
                            <i class="icon-cogs"></i>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?= Uri::create('app/'); ?>{{ appslug }}/{{ namespace }}/edit">
                                    <i class="icon-edit"></i>
                                    <?= __('tapioca.ui.label.collection_edit'); ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:;">
                                    <i class="icon-trash"></i>
                                    <?= __('tapioca.ui.label.collection_empty'); ?>
                                </a>
                            </li>
                        </ul>
                        {{/if}}
                    </div>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                {{#digest}}
                                <th>{{ label }}</th>
                                {{/digest}}
                                <th width="100"><?= __('tapioca.ui.label.document_status'); ?></th>
                                <th width="150"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>