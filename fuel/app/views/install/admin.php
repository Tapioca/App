			<div class="span4">
				<h2>Tapioca's master</h2>
				<p>First you need to create a master admin who will mange the tapioca server.</p>

				<p>Master admin is the one granted to create/edit/disable/destroy applications and users so you guess the next sentence, <em>great power...</em></p>
			</div>
			<div class="span8">
					<?php if($displayError): ?>
					<div class="alert alert-error">
						<?php 
							if(isset($form_error)):
								echo $form_error;
							else:
								echo '<strong>Oh snap!</strong> Change a few things up and try submitting again.';
							endif;
						?>
					</div>
					<?php endif; ?>
					<form method="post" accept-charset="utf-8" target="postFrame" class="form-horizontal" action="<?= Uri::create('install/start'); ?>">
					<fieldset>
						<legend class="padding-top-10px">Master Admin</legend>

						<div class="control-group<?php if(in_array('email', $errorsKeys)) echo ' error'; ?>">
							<label class="control-label" for="form_email">Your Email</label>
							<div class="controls">
								<?= Form::input('email', $email, array('type' => 'email', 'class' => 'input-xlarge')); ?>
								<span class="help-inline"><?= $validation->error('email'); ?></span>
							</div>
						</div>
						<div class="control-group<?php if(in_array('name', $errorsKeys)) echo ' error'; ?>">
							<label class="control-label" for="form_email">Your Name</label>
							<div class="controls">
								<?= Form::input('name', $name, array('class' => 'input-xlarge')); ?>
								<span class="help-inline"><?= $validation->error('name'); ?></span>
							</div>
						</div>
						<div class="control-group<?php if(in_array('password', $errorsKeys)) echo ' error'; ?>">
							<label class="control-label" for="form_email">Your password</label>
							<div class="controls">
								<?= Form::input('password', $name, array('type' => 'password', 'class' => 'input-xlarge')); ?>
								<span class="help-inline"><?= $validation->error('password'); ?></span>
							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend class="padding-top-10px">Application</legend>

						<div class="control-group<?php if(in_array('appname', $errorsKeys)) echo ' error'; ?>">
							<label class="control-label" for="form_email">Application name</label>
							<div class="controls">
								<?= Form::input('appname', $email, array('class' => 'input-xlarge')); ?>
								<span class="help-inline"><?= $validation->error('appname'); ?></span>
							</div>
						</div>
						<div class="control-group<?php if(in_array('appslug', $errorsKeys)) echo ' error'; ?>">
							<label class="control-label" for="form_email">Application slug</label>
							<div class="controls">
								<?= Form::input('appslug', $name, array('class' => 'input-xlarge')); ?>
								<span class="help-inline"><?= $validation->error('appslug'); ?></span>
							</div>
						</div>
					</fieldset>

					<div class="form-actions">
						<button class="btn btn-primary" type="submit">Save</button>
					</div>
				   
				</form>
			</div><!-- /span8 -->