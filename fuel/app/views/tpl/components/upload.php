
					<h2 class="page-name">Upload file </h2>
					<!-- The file upload form used as target for the file upload widget -->
					<form id="fileupload" action="<?= Uri::create('api/'); ?>{{ appslug }}/library" method="POST" enctype="multipart/form-data" class="clear-left">
						<br>
						<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
						<div class="row fileupload-buttonbar">
							<div class="span3">
								<!-- The fileinput-button span is used to style the file input field as button -->
								<span class="btn btn-success fileinput-button">
									<i class="icon-plus icon-white"></i>
									<span>Add files...</span>
									<input type="file" name="files[]" multiple>
								</span>
								<button type="submit" class="btn btn-primary start" id="btn-start-upload">
									<i class="icon-upload icon-white"></i>
									<span>Start upload</span>
								</button>
								<!-- button type="reset" class="btn btn-warning cancel">
									<i class="icon-ban-circle icon-white"></i>
									<span>Cancel upload</span>
								</button>
								<button type="button" class="btn btn-danger delete">
									<i class="icon-trash icon-white"></i>
									<span>Delete</span>
								</button>
								<input type="checkbox" class="toggle"-->
							</div>
							<div class="span5">
								<input id="tags" type="text" name="tags" placeholder="séparez les tags par des virgules" />
							</div>
							<!-- The global progress information -->
							<div class="span5 fileupload-progress fade">
								<!-- The global progress bar -->
								<div class="progress progress-success progress-striped active">
									<div class="bar" style="width:0%;"></div>
								</div>
								<!-- The extended global progress information -->
								<div class="progress-extended">&nbsp;</div>
							</div>
						</div>
						<!-- The loading indicator is shown during file processing -->
						<div class="fileupload-loading"></div>
						<!-- The table listing the files available for upload/download -->
						<table class="table table-striped">
							<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
						</table>
					</form>

				</div><!-- /.pane-content -->