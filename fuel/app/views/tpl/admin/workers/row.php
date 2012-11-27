                            <tr>
                                <td>
                                    {{appname appslug }}
                                </td>
                                <td>
                                    {{dateFromTimestamp pushed.sec format='%d-%m-%Y %H:%M:%S'}}
                                </td>
                                <td>
                                    {{ job }}
                                </td>
                                <td>
                                    <span class="label {{jobStatusLabel status }}">{{jobStatusText status }}</span>
                                </td>
                                <td>
                                    <div class="btn-group float-right">
                                        <a href="javascript:;" data-job="{{ _id.$id }}" class="btn btn-mini btn-job-trigger">
                                            <i class="icon-play"></i>
                                            <?= __('tapioca.ui.label.worker_perfom'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>