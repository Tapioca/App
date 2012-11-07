
                <div class="pane-content">
                    <h2 class="page-name"><?= __('tapioca.ui.title.edit_account'); ?></h2>
                    <ul class="nav nav-tabs clear-both">
                        <li class="active">
                            <a href="#account-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.session.edit_account'); ?></a>
                        </li>
                        <li>
                            <a href="#password-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.session.edit_password'); ?></a>
                        </li>
                        <li>
                            <a href="#apps-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.session.edit_apps'); ?></a>
                        </li>
                    </ul>
                    <form class="form-horizontal tab-content" method="post" action="<?= Uri::create('api/void'); ?>" target="postFrame">
                        <fieldset id="account-form" class="tab-pane active">
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.user_name'); ?></label>
                                <div class="controls">
                                    <input id="name" type="text" value="{{ name }}" class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.user_email'); ?></label>
                                <div class="controls">
                                    <input id="email" type="text" value="{{ email }}" class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.is_tapp_admin'); ?></label>
                                <div class="controls">
                                    <input id="admin" type="checkbox" value="1" {{isSelected admin default=1 attribute="checked"}}>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset id="password-form" class="tab-pane">

                            <div class="control-group">
                                <label for="password" class="control-label"><?= __('tapioca.ui.label.new_password'); ?></label>
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
                        <fieldset id="apps-form" class="tab-pane">
                            <ul>
                                {{#apps}}
                                <li>{{name}}</li>
                                {{/apps}}
                            </ul>
                        </fieldset>
                    </form>
                    <iframe name="postFrame" class="hide"></iframe>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->
