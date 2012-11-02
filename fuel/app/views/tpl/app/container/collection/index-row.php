
                                {{{displayDigest digest uri}}}
                                <td> 
                                    {{{docStatus _ref locale.key revisions }}}
                                </td>
                                <td>
                                    <div class="btn-group float-right">
                                        <a href="<?= Uri::create('app/'); ?>{{ appslug }}/{{ namespace }}/{{ _ref }}" class="btn btn-mini">
                                            <i class="icon-pencil"></i>
                                            <?= __('tapioca.ui.label.edit'); ?>
                                        </a>
                                        <a href="<?= Uri::create('app/'); ?>{{ appslug }}/{{ namespace }}/clone/{{ _ref }}" class="btn btn-mini">
                                            <i class="icon-pencil"></i>
                                            <?= __('tapioca.ui.label.clone'); ?>
                                        </a>
                                        {{#if isAppAdmin}}
                                        <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                            <i class="icon-trash"></i>
                                        </a>
                                        {{/if}}
                                    </div>
                                </td>