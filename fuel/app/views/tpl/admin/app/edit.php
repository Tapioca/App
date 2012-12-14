
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
                            <a href="#locales-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_locale'); ?></a>
                        </li>
                    </ul>
                    <?= Form::open(array('class' => 'form-horizontal tab-content')); ?>
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

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="60"></th>
                                        <th><?= __('tapioca.ui.label.user_name'); ?></th>
                                        <th><?= __('tapioca.ui.label.user_role'); ?></th>
                                        <th width="100"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                {{#team}}
                                    <tr{{#disabled}} class="warning"{{/disabled}}>
                                        <td>
                                            <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}">
                                                <img src="{{ avatar }}" alt="" width="50">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}">
                                                {{ name }}
                                            </a>
                                        </td>
                                        <td>
                                            {! roleSelector ../slug id  ../operator}}
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {{/team}}
                                </tbody>
                            </table>
                        </fieldset>
                        <fieldset id="locales-form" class="tab-pane">
                            <ul class="input-repeat-list">
                                {{#atLeastOnce locales}}
                                    {{> locale-list}}
                                {{/atLeastOnce}}
                            </ul>
                        </fieldset>
                    <?= Form::close(); ?>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="app-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->

