
					<a href="javascript:void(0)" class="app-nav-header" data-app-id="{{ id }}" data-app-slug="{{ slug }}">
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
								<a href="#">application</a>
							</li>
							<li>
								<a href="#">utilisateurs</a>
							</li>
						</ul>
						{{/if}}

						<h6>Documents</h6>
						<ul id="app-nav-collections-{{ slug }}">
							<li class="app-nav-collections-empty">
								<span class="no-collection">Pas de collections</span>
							</li>
							{{#if isAppAdmin}}
							<li class="divider"></li>
							<li>
								<a href="app/{{ slug }}/collections/new" class="admin-action">
									<i class="icon-plus"></i>
									Ajouter une collection
								</a>
							</li>
							{{/if}}
						</ul>

						<h6>Fichiers</h6>
						<ul id="app-nav-files-{{ slug }}">
							<li data-namespace="library">
								<a href="app/{{ slug }}/file">Library</a>
							</li>
							<li class="divider"></li>
							<li>
								<a href="app/{{ slug }}/file/upload" class="admin-action">
									<i class="icon-plus"></i>
									Ajouter un fichier
								</a>
							</li>
						</ul>
					</div><!-- /.app-nav-lists -->
