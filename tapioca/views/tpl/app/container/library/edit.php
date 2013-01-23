                <div class="pane-content">
                    <?= Form::open('tapioca-file-form'); ?>
                        <h3 id="filename">{{ file.filename }}</h3>
                        <div class="btn-group">
                            <a class="btn upload-trigger" href="javascript:;">
                                <i class="icon-plus"></i>
                                <?= __('tapioca.ui.label.update_file'); ?>
                            </a>
                        </div>
                        <div class="row-fluid">
                            {{#preview}}
                            <div class="span2">
                                <a href="{{ original }}" data-bypass="true" target="_blank">
                                    <img src="{{ thumb }}" alt="">
                                </a>
                            </div>
                            {{/preview}}
                            <div class="span10">
                                {{#file}}
                                <dl>
                                    <dt>basename</dt>
                                    <dd>
                                        <input type="text" id="basename" value="{{basename}}">
                                    </dd>

                                    <dt>category</dt>
                                    <dd>{{category}}</dd>

                                    <dt>mimetype</dt>
                                    <dd>{{mimetype}}</dd>

                                    <dt>length</dt>
                                    <dd>{{fileSize length}}</dd>

                                    {{#size}}
                                    <dt>size</dt>
                                    <dd>{{imageSize this}}</dd>
                                    {{/size}}

                                    <dt>presets</dt>
                                    <dd>
                                        <ul>
                                            {{#presets}}
                                            <li>{{ this }}</li>
                                            {{/presets}}
                                        </ul>
                                    </dd>

                                    <dt>tags</dt>
                                    <dd>
                                        <ul class="input-repeat-list">
                                            {{#atLeastOnce tags}}
                                                {{> tag-edit}}
                                            {{/atLeastOnce}}
                                        </ul>
                                    </dd>
                                </dl>
                                {{/file}}
                            </div>
                        </div>
                    <?= Form::close(); ?>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->