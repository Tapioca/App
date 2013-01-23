
					<h2 class="page-name">Upload file{{#multiple}}s{{/multiple}}</h2>
					<a href="javascript:;" id="close-upload" class="close">x</a>
					<!-- The file upload form used as target for the file upload widget -->
					<form id="fileupload" action="<?= Uri::create('api/'); ?>{{ appslug }}/library/{{ filename }}" method="POST" enctype="multipart/form-data" class="clear-left">
						<br>
						<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
						<div class="row fileupload-buttonbar">

							<div class="span7">
								<!-- The fileinput-button span is used to style the file input field as button -->
								<span class="btn btn-success fileinput-button">
									<i class="icon-plus icon-white"></i>
									<span>Add files...</span>
									<input type="file" name="files[]"{{#multiple}} multiple{{/multiple}}>
								</span>
								<button type="submit" class="btn btn-primary start" id="btn-start-upload">
									<i class="icon-upload icon-white"></i>
									<span>Start upload</span>
								</button>

								<input id="tags" type="text" placeholder="séparez les tags par des virgules">
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
						<ul id="upload-files-list">
						</ul>
					</form>

				</div><!-- /.pane-content -->