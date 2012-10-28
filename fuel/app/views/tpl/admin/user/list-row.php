
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
                                    <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}">
                                        {{ email }}
                                    </a>
                                </td>
                                <td>
                                    {{#if admin}}
                                    <?= __('tapioca.ui.label.tapp_admin'); ?> /
                                    {{/if}}
                                    {{#if activated}}
                                    <?= __('tapioca.ui.label.activated'); ?>
                                    {{else}}
                                    <?= __('tapioca.ui.label.not_activated'); ?>
                                    {{/if}}
                                </td>
                                <td>
                                    <div class="btn-group float-right">
                                        <a href="<?= Uri::create('app/admin/user/'); ?>{{ id }}" class="btn btn-mini">
                                            <i class="icon-pencil"></i>
                                            <?= __('tapioca.ui.label.edit'); ?>
                                        </a>
                                        <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                            <i class="icon-trash"></i>
                                        </a>
                                    </div>
                                </td>
