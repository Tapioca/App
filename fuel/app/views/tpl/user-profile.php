
                <div class="pane-content">
                    <h2 class="page-name"><?= __('tapioca.ui.session.user-profile'); ?></h2>
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
                                <label class="control-label"><?= __('tapioca.ui.session.name'); ?></label>
                                <div class="controls">
                                    <input id="name" name="name" type="text" value="{{ name }}" class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.session.email'); ?></label>
                                <div class="controls">
                                    <input id="email" name="email" type="text" value="{{ email }}" class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
                                    <img src="{{ avatar }}" alt="">
                                </label>
                                <div class="controls">
                                    <a href="http://gravatar.com/"><?= __('tapioca.ui.session.invite_gravatar'); ?></a>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="password-form" class="tab-pane">
                            <div class="control-group">
                                <label for="old-pass" class="control-label"><?= __('tapioca.ui.session.old_password'); ?></label>
                                <div class="controls">
                                    <input type="password" id="old-pass">
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="new-pass" class="control-label"><?= __('tapioca.ui.session.new_password'); ?></label>
                                <div class="controls">
                                    <input type="password" id="new-pass">
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="conf-pass" class="control-label"><?= __('tapioca.ui.session.conf_password'); ?></label>
                                <div class="controls">
                                    <input type="password" id="conf-pass">
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
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="Saving stuff..."><?= __('tapioca.ui.label.submit'); ?></button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->
                <div id="dialog-confirm" class="hide">
                    <p><?= __('tapioca.ui.dialog.beforeunload'); ?></p>
                </div>
