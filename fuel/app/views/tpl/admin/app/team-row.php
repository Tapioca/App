                                        <td>
                                            <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}">
                                                <img src="{{ avatar }}" alt="" width="50">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}">
                                                {{ name }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ roleDisplay }}
                                        </td>
                                        <td>
                                            {{^disabled}}
                                            <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                                <i class="icon-trash"></i>
                                                <?= __('tapioca.ui.label.delete'); ?>
                                            </a>
                                            {{/disabled}}
                                        </td>