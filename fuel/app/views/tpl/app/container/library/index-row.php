
                                    <td>
                                        {{#if select}}
                                            {{file.filename}}
                                        {{/if}}
                                        {{#unless select}}
                                        <a href="<?= Uri::create('app/'); ?>{{ appslug }}/library/{{ filename }}">{{filename}}</a>
                                        {{/unless}}
                                    </td>
                                    <td>
                                        {{category}}
                                    </td>
                                    <td>
                                        <div class="btn-group float-right">
                                            {{#unless select}}
                                            <a href="<?= Uri::create('app/'); ?>{{ appslug }}/library/{{ filename }}" class="btn btn-mini">
                                                <i class="icon-pencil"></i>
                                                <?= __('tapioca.ui.label.edit'); ?>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-mini btn-danger delete-file-trigger" data-filename="{{ filename }}">
                                                <i class="icon-trash"></i>
                                                <?= __('tapioca.ui.label.delete'); ?>
                                            </a>
                                            {{/unless}}
                                            {{#if select}}
                                            <a href="javascript:void(0)" class="btn btn-mini select-file-trigger">
                                                <i class="icon-pencil"></i>
                                                <?= __('tapioca.ui.label.select'); ?>
                                            </a>
                                            {{/if}}
                                        </div>
                                    </td>
