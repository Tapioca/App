                <div class="pane-content">
                    <?= Form::open('tapioca-document-form'); ?>
                        <div class="row-fluid">
                            <div class="span8">
                                <fieldset id="form-holder">
                                    <legend>{{ pageTitle }}</legend>
                                    <div class="dropdown btn-group{{#isNew}} hide{{/isNew}}" id="locale-switch">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
                                            {{ locale.label }}
                                            <b class="caret"></b>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            {{{localeSwitcher appslug baseUri }}}
                                        </ul>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="span4">
                                <ul id="revisions">
                                </ul><!-- /#revisions -->
                            </div><!-- /.span4 -->
                        </div>
                    <?= Form::close(); ?>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->