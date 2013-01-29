
                                <td>
                                </td>
                                <td>
                                    <a href="<?= Uri::create('app/admin/app/'); ?>{{ slug }}">
                                        {{ name }}
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group float-right">
                                        <a href="<?= Uri::create('app/admin/app/'); ?>{{ slug }}" class="btn btn-mini">
                                            <i class="icon-pencil"></i>
                                            <?= __('tapioca.ui.label.edit'); ?>
                                        </a>
                                        <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                            <i class="icon-trash"></i>
                                            <?= __('tapioca.ui.label.delete'); ?>
                                        </a>
                                    </div>
                                </td>
