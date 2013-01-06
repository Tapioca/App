
                <div class="pane-content">
                    <h2 class="page-name">{{ pageTitle }}</h2>
                    <ul class="nav nav-tabs clear-both">
                        <li class="active">
                            <a href="#collection-desc" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_desc'); ?></a>
                        </li>
                        <li>
                            <a href="#collection-schema" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_schema'); ?></a>
                        </li>
                        <li>
                            <a href="#collection-digest" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_digest'); ?></a>
                        </li>
                        <li>
                            <a href="#collection-hooks" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_hooks'); ?></a>
                        </li>
                        <li>
                            <a href="#collection-preview" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_preview'); ?></a>
                        </li>
                    </ul>
                    <?= Form::open(array('class' => 'form-horizontal tab-content')); ?>
                        <fieldset id="collection-desc" class="tab-pane active">
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.col_namespace'); ?></label>
                                <div class="controls">
                                    <input id="namespace" ype="text" value="{{ namespace }}" class="span7" {{#unless isNew}} disabled{{/unless}}>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.col_name'); ?></label>
                                <div class="controls">
                                    <input type="text" id="name" value="{{ name }}" required class="span7">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.col_desc'); ?></label>
                                <div class="controls">
                                    <textarea rows="3" id="desc" name="desc" class="span7">{{ desc }}</textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.col_status'); ?></label>
                                <div class="controls">
                                    <select id="status">
                                        <option value="draft"{{{isSelected status default="draft" attribute=" selected"}}>
                                            <?= __('tapioca.ui.label.col_status_draft'); ?>
                                        </option>
                                        <option value="public"{{{isSelected status default="public" attribute=" selected"}}>
                                            <?= __('tapioca.ui.label.col_status_public'); ?>
                                        </option>
                                        <option value="private"{{{isSelected status default="private" attribute=" selected"}}>
                                            <?= __('tapioca.ui.label.col_status_private'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </fieldset>
                        <fieldset id="collection-schema" class="tab-pane">
                            <div class="controls">
                                <textarea id="schema" name="schema" rows="15" class="span10 lined">{{ schema }}</textarea>
                            </div>
                        </fieldset>
                        <fieldset id="collection-digest" class="tab-pane">
                            <div class="controls">
                                <textarea id="digest" name="digest" rows="15" class="span10 _lined">{{ digest.fields }}</textarea>
                                <p class="help-block">
                                    <label>
                                        <input type="checkbox" id="digest-edit" value="1" {{isSelected digest.edited default=1 attribute="checked"}}>
                                        Editer manullement le résumé
                                    </label>
                                </p>
                            </div>
                        </fieldset>
                        <fieldset id="collection-hooks" class="tab-pane">
                            <div class="controls">
                                <textarea id="hooks" name="hooks" rows="15" class="span10 _lined">{{ hooks }}</textarea>
                            </div>
                        </fieldset>
                        <fieldset id="collection-preview" class="tab-pane">
                            <div class="control-group">
                                <label class="control-label"><?= __('tapioca.ui.label.col_preview'); ?></label>
                                <div class="controls">
                                    <ul class="input-repeat-list">
                                        {{#atLeastOnce preview}}
                                            {{> preview-edit}}
                                        {{/atLeastOnce}}
                                    </ul>
                                </div>
                            </div>
                        </fieldset>
                    <?= Form::close(); ?>
                </div><!-- /.pane-content -->
                <div class="form-actions form-footer">
                    <button type="submit" id="profile-form-save" class="btn btn-primary disabled" disabled="disabled" data-loading-text="<?= __('tapioca.ui.label.saving'); ?>">
                        <?= __('tapioca.ui.label.submit'); ?>
                    </button>
                    <button type="reset" class="btn"><?= __('tapioca.ui.label.cancel'); ?></button>
                </div><!-- /.form-actions -->

