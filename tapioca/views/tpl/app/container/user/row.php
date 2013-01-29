                                <td>
                                    <img src="{{ avatar }}" alt="" width="50">
                                </td>
                                <td>
                                    {{ name }}
                                </td>
                                <td>
                                    {{{roleSelector appslug id operator}}}
                                </td>
                                <td>
                                    <a href="javascript:;" class="btn btn-mini btn-danger btn-delete-trigger">
                                        <i class="icon-trash"></i>
                                        <?= __('tapioca.ui.label.delete'); ?>
                                    </a>
                                </td>
