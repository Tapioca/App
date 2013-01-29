
                <div class="pane-content">
                    <h2 class="page-name">{{ pageTitle }}</h2>
                    <ul class="nav nav-tabs clear-both">
                        <li class="active">
                            <a href="#locales-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_locale'); ?></a>
                        </li>
                        <li>
                            <a href="#medias-form" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_app_mediatype'); ?></a>
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
