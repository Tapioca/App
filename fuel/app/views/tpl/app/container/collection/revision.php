
                                    {{#each revisions}}
                                        <li {{#if active}}class="well"  id="revision-active"{{else}}class="revision"{{/if}}>
                                            <a href="{{ ../baseUri }}?r={{ revision }}">
                                                <span class="revision-id">#{{ revision }}</span>
                                                <span class="revision-details">
                                                    {{dateFromTimestamp date.sec format='%Y-%m-%d %H:%M:%S'}}<br>
                                                    <?= __('tapioca.ui.label.by'); ?> {{user.name}}
                                                </span>
                                            </a>
                                        </li>
                                    {{/each}}
                                    