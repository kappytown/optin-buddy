<?php defined('ABSPATH') || exit; ?>

<section class="obp-admin add-form-section wrap">
	<!-- Choose Your Form Type Section -->
	<section class="section-choose-type hidden">
		<h1>Choose Your Form Type</h1>
		<p>Form type description goes Here</p>
		<div class="icons">
			<div class="icon" data-type="inline-form" data-type_id="1">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/inline-form.svg" /></div>
				<div class="details">
					<strong>Inline Form</strong>
					<p>An inline optin, is an optin box that appears inside of your post at your desired location.</p>
				</div>
			</div>
			<div class="icon" data-type="floating-box" data-type_id="2">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/floating-box.svg" /></div>
				<div class="details">
					<strong>Floating Box</strong>
					<p>A floating box optin will appear from the bottom left or right of the page on top of content. While you can use them to highlight your lead magnet, they also work well for enticing people to read a related piece of content.</p>
				</div>
			</div>
			<div class="icon" data-type="modal-popup" data-type_id="3">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/modal-popup.svg" /></div>
				<div class="details">
					<strong>Modal Popup</strong>
					<p>A modal popup optin puts a semi-opaque overlay over page content, while highlighting your popup. Modal popups definitely get attention, so it's no surprise that these optins are the highest converting popups.</p>
				</div>
			</div>
			<div class="icon" data-type="fixed-top" data-type_id="4">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/fixed-top.svg" /></div>
				<div class="details">
					<strong>Fixed Top Bar</strong>
					<p>A fixed top optin sits at the top of the page, hidden until the user scrolls or a specifed amount of time has passed. This optin type floats above the content so it doesn't affect the user experience.</p>
				</div>
			</div>
			<div class="icon" data-type="exit-intent" data-type_id="5">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/exit-intent.svg" /></div>
				<div class="details">
					<strong>Exit Intent</strong>
					<p>An exit intent optin is a modal popup that is displayed when a user is about to leave your site.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Choose Your Form Template Section -->
	<section class="section-choose-template hidden">
		<h1>Choose Your Form Template</h1>
		<p>Form template description goes here</p>
		<div class="icons">
			<div class="icon" data-template="no-image" data-template_id="1">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/no-image.svg" /></div>
				<div class="details">
					<strong>No Image</strong>
					<p>Info goes here</p>
				</div>
			</div>
			<div class="icon" data-template="image-top" data-template_id="2">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/image-top.svg" /></div>
				<div class="details">
					<strong>Image Top</strong>
					<p>Info goes here</p>
				</div>
			</div>
			<div class="icon" data-template="image-left" data-template_id="3">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/image-left.svg" /></div>
				<div class="details">
					<strong>Image Left</strong>
					<p>Info goes here</p>
				</div>
			</div>
			<div class="icon" data-template="image-right" data-template_id="4">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/image-right.svg" /></div>
				<div class="details">
					<strong>Image Right</strong>
					<p>Info goes here</p>
				</div>
			</div>
			<div class="icon" data-template="image-background" data-template_id="5">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/image-background.svg" /></div>
				<div class="details">
					<strong>Image in Back</strong>
					<p>Info goes here</p>
				</div>
			</div>
			<div class="icon hidden" data-template="fixed-top-bar" data-template_id="6">
				<div class="image"><img src="<?php echo OPTIN_BUDDY_URL ?>assets/img/fixed-bar.svg" /></div>
				<div class="details">
					<strong>Floating Bar</strong>
					<p>Info goes here</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Edit Form Details Section -->
	<section class="section-form-details hidden">
		<h1>Edit Form Details</h1>
		<p>Here you can add or update the placement, layout, and settigns of your email form.</p>
		<div class="desc"><strong>Note:</strong> To change the content of your form, click in the area you with to change.</div>

		<div class="flex-container">
			<!-- Form Template Layout Section -->
			<div class="flex-form-layout">
				<div class="template opt-bud"></div>
				<style name="overrides"></style>
			</div>

			<!-- Form Template Settings Section -->
			<div class="flex-form-settings">
				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="form-add-new">
					<input type="hidden" name="action" value="save_add_form_action" />
					<input type="hidden" name="nonce" value="" />
					<input type="hidden" name="id" value="<?php echo $form->id; ?>" />
					<input type="hidden" name="type_id" value="<?php echo $form->type_id ?>" />
					<input type="hidden" name="template_id" value="<?php echo $form->template_id ?>" />
					<input type="hidden" name="header" />
					<input type="hidden" name="body" />
					<input type="hidden" name="button" />
					<input type="hidden" name="disclaimer" />
					<input type="hidden" name="page_location_value" value="<?php echo $form->page_location_value; ?>" />
					<input type="hidden" name="page_timing_value" value="<?php echo $form->page_timing_value; ?>" />
					<input type="hidden" name="target_categories" />

					<!-- Navigation Tabs -->
					<ul class="tabs">
						<li class="tab selected" data-section="styles">Styles</li>
						<li class="tab" data-section="location">Location</li>
						<li class="tab" data-section="email-settings">Email Settings</li>
					</ul>

					<!-- Navigation Styles Tab -->
					<section class="section-tab-styles tab-section">
						<!-- Form Type/Template Selection Section -->
						<section class="section-form-selection">
							<div class="title">Form Selection:</div>
							<span class="desc">The form type and form template that you selected.</span>

							<div class="flex inline gap20 nowrap">
								<div class="flex nowrap form-type">
									<div class="icon">
										<img src="">
									</div>
									<div>
										<strong>Form Type:</strong>
										<span class="name"></span>
									</div>
								</div>

								<div class="flex nowrap form-template">
									<div class="icon">
										<img src="">
									</div>
									<div>
										<strong>Form Template:</strong>
										<span class="name"></span>
									</div>
								</div>
							</div>
							<button class="button button-primary change-template">Change Form Selection</button>
						</section>

						<!-- Add Additional Fields Section -->
						<section class="section-additional-fields">
							<div class="title">Additional Fields:</div>
							<span class="desc">Add the following additional fields to this form.</span>

							<div class="field">
								<label for="has_name_field"><input type="checkbox" id="has_name_field" name="has_name_field" value="1" <?php echo ($form->has_name_field === 1 ? 'checked="checked"' : ''); ?>>Add First Name Field</label>
							</div>
						</section>

						<!-- Change Image Section -->
						<section class="section-change-image <?php echo !$has_image ? 'hidden' : ''; ?>">
							<div class="title">Image: <span class="required">*</span></div>
							<span class="desc">Change the image in this form.</span>

							<div class="image-selector">
								<input type="hidden" id="image_id" name="image_id" value="<?php echo esc_attr($form->image_id); ?>" />
								<input type="hidden" id="image_url" name="image_url" value="" />
								<div class="image-wrapper">
									<div id="image-preview"></div>
									<input type="button" id="upload-button" class="button" value="Change Image" />
								</div>
							</div>
						</section>

						<!-- Customize CSS Section -->
						<section class="section-change-styles">
							<div class="title">Custom CSS:</div>
							<span class="desc">Change the default CSS.</span>
							<a href="#" class="css-show-me">Show Me How</a>
							<div class="field">
								<textarea name="custom_css" rows="10" placeholder="<?php echo ".opt-bud .email-container {}\n.opt-bud .email-container .image {}\n.opt-bud .email-container h3 {}\n.opt-bud .email-container p {}\n.opt-bud .email-container input {}\n.opt-bud .email-container .button {}\n.opt-bud .email-container .button:hover {}\n.opt-bud .email-container .disclaimer {}\n.opt-bud .email-container.submitted .message {}\n.opt-bud .email-container .spinner {}" ?>"><?php echo $form->custom_css; ?></textarea>
							</div>

							<div class="flex field">
								<button type="button" class="button css-preview">Preview</button>
								<button type="button" class="button css-reset" style="margin-left: auto;">Reset CSS</button>
							</div>
						</section>

						<!-- Success Message Section -->
						<section class="section-success-message">
							<div class="title">Success Message: <span class="required">*</span></div>
							<span class="desc">Message to show when this form has been submitted.</span>

							<div class="field">
								<textarea rows="3" name="success" required><?php echo $form->success; ?></textarea>
							</div>

							<div class="field">
								<label for="preview_success_message"><input type="checkbox" id="preview_success_message" name="preview_success_message" value="1">Preview Success Message</label>
							</div>
						</section>
					</section>

					<!-- Navigation Locations Tab -->
					<section class="section-tab-location tab-section hidden">
						<!-- Page Type Section -->
						<section class="page-type">
							<div class="title">Page Type: <span class="required">*</span></div>
							<span class="desc">The page type where this form will be displayed on.</span>

							<div class="field">
								<label for="all_pages"><input type="radio" id="all_pages" name="page_type" value="all" <?php echo ($form->page_type === 'all' ? 'checked="checked"' : ''); ?>> All Pages</label>
							</div>
							<div class="field">
								<label for="post_pages"><input type="radio" id="post_pages" name="page_type" value="post" <?php echo ($form->page_type === 'post' ? 'checked="checked"' : ''); ?>> Post Pages</label>
							</div>
							<div class="field">
								<label for="category_pages"><input type="radio" id="category_pages" name="page_type" value="category" <?php echo ($form->page_type === 'category' ? 'checked="checked"' : ''); ?>> Category Pages</label>
							</div>
						</section>

						<!-- Page Location Section -->
						<section class="page-location <?php echo $form->type_id !== 1 ? 'hidden' : ''; ?>">
							<div class="title">Page Location: <span class="required">*</span></div>
							<span class="desc">The location in the page where this form will be displayed.</span>

							<div class="field">
								<label class="flex gap5 center nowrap" for="before_paragraph"><input type="radio" id="before_paragraph" name="page_location" value="before_paragraph" <?php echo ($form->page_location === 'before_paragraph' ? 'checked="checked"' : ''); ?>><span>Before paragraph</span><input type="number" placeholder="Paragraph #" value="<?php echo ($form->page_location === 'before_paragraph' ? $form->page_location_value : ''); ?>"></label>
							</div>
							<div class="field">
								<label class="flex gap5 center nowrap" for="after_paragraph"><input type="radio" id="after_paragraph" name="page_location" value="after_paragraph" <?php echo ($form->page_location === 'after_paragraph' ? 'checked="checked"' : ''); ?>><span>After paragraph</span><input type="number" placeholder="Paragraph #" value="<?php echo ($form->page_location === 'after_paragraph' ? $form->page_location_value : ''); ?>"></label>
							</div>
							<div class="field">
								<label class="flex gap5 center nowrap" for="before_element"><input type="radio" id="before_element" name="page_location" value="before_element" <?php echo ($form->page_location === 'before_element' ? 'checked="checked"' : ''); ?>><span>Before HTML element</span><input type="text" placeholder="HTML Element e.g. .my-class or #my_id" value="<?php echo ($form->page_location === 'before_element' ? $form->page_location_value : ''); ?>"></label>
							</div>
							<div class="field">
								<label class="flex gap5 center nowrap" for="after_element"><input type="radio" id="after_element" name="page_location" value="after_element" <?php echo ($form->page_location === 'after_element' ? 'checked="checked"' : ''); ?>><span>After HTML element</span><input type="text" placeholder="HTML Element e.g. .my-class or #my_id" value="<?php echo ($form->page_location === 'after_element' ? $form->page_location_value : ''); ?>"></label>
							</div>
						</section>

						<!-- Form Location Section -->
						<section class="form-location <?php echo $form->type_id !== 2 ? 'hidden' : ''; ?>">
							<div class="title">Form Location: <span class="required">*</span></div>
							<span class="desc">The location on page where this form will appear from.</span>

							<div class="field">
								<label for="bottom_right"><input type="radio" id="bottom_right" name="form_location" value="bottom_right" <?php echo ($form->form_location === 'bottom_right' ? 'checked="checked"' : ''); ?>> Bottom Right</label>
							</div>
							<div class="field">
								<label for="bottom_left"><input type="radio" id="bottom_left" name="form_location" value="bottom_left" <?php echo ($form->form_location === 'bottom_left' ? 'checked="checked"' : ''); ?>> Bottom Left</label>
							</div>
						</section>

						<!-- Page Timing Section -->
						<section class="page-timing <?php echo $form->type_id == 1 ? 'hidden' : ''; ?>">
							<div class="title">Form Timing: <span class="required">*</span></div>
							<span class="desc">This is when this form should be displayed.</span>
							<div class="field">
								<label for="scroll" class="flex gap5 center nowrap">
									<input type="radio" id="scroll" name="page_timing" value="scroll" <?php echo ($form->page_timing === 'scroll' ? 'checked="checked"' : ''); ?> />The visitor scrolls to <input type="number" value="<?php echo ($form->page_timing === 'scroll' ? $form->page_timing_value : ''); ?>" style="width:50px; min-width:50px;" /> % of the page.
								</label>
							</div>
							<div class="field">
								<label for="delay" class="flex gap5 center nowrap">
									<input type="radio" id="delay" name="page_timing" value="delay" <?php echo ($form->page_timing === 'delay' ? 'checked="checked"' : ''); ?> />The visitor is on the page for <input type="number" value="<?php echo ($form->page_timing === 'delay' ? $form->page_timing_value : ''); ?>" style="width:50px; min-width:50px;" /> seconds.
								</label>
							</div>
						</section>

						<!-- Select Categories Section -->
						<?php if (!empty($categories)) { ?>
							<section class="categories-section">
								<div class="title">Category Filter:</div>
								<span class="desc">(Optional) Use this filter to only show this form on the selected categories within the specified page type above.</span>
								<div class="slide-toggle multi-select">
									<div class="title select-box">
										<div class="tag-container">Select a Category</div>
									</div>
									<div class="body">
										<div class="dropdown-content">
											<input type="text" placeholder="Search..." class="search-box">
											<ul class="categories">
												<?php foreach ($categories as $category) { ?>
													<li data-term-id="<?php echo esc_html($category->term_id); ?>" class="<?php echo in_array($category->term_id, $form->target_categories) ? 'selected' : '' ?>"><?php echo esc_html($category->name); ?></li>
												<?php } ?>
											</ul>
										</div>
									</div>
								</div>
							</section>
						<?php } ?>

						<!-- URL Exclusion List -->
						<section class="section-exclusion">
							<div class="title">URL Exclusion List:</div>
							<div class="desc">Add the URLs that you do NOT want this form to appear on.<br /><strong>Note:</strong> The URLs must be separated by a new line.</div>
							<div class="field"><textarea name="exclusion_list" rows="5"><?php echo $form->exclusion_list ?></textarea></div>
						</section>

						<!-- Inview Effect Section -->
						<section class="section-inview">
							<div class="title">Effects:</div>
							<span class="desc">(Optional) This will apply the inview effect to this form. The inview effect will highlight the form when the user first scrolls to where it is in view.</span>
							<p class="desc hidden"><strong>Note:</strong> If the form is within view when the page is first loaded, the effect will not be applied.</p>
							<div class="field">
								<label for="inview"><input type="checkbox" id="inview" name="inview" <?php echo ($form->inview === 1 ? 'checked="checked"' : ''); ?> value="1" />Apply inview effect?</label>
							</div>
						</section>

						<!-- Deactivate Form Section -->
						<section class="section-deactivate">
							<div class="title">Form Visibility:</div>
							<span class="desc">This will prevent this form from displaying on all pages.</span>
							<div class="field deactivate">
								<label for="deactivate"><input type="checkbox" id="deactivate" name="deactivate" value="1" <?php echo ($form->deactivate === 1 ? 'checked="checked"' : ''); ?>>Deactivate Form?</label>
							</div>
						</section>
					</section>

					<!-- Navigation Email Settings Tab -->
					<section class="section-tab-email-settings tab-section hidden">
						<!-- Advanced Email Settings Section -->
						<?php
						$fields = $Provider->get_meta_fields();
						if (count($fields) > 0) {
						?>
							<section class="section-advanced">
								<div class="title">Email Provider Settings:</div>

								<span class="desc">These are fields specific to your email provider.</span>
								<div class="slide-toggle">
									<div class="title">Email Provider Fields</div>
									<div class="body">
										<?php
										foreach ($fields as $field) {
											// Get the saved value or default if empty
											$value = !empty($form->meta[$field['key']]) ? $form->meta[$field['key']] : $field['value'];
											$type = $field['type'];

											if ($type == 'checkbox') {
												// Only get the default value if this form has NOT been saved
												if (!empty($form->id)) {
													$value = !empty($form->meta[$field['key']]) ? $form->meta[$field['key']] : '';
												}
												$checked = !empty($value) ? 'checked="checked"' : '';
												echo "<div class='field'><label><input type='{$field['type']}' name='meta[{$field['key']}]' value='1' {$checked}/> {$field['name']}:</label><div class='desc'>{$field['desc']}</div></div>";
											} else if ($type === 'select') {
												$options = !empty($field['options']) ? $field['options'] : [];
												$handler = !empty($field['handler']) ? $field['handler'] : '';

												echo "<div class='field'><label>{$field['name']}:</label><span class='desc'>{$field['desc']}</span><div style='display: flex; gap: 10px;'><select name='meta[{$field['key']}]' value='{$value}' data-handler='{$handler}'>";
												foreach ($options as $option) {
													$selected = $option['value'] == $value ? 'selected' : '';
													echo "<option value='{$option['value']}' {$selected}>{$option['name']}</option>";
												}
												echo "</select><button type='button' class='button button-small btn-refresh-list'>Refresh List</button></div></div>";
											} else {
												echo "<div class='field'><label>{$field['name']}:</label><span class='desc'>{$field['desc']}</span><input type='{$field['type']}' name='meta[{$field['key']}]' value='{$value}' /></div>";
											}
										}
										?>
									</div>
								</div>
							</section>
						<?php } ?>

						<!-- Custom Fields Section -->
						<section class="section-custom-fields">
							<div class="title">Custom Fields:</div>
							<div class="desc">The following fields will also be sent to your provider so that you can map them to your custom fields.:</div>
							<p>Page title:<br /><span class="desc">page_title, PAGE_TITLE</span></p>
							<p>Page URL:<br /><span class="desc">page_url, PAGE_URL</span></p>
							<p>Recipe (Page URL):<br /><span class="desc">recipe, RECIPE</span></p>
						</section>

						<!-- Customize Email Section -->
						<section class="section-custom-email">
							<div class="title">Custom Email:</div>
							<div class="desc">This will automatically send a custom email to the subscriber of this form upon submission.</div>

							<div class="field">
								<label for="send_email"><input type="checkbox" id="send_email" name="send_email" <?php echo ($form->send_email === 1 ? 'checked="checked"' : ''); ?> value="1" />Send custom email to subscriber?</label>
							</div>

							<div class="field">
								<label for="send_email_subject">Subject:</label>
								<input type="text" id="send_email_subject" name="send_email_subject" placeholder="Thank you for signing up" value="<?php echo $form->send_email_subject; ?>" />
							</div>

							<div class="field">
								<label for="send_email_message">Message:</label>
								<?php
								wp_editor($form->send_email_message, 'send_email_message', [
									'wpautop' => true,
									'default_editor' => 'tinymce',
									'media_buttons' => false,
									'textarea_name' => 'send_email_message',
									'textarea_rows' => 20,
									'tabindex' => '-1',
									'editor_css' => '<style>.mce-btn.mce-active{background-color: #50575e!important;}</style>',
									'editor_class' => 'editor',
									'teeny' => false,
									'editor_height' => 250,
									// 'toolbard_mode' => 'floating',
									'tinymce' => [
										'toolbar1' => 'formatselect, fontsizeselect, forecolor, bold, italic, underline, link, addbutton, image, bbullist, numlist, alignleft, aligncenter, alignright, custom_fields',
										'toolbar2' => false
									],
									'quicktags' => false
								]);
								?>
								<!-- <textarea rows="5" id="send_email_message" name="send_email_message" style="padding: 5px; border-radius: 5px;" placeholder="<?php echo "Thanks for signing up, here is your recipe!\n\n{{page_title}}\n{{recipe}}\n"; ?>"></textarea> -->
							</div>
							<div class="desc">
								<strong>{{page_title}}</strong> - Adds the page title.<br />
								<strong>{{page_url}}</strong> - Adds the page URL.<br />
								<strong>{{recipe}}</strong> - Adds the page URL.
							</div>
							<div class="email-notice">
								<strong>Note:</strong> This requires that you have setup your SMTP settings in WordPress.
								<p class="flex gap10 nowrap">
									<input type="email" placeholder="Email Address" />
									<button type="button" class="button send-test">Send Test</button>
								</p>
								<div class="desc"><strong>Note:</strong> If you do not receive an email, please verify your SMTP WordPress settings.</div>
							</div>
						</section>
					</section>

					<!-- Cancel / Save Buttons -->
					<div class="form-actions">
						<button type="button" data-location="<?php echo admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'main_settings') ?>" class="button cancel-form">Cancel</button>
						<button type="submit" class="button button-primary save-form">Save Form</button>
					</div>
				</form>
			</div>
		</div>
	</section>
</section>