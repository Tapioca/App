                <ul class="thumbnails" style="margin-right:10px; float:left">
                    <li class="span2">
                        <div class="thumbnail">
                            <div class="media-preview">
                            {{#if thumb.url}}
                            <img src="{{thumb.url}}" alt="">
                            {{else}}
                            <i class="icon-{{thumb.icon}}"></i>
                            {{/if}}
                            </div>
                            <h5 class="align-center word-wrap">{{hash.filename}}</h5>
                            <p class="align-center">
                                <a href="javascript:void(0)" class="btn btn-mini file-remove-trigger">
                                    <i class="icon-trash"></i>
                                    remove
                                </a>
                                {{{thumb.str}}}
                            </p>
                        </div>
                    </li>
                </ul>