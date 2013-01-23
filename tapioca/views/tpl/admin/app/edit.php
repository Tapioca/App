
                <div class="pane-content">
                    <h2 class="page-name" id="app-name" style="margin-bottom: 30px">{{ pageTitle }}</h2>
                    <?= Form::open(array('class' => 'form-horizontal clear-both')); ?>
                        <fieldset id="profile-form" style="margin-bottom: 30px">
                            <legend><?= __('tapioca.ui.label.edit_app_profile'); ?></legend>
                            <div class="control-group" for="name">
                                <label class="control-label"><?= __('tapioca.ui.label.app_name'); ?></label>
                                <div class="controls">
                                    <input id="name" type="text" value="{{ name }}" class="span7">
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label" for="slug"><?= __('tapioca.ui.label.app_slug'); ?></label>
                                <div class="controls">
                                    <input id="slug" type="text" value="{{ slug }}" class="span7" {{#unless isNew}} disabled{{/unless}}>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset id="users-form" style="margin-bottom: 30px">
                            <legend><?= __('tapioca.ui.label.edit_app_user'); ?></legend>
                            <div class="control-group">
                                <label for="new_user" class="control-label"><?= __('tapioca.ui.label.add_user'); ?></label>
                                <div class="controls">
                                    <input type="text" id="new-user" data-bypass="true">
                                </div>
                            </div>
                            <table class="table table-striped {{#isNew}}hide{{/isNew}}" id="app-team">
                                <thead>
                                    <tr>
                                        <th width="60"></th>
                                        <th><?= __('tapioca.ui.label.user_name'); ?></th>
                                        <th><?= __('tapioca.ui.label.user_role'); ?></th>
                                        <th width="100"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </fieldset>
                        <fieldset id="locales-form" style="margin-bottom: 30px">
                            <legend><?= __('tapioca.ui.label.edit_app_locale'); ?></legend>
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

