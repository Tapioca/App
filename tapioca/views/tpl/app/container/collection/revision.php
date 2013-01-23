                                        {{#each revisions}}
                                        <li class="{{#if active}}well{{else}}revision{{/if}}" data-revision="{{ revision }}">
                                            <a href="javascript:;" class="revision-btn">
                                                <span class="revision-id">#{{ revision }}</span>
                                                <span class="revision-details">
                                                    {{dateFromTimestamp date.sec format='%Y-%m-%d %H:%M:%S'}}<br>
                                                    <?= __('tapioca.ui.label.by'); ?> {{username user}}
                                                </span>
                                            </a>
                                            <div class="dropdown btn-group pull-right">
                                                {{{docStatus this }}}
                                                {{> docStatusList}}
                                            </div>
                                        </li>
                                        {{/each}}