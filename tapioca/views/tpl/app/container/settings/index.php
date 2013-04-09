
                <div class="pane-content">
                    <h2 class="page-name">{{ pageTitle }}</h2>
                    <ul class="nav nav-tabs clear-both">
                        <li class="active">
                            <a href="#locales-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_locale'); ?></a>
                        </li>
                        <li>
                            <a href="#medias-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.app_library'); ?></a>
                        </li>
                        <li>
                            <a href="#apikey-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_apikey'); ?></a>
                        </li>
                    </ul>
                    <?= Form::open(array('class' => 'form-horizontal tab-content')); ?>
                        <fieldset id="locales-form" class="tab-pane active">
                            <ul class="input-repeat-list">
                                {{#each locales}}
                                    {{> locale-list}}
                                {{/each}}
                            </ul>
                        </fieldset>
                        <fieldset id="medias-form" class="tab-pane">
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.ext_whitelist'); ?></label>
                                <div class="controls">
                                    <textarea rows="3" id="ext-whitelist" class="span7">{{ extWhitelist }}</textarea>
                                </div>
                            </div>
                            <hr>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.storage'); ?></label>
                                <div class="controls">
                                    <select id="storage">
                                        <option value=""{{isSelected storage.method default="" attribute="selected"}}><?= __('tapioca.ui.label.storage_locale'); ?></option>
                                        <option value="ftp"{{isSelected storage.method default="ftp" attribute="selected"}}><?= __('tapioca.ui.label.storage_ftp'); ?></option>
                                        <option value="sftp"{{isSelected storage.method default="sftp" attribute="selected"}}><?= __('tapioca.ui.label.storage_sftp'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div id="storage-data">
                                <div class="control-group" data-storage="ftp|sftp">
                                    <label class="control-label"><?= __('tapioca.ui.label.storage_host'); ?></label>
                                    <div class="controls"><input type="text" id="storage.host" value="{{storage.host}}"></div>
                                </div>
                                <div class="control-group" data-storage="ftp|sftp">
                                    <label class="control-label"><?= __('tapioca.ui.label.storage_path'); ?></label>
                                    <div class="controls"><input type="text" id="storage.path" value="{{storage.path}}" placeholder="/"></div>
                                </div>
                                <div class="control-group" data-storage="ftp|sftp">
                                    <label class="control-label"><?= __('tapioca.ui.label.storage_username'); ?></label>
                                    <div class="controls"><input type="text" id="storage.username" value="{{storage.username}}"></div>
                                </div>
                                <div class="control-group" data-storage="ftp|sftp">
                                    <label class="control-label"><?= __('tapioca.ui.label.storage_password'); ?></label>
                                    <div class="controls"><input type="text" id="storage.password" placeholder="only you know"></div>
                                </div>
                            </div>
                            <div class="control-group" id="storage-test-holder">
                                <label class="control-label">&nbsp;</label>
                                <div class="controls">
                                    <a href="javascript:;" id="storage-test" class="btn btn-mini">Test settings</a>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="apikey-form" class="tab-pane">

                        </fieldset>

                    <?= Form::close(); ?>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="app-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->
