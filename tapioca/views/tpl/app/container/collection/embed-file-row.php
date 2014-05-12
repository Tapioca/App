                                    <td>
                                        {{#if isImage}}
                                        <a href="<?= Uri::create('app/'); ?>{{ appslug }}/library/{{ filename }}">
                                            <img src="{{ thumb }}" alt="">
                                        </a>
                                        {{/if}}
                                    </td>
                                    <td>
                                        <a href="javascript:;" class="select-file-trigger">{{filename}}</a>
                                    </td>
                                    <td>
                                        {{category}}
                                    </td>
                                    <td>
                                        <div class="btn-group float-right">
                                            <a href="javascript:void(0)" class="btn btn-mini select-file-trigger">
                                                <i class="icon-pencil"></i>
                                                <?= __('tapioca.ui.label.select'); ?>
                                            </a>
                                        </div>
                                    </td>
