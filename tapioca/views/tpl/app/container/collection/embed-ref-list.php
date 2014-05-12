
                <a href="javascript:;" class="close" id="close-popup-list">x</a>
                <div class="app-content-header">
                    <h2 class="page-name">{{ name }}</h2>
                </div><!-- /#app-content-header -->
                <div class="pane-content header-active" style="padding-top: 70px">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                {{#digest}}
                                <th>{{ label }}</th>
                                {{/digest}}
                                <th width="150"></th>
                            </tr>
                        </thead>
                        <tbody>
                        {{#abstracts}}
                            <tr>
                                {{{displayDigest digest}}}
                                <td>
                                    <div class="btn-group float-right">
                                        <a href="javascript:;" class="btn btn-mini" data-ref="{{ _ref }}">
                                            <?= __('tapioca.ui.label.select'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {{/abstracts}}
                        </tbody>
                    </table>
                </div>