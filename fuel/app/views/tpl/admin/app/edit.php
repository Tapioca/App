
                <div class="pane-content">
                    <h2 class="page-name">{{ pageTitle }}</h2>
                    <ul class="nav nav-tabs clear-both">
                        <li class="active">
                            <a href="#profile-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_profile'); ?></a>
                        </li>
                        <li>
                            <a href="#users-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_user'); ?></a>
                        </li>
                        <li>
                            <a href="#admins-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_admin'); ?></a>
                        </li>
                        <li>
                            <a href="#locales-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_locale'); ?></a>
                        </li>
                    </ul>
                    <form class="form-horizontal tab-content" method="post" action="<?= Uri::create('api/void'); ?>" target="postFrame">
                        <fieldset id="profile-form" class="tab-pane active">
                            <div class="control-group">
                                <label class="control-label" for="slug"><?= __('tapioca.ui.label.app_slug'); ?></label>
                                <div class="controls">
                                    <input id="slug" type="text" value="{{ slug }}" class="span7" {{#unless isNew}} disabled{{/unless}}>
                                </div>
                            </div>
                            <div class="control-group" for="name">
                                <label class="control-label"><?= __('tapioca.ui.label.app_name'); ?></label>
                                <div class="controls">
                                    <input id="name" type="text" value="{{ name }}" class="span7">
                                </div>
                            </div>

                        </fieldset>

                        <fieldset id="users-form" class="tab-pane">

                            <div class="control-group">
                                <label for="new_user" class="control-label"><?= __('tapioca.ui.label.add_user'); ?></label>
                                <div class="controls">
                                    <input type="text" id="new-user">
                                </div>
                            </div>
                            <ul>
                                {{#team}}
                                <li>{{ name }} / {{ level }}</li>
                                {{/team}}
                            </ul>
                        </fieldset>
                        <fieldset id="admins-form" class="tab-pane">
                            <ul>
                                {{#each admins}}
                                <li>{{ name }}</li>
                                {{/each}}
                            </ul>
                        </fieldset>
                        <fieldset id="locales-form" class="tab-pane">
                            <ul>
                                {{#each locales}}
                                <li>{{ label }} {{ key }} {{#default}}default{{/default}}</li>
                                {{/each}}
                            </ul>
                        </fieldset>
                    </form>
                    <iframe name="postFrame" class="hide"></iframe>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="app-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->
                <div id="dialog-confirm" class="hide">
                    <p><?= __('tapioca.ui.dialog.beforeunload'); ?></p>
                </div>
