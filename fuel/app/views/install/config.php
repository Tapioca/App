			<div class="span4">
				<h2>Default styles</h2>
				<p>All forms are given default styles to present them in a readable and scalable way. Styles are provided for text inputs, select lists, textareas, radio buttons and checkboxes, and buttons.</p>
			</div>
			<div class="span8">
				<form>
					<fieldset>
						<legend class="padding-top-10px">MongoDb</legend>

						<div class="clearfix">
							<label for="host">host</label>
							<div class="input">
								<input type="text" size="30" name="host" id="host" class="xlarge" placeholder="localhost">
							</div>
						</div>
						<div class="clearfix">
							<label for="port">port</label>
							<div class="input">
								<input type="text" size="30" name="port" id="port" class="xlarge" placeholder="27017">
							</div>
						</div>
						<div class="clearfix">
							<label for="username">username</label>
							<div class="input">
								<input type="text" size="30" name="username" id="username" class="xlarge">
							</div>
						</div>
						<div class="clearfix">
							<label for="password">password</label>
							<div class="input">
								<input type="text" size="30" name="password" id="password" class="xlarge">
							</div>
						</div>
						<div class="clearfix">
							<label for="db">database</label>
							<div class="input">
								<input type="text" size="30" name="db" id="db" class="xlarge" placeholder="translantic">
							</div>
						</div>
						<div class="clearfix">
							<label for="collection-prefix">collection prefix</label>
							<div class="input">
								<input type="text" size="30" name="collection-prefix" id="collection-prefix" class="xlarge" placeholder="i18n_">
							</div>
						</div>
					</fieldset>
					
					<div class="actions">
						<input type="submit" value="Save changes" class="btn primary">
						<button class="btn" type="reset">Cancel</button>
					</div>
				   
				</form>
			</div><!-- /span8 -->