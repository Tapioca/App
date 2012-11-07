
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
                            <a href="#collection-callback" data-toggle="tab" data-bypass="true"><?= __('tapioca.ui.label.edit_callback'); ?></a>
                        </li>
                    </ul>
                    <form class="form-horizontal tab-content" method="post" action="<?= Uri::create('api/void'); ?>" target="postFrame">
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
                                    <select>
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
  <ul id="form-elements">
    <!-- TEXT -->
    <li data-type="text">
      <span class="element-name">text</span>
      <span class="ov-h dp-b">
        <span class="remove">x</span>
        <span class="element-required">
          <input type="text" name="label" placeholder="label">
          <span class="input-id-wrapper">[<input type="text" name="id" placeholder="id">]</span>
          <span class="options-trigger"></span>
        </span>
      </span>
      <div class="element-options">
        <span class="tto">options</span>
        <ul class="options-attributes" data-type="options">
          <li>
            <label>
              <input type="checkbox" name="summary"> summary
            </label>
          </li>
          <li>
            <label>
              <input type="text" name="placeholder" placeholder="placeholder">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="repeat"> repeat
            </label>
          </li>
          <li>
            <label>
              template
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="slugify"> slugify
            </label>
            <input type="text" name="slugify-reg" placeholder="/[^-a-zA-Z0-9_\s]+/ig">
          </li>
        </ul>

        <span class="tto">rules</span>
        <ul class="options-attributes" data-type="rules">
          <li>
            <label>
              <input type="checkbox" name="rule-required"> required
            </label>
          </li>
          <li class="cr-l">
            <label>
              <input type="checkbox" name="rule-matches"> matches
            </label>
            <input type="text" name="rule-matches-input">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-valid_email"> valid_email
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-min_length"> min_length
            </label>
            <input type="text" name="rule-min_length-input" size="3">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha"> alpha
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-max_length"> max_length
            </label>
            <input type="text" name="rule-max_length-input" size="3">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha_numeric"> alpha_numeric
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-exact_length"> exact_length
            </label>
            <input type="text" name="rule-exact_length-input" size="3">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha_dash"> alpha_dash
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-greater_than"> greater_than
            </label>
            <input type="text" name="rule-greater_than-input" size="3">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-numeric"> numeric
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-less_than"> less_than
            </label>
            <input type="text" name="rule-less_than-input" size="3">
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-integer"> integer
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-decimal"> decimal
            </label>
          </li>
        </ul>
      </div>
    </li>
    <!-- TEXTAREA -->
    <li data-type="textarea">
      <span class="element-name">textarea</span>
      <span class="ov-h dp-b">
        <span class="remove">x</span>
        <span class="element-required">
          <input type="text" name="label" placeholder="label">
          <span class="input-id-wrapper">[<input type="text" name="id" placeholder="id">]</span>
          <span class="options-trigger"></span>
        </span>
      </span>
      <div class="element-options">
        <span class="tto">options</span>
        <ul class="options-attributes" data-type="options">
          <li>
            <label>
              <input type="checkbox" name="summary"> summary
            </label>
          </li>
          <li>
            <label>
              <input type="text" name="placeholder" placeholder="placeholder">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="repeat"> repeat
            </label>
          </li>
          <li>
            <label>
              template
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="wysiwyg"> wysiwyg
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="code"> code
            </label>
          </li>
        </ul>

        <span class="tto">rules</span>
        <ul class="options-attributes" data-type="rules">
          <li>
            <label>
              <input type="checkbox" name="rule-required"> required
            </label>
          </li>
          <li class="cr-l">
            <label>
              <input type="checkbox" name="rule-matches"> matches
              <input type="text" name="rule-matches-input">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-valid_email"> valid_email
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-min_length"> min_length
              <input type="text" name="rule-min_length-input" size="3">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha"> alpha
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-max_length"> max_length
              <input type="text" name="rule-max_length-input" size="3">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha_numeric"> alpha_numeric
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-exact_length"> exact_length
              <input type="text" name="rule-exact_length-input" size="3">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-alpha_dash"> alpha_dash
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-greater_than"> greater_than
              <input type="text" name="rule-greater_than-input" size="3">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-numeric"> numeric
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-less_than"> less_than
              <input type="text" name="rule-less_than-input" size="3">
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-integer"> integer
            </label>
          </li>
          <li>
            <label>
              <input type="checkbox" name="rule-decimal"> decimal
            </label>
          </li>
        </ul>
      </div>
    </li>
    <!-- SELECT -->
    <li data-type="select">
      <span class="element-name">select</span>
      <span class="element-options">options</span>
    </li>
    <!-- ARRAY -->
    <li data-type="array" data-node="true">
      <span class="element-name">array</span>
      <span class="ov-h dp-b">
        <span class="remove">x</span>
        <span class="element-required">
          <input type="text" name="label" placeholder="label">
          <span class="input-id-wrapper">[<input type="text" name="id" placeholder="id">]</span>
          <span class="options-trigger"></span>
        </span>
      </span>
      <div class="element-options">
        <span class="tto">options</span>
        <ul class="options-attributes" data-type="options">
          <li>
            <label>
              template
            </label>
          </li>
        </ul>
      </div>
      <ul class="sortable"></ul>
    </li>
    <!-- OBJECT -->
    <li data-type="object" data-node="true">
      <span class="element-name">object</span>
      <span class="ov-h dp-b">
        <span class="remove">x</span>
        <span class="element-required">
          <input type="text" name="label" placeholder="label">
          <span class="input-id-wrapper">[<input type="text" name="id" placeholder="id">]</span>
          <span class="options-trigger"></span>
        </span>
      </span>
      <div class="element-options">
        <span class="tto">options</span>
        <ul class="options-attributes" data-type="options">
          <li>
            <label>
              template
            </label>
          </li>
        </ul>
      </div>
      <ul class="sortable"></ul>
    </li>
  </ul>

  <div id="form">
    <ul class="sortable empty"></ul>
  </div>

  <hr>
  <textarea cols="100" rows="10" id="schema-holder"></textarea>
  <textarea cols="100" rows="10" id="summary-holder"></textarea>



                        </fieldset>
                        <fieldset id="collection-digest" class="tab-pane">

                        </fieldset>
                        <fieldset id="collection-callback" class="tab-pane">

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

