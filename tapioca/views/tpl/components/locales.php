								<li>
                                    <input type="text" name="locale-label" value="{{ label }}" placeholder="<?= __('tapioca.ui.label.label'); ?>">
                                    <input type="text" name="locale-key" value="{{ key }}" placeholder="<?= __('tapioca.ui.label.key'); ?>">
                                    <input type="radio" name="locale-default" value="1"{{#default}} checked{{/default}}>
                                    <a href="javascript:void(0)" class="btn btn-mini input-repeat-trigger">
                                        <i class="icon-repeat-trigger"></i>
                                    </a>
                                </li>