
					<a href="<?= Uri::create('app/'); ?>{{ slug }}" class="app-nav-header" data-app-id="{{ id }}" data-app-slug="{{ slug }}">
						<!--span class="avatar">
							<img src="/dynamic/apps/dior-logo.jpg" alt="" />
						</span-->
						<h5 class="app-nav-name">{{ name }}</h5>
					</a><!-- /.app-nav-header -->
					<div class="app-nav-lists">
						{{#if isAppAdmin}}
						<h6>Admin</h6>
						<ul>
							<li>
								<a href="<?= Uri::create('app/'); ?>{{ slug }}/settings"><?= __('tapioca.ui.label.app_settings'); ?></a>
							</li>
							<li>
								<a href="<?= Uri::create('app/'); ?>{{ slug }}/user"><?= __('tapioca.ui.label.app_users'); ?></a>
							</li>
						</ul>
						{{/if}}

						<h6><?= __('tapioca.ui.label.app_documents'); ?></h6>
						<ul id="app-nav-collections-{{ slug }}">
							<li class="app-nav-collections-empty">
								<span class="no-collection"><?= __('tapioca.ui.label.no_collections'); ?></span>
							</li>
							{{#if isAppAdmin}}
							<li class="divider"></li>
							<li>
								<a href="<?= Uri::create('app/'); ?>{{ slug }}/collection/new" class="admin-action">
									<i class="icon-plus"></i>
									<?= __('tapioca.ui.label.add_collections'); ?>
								</a>
							</li>
							{{/if}}
						</ul>

						<h6><?= __('tapioca.ui.label.app_library'); ?></h6>
						<ul id="app-nav-files-{{ slug }}">
							<li data-namespace="library">
								<a href="<?= Uri::create('app/'); ?>{{ slug }}/library"><?= __('tapioca.ui.label.library_all_files'); ?></a>
							</li>
							<li class="divider"></li>
							<li>
								<a href="<?= Uri::create('app/'); ?>{{ slug }}/library/upload" class="admin-action">
									<i class="icon-plus"></i>
									<?= __('tapioca.ui.label.add_file'); ?>
								</a>
							</li>
						</ul>
					</div><!-- /.app-nav-lists -->
