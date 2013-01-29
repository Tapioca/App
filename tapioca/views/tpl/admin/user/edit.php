
                <div class="pane-content">
                    <h2 class="page-name" style="margin-bottom: 30px"><?= __('tapioca.ui.title.edit_account'); ?></h2>
                    {{#if restricted}}
                        <p class="clear-both">
                            <?= __('tapioca.ui.label.cannot_edit_admin'); ?>
                        </p>
                    {{else}}
                    <?= Form::open(array('class' => 'form-horizontal clear-both', 'id' => 'tapioca-user-form')); ?>
                        <fieldset id="account-form" style="margin-bottom: 30px">
                            <legend><?= __('tapioca.ui.session.edit_account'); ?></legend>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.user_name'); ?></label>
                                <div class="controls">
                                    <input id="name" name="name" type="text" value="{{ name }}" class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.user_email'); ?></label>
                                <div class="controls">
                                    <input id="email" name="email" type="text" value="{{ email }}" class="span7">
                                </div>
                            </div>
                            {{#isMaster}}
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.is_tapp_admin'); ?></label>
                                <div class="controls">
                                    <input id="admin" type="checkbox" value="1" {{isSelected admin default=true attribute="checked"}}>
                                </div>
                            </div>
                            {{/isMaster}}
                        </fieldset>

                        <fieldset id="password-form" style="margin-bottom: 30px">
                            <legend><?= __('tapioca.ui.session.edit_password'); ?></legend>
                            <div class="control-group">
                                <label for="password" name="password" class="control-label"><?= __('tapioca.ui.label.new_password'); ?></label>
                                <div class="controls">
                                    <input type="text" id="password">
                                    <a href="javascript:;" id="password-generator">
                                        <?= __('tapioca.ui.label.random_password'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="conf-pass" class="control-label"><?= __('tapioca.ui.label.send_password'); ?></label>
                                <div class="controls">
                                    LINK TO SENDBACK INVITE
                                </div>
                            </div>
                        </fieldset>
                        <fieldset id="apps-form">
                            <legend><?= __('tapioca.ui.session.edit_apps'); ?></legend>
                            <ul>
                                {{#apps}}
                                <li>{{name}}</li>
                                {{/apps}}
                            </ul>
                        </fieldset>
                    <?= Form::close(); ?>
                    {{/if}}
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->
