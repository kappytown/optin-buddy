
jQuery(document).ready(function ($) {
	// Prevents multiple submissions
	let _processing = false;

	// Holds a the values of the selected form type, type_id, template and template_id
	let _form_type;
	let _form_type_id;
	let _form_template;
	let _form_template_id;

	// If set to true, this will hide the initial form type and form template selector sections
	let _edit_mode = false;

	/**
	 * Choose Form Type click handler
	 */
	$(document).on('click', '.section-choose-type .icon', function (e) {
		const $this = $(this);
		$('.section-choose-type .icon').removeClass('selected');
		$this.addClass('selected');

		_form_type = $this.data('type');
		_form_type_id = Number($this.data('type_id'));

		// Hide this section
		$('.section-choose-type').hide();

		// Hide all icons 
		$('.section-choose-template .icon').hide();

		if (_form_type_id == 4) {
			$('.section-choose-template .icon[data-template_id="6"]').show();
		} else {
			$('.section-choose-template .icon[data-template_id!="6"]').show();
		}

		// If we are in edit mode, we do not want to see the form type and template sections
		if (!_edit_mode) {
			$('.section-choose-template').show();
		}
	});

	/**
	 * Choose Form Template click handler
	 * This will load the selected template and display it so it can be edited
	 */
	$(document).on('click', '.section-choose-template .icon', function (e) {
		e.preventDefault();

		// Prevent multiple clicks
		if (_processing) return;
		_processing = true;

		const $this = $(this);
		$('.section-choose-template .icon').removeClass('selected');
		$this.addClass('selected');

		_form_template = $this.data('template');
		_form_template_id = Number($this.data('template_id'));

		// Updated all the necessary form details based off the type and template selections
		update_form_details();

		// Set edit mode to false as it is only needed up until the first time we display the form
		_edit_mode = false;

		const url = AdminAddFormAjax.ajax_url;
		const data = new URLSearchParams();
		data.append('action', 'handle_load_template');
		data.append('nonce', AdminAddFormAjax.nonce);
		data.append('form_id', $('input[name="id"]').val());
		data.append('type_id', _form_type_id);
		data.append('template_id', _form_template_id);

		opt_bud.utils.loader.show();

		// Gets the form to render
		fetch(url, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			}
		})
			.then(response => response.text())
			.then(response => {
				// Populate the form
				const $template = $('.template.opt-bud');
				$template.html(response);

				// Remove default behaviour and make elements editable
				$('button, input', $template).on('click', function (e) {
					e.preventDefault();
				});

				$('.header, .body, button>span, .disclaimer', $template).prop('contenteditable', 'true');

				// Hide this section and show the form details section
				$('.section-choose-template').hide();
				$('.section-form-details').show();

				// add name field
				if ($('input[name="has_name_field"]:checked')[0]) {
					$('.email-container').addClass('has-name');
				}

				// Update colors
				//$('.flex.colors .field input').trigger('blur');

				// Does this template have an image?
				if (_form_template === 'no-image' || _form_template === 'fixed-top-bar') {
					$('.section-change-image').hide();
				} else {
					$('#image-preview').css('background-image', $('.email-container .image').css('background-image'));
					$('.section-change-image').show();
				}

				_processing = false;
				opt_bud.utils.loader.remove();
			})
			.catch(error => {
				console.log(error);
				_processing = false;
				opt_bud.utils.loader.remove();
			});
	});

	/**
	 * Edit Form Tabs click handler
	 */
	$(document).on('click', 'ul.tabs li.tab', function (e) {
		$('ul.tabs li.selected').removeClass('selected');
		$(this).addClass('selected');

		const section = $(this).data('section');
		$('.tab-section').hide();
		$('.section-tab-' + section).show();
	});

	/**
	 * Change Template click handler
	 */
	$(document).on('click', '.change-template', function (e) {
		e.preventDefault();
		$('.section-choose-template, .section-form-details').hide();
		$('.section-choose-type').show();

		// uncheck preview success message
		$('input[name="preview_success_message"]').prop('checked', false);
	});

	/**
	 * Add Name Field change handler
	 * This will add/remove the name field from the form
	 */
	$(document).on('change', '.section-additional-fields input[type="checkbox"]', function (e) {
		if ($(this).is(':checked')) {
			$('.email-container').addClass('has-name');
		} else {
			$('.email-container').removeClass('has-name');
		}
	});

	/* Change Image click handler is located in admin-image-selector.js */

	/**
	 * CSS Show Me Click Handler
	 */
	$(document).on('click', '.css-show-me', function (e) {
		e.preventDefault();

		if (_processing) return;
		_processing = true;

		const params = {
			'action': 'handle_load_content',
			'nonce': AdminAddFormAjax.nonce,
			'template': 'css-show-me'
		}

		opt_bud.utils.ajax(AdminAddFormAjax.ajax_url, 'POST', params, (error, response) => {
			_processing = false;
			if (response && response !== 'not found') {
				opt_bud.modal.show('show-me', { title: 'CSS Show Me', body: response, closeButton: true });
			} else {
				opt_bud.utils.showAdminNotice('Unable to load the CSS examples at this time. Please try again later.', 'error');
			}
		}, true);
	});

	/**
	 * CSS Preview Click Handler
	 */
	$(document).on('click', '.css-preview', function (e) {
		e.preventDefault();
		$('style[name="overrides"]').html($('textarea[name="custom_css"]').val());

	});

	/**
	 * CSS Reset Click Hanlder
	 */
	$(document).on('click', '.css-reset', function (e) {
		e.preventDefault();
		$('style[name="overrides"]').empty();
	});

	/**
	 * This will show/hide the success message on the form
	 */
	$(document).on('change', 'input[name="preview_success_message"]', function (e) {
		const $this = $(this);
		const $container = $('.email-container');
		const message = $this.closest('section').find('textarea[name="success"]').val();
		const $response = $('.message', $container);

		if ($this.is(':checked')) {
			$response.html(message).removeClass('hidden');
			$container.addClass('submitted');
		} else {
			$response.addClass('hidden');
			$container.removeClass('submitted');
		}
	});

	/**
	 * Page Location click handler
	 */
	$(document).on('click', 'input[name="page_location"]', function (e) {
		const $this = $(this);
		const $parent = $this.closest('.field');
		const $input = $('input[type="number"], input[type="text"]', $parent);
		const val = $input.val();

		// Clear all values then reset the selected value
		$parent.closest('.page-location').find('input[type="number"], input[type="text"]').val('');
		$input.val(val).focus();
	});

	/**
	 * Page Timing click handler
	 */
	$(document).on('click', 'input[name="page_timing"]', function (e) {
		const $this = $(this);
		const $parent = $this.closest('.field');
		const $input = $('input[type="number"]', $parent);
		const val = $input.val();

		// Clear all values then reset the selected value
		$parent.closest('.page-timing').find('input[type="number"]').val('');
		$input.val(val).focus();
	});

	/**
	 * Refresh List click handler
	 * This will come from the form meta data if there is a select list that needs to be refreshed
	 */
	$(document).on('click', '.btn-refresh-list', function (e) {
		e.preventDefault();
		const $this = $(this);
		const $parent = $this.closest('.field');
		const $select = $('select', $parent);
		const handler = $select.data('handler');

		if (_processing) return;
		_processing = true;

		const params = {
			'action': 'handle_meta_refresh_list',
			'nonce': AdminAddFormAjax.nonce,
			'handler': handler
		}

		opt_bud.utils.ajax(AdminAddFormAjax.ajax_url, 'POST', params, (error, response) => {
			_processing = false;
			response = response || {};
			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			opt_bud.utils.append_select_options($select, response.options);
		});
	});

	/**
	 * Send test email
	 */
	$(document).on('click', '.send-test', function (e) {
		e.preventDefault();
		const $email = $(this).parent().find('input[type="email"]');

		if (_processing) return;
		_processing = true;

		const params = {
			'action': 'handle_send_test_email',
			'nonce': AdminAddFormAjax.nonce,
			'email': $email.val(),
			'content': tinyMCE.get('send_email_message').getContent()
		}

		opt_bud.utils.ajax(AdminAddFormAjax.ajax_url, 'POST', params, (error, response) => {
			_processing = false;
			response = response || {};
			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			} else {
				opt_bud.utils.showAdminNotice('Email sent successfully. If you do not recieve the test email soon, verify that your WordPress SMTP settings are correct.', 'success');
			}
		});
	});

	/**
	 * Form cancel
	 */
	$(document).on('click', '.cancel-form', function (e) {
		e.preventDefault();
		window.location = $(this).data('location');
	});

	/**
	 * Form submit handler
	 */
	$(document).on('submit', '.form-add-new', function (e) {
		// Prevent multiple clicks
		if (_processing) {
			e.preventDefault();
			return false;
		}
		_processing = true;

		const $this = $(this);

		// Update fields
		$('input[name="header"]').val($.trim($('.email-container .header').text()));
		$('input[name="body"]').val($.trim($('.email-container .body').text()));
		$('input[name="button"]').val($.trim($('.email-container .button span').text()));
		$('input[name="disclaimer"]').val($.trim($('.email-container .disclaimer').text()));

		let $field = null;
		let $parent = null;
		let $input = null;
		let val = '';

		// Handle form error
		const handle_error = (tab, field, error) => {
			const $tab = $(`ul.tabs li[data-section="${tab}"]`);
			$tab.trigger('click');

			opt_bud.utils.showAdminNotice(error, 'error');

			if (field) field.focus();
			_processing = false;
		}

		// --------------------------------------
		//	Validate Styles Section
		// --------------------------------------
		// Validate Image - only if template contains an image
		$field = $('input[name="image_id"]', $this);
		if (_form_template_id !== 1 && _form_template_id !== 6) {
			val = $field.val();
			if (Number(val) === 0 || val === '') {
				handle_error('styles', $field, 'Please select an image.');
				return false;
			}
		} else {
			// Default to 0 if this template has NO image
			$field.val(0);
		}

		// Validate Success Message
		$field = $('textarea[name="success"]', $this);
		if ($.trim($field.val()) === '') {
			handle_error('styles', $field, 'Success Message is a required field.');
			return false;
		}

		// --------------------------------------
		//	Validate Location Section
		// --------------------------------------
		// Validate Page Type
		$field = $('input[name="page_type"]', $this);
		if (!$field.is(':checked')) {
			handle_error('location', $field.get(0), 'Page Type is a required field.');
			return false;
		}

		// Validate Popup Location - Only validate if floating box
		if (_form_type_id === 2) {
			$field = $('input[name="form_location"]', $this);
			if (!$field.is(':checked')) {
				handle_error('location', $field.get(0), 'Form Location is a required field.');
				return false;
			}
		}

		// Validate Page Location - Only validate if inline form
		val = '';
		if (_form_type_id === 1) {
			$field = $('input[name="page_location"]', $this);
			$parent = $field.filter(':checked').closest('label');
			$input = $('input[type="text"], input[type="number"]', $parent);
			val = $.trim($input.val());
			if (!$field.is(':checked')) {
				handle_error('location', $field.get(0), 'Page Location is a required field.');
				return false;
			} else {
				if (val === '') {
					handle_error('location', $input, 'Page Location must contain a value.');
					return false;
				}
			}
		}
		$('input[name="page_location_value"]').val(val);

		// Validate Form Timing - Only validate if NOT inline form
		val = '';
		if (_form_type_id !== 1) {
			$field = $('input[name="page_timing"]', $this);
			$parent = $field.filter(':checked').closest('label');
			$input = $('input[type="number"]', $parent);
			val = $.trim($input.val());
			if (!$field.is(':checked')) {
				handle_error('location', $field.get(0), 'Page Timing is a required field.');
				return false;
			} else {
				if (val === '' || isNaN(parseInt(val))) {
					handle_error('location', $input, 'Page Timing must contain a valid number.');
					return false;
				}
			}
		}
		$('input[name="page_timing_value"]').val(val);

		// --------------------------------------
		//	Validate Email Settings Section
		// --------------------------------------
		// Validate Custom Message
		$field = $('input[name="send_email"]', $this);
		$subject_field = $('input[name="send_email_subject"]');
		$message_field = $('textarea[name="send_email_message"]');

		let editor = opt_bud.wp_editor.get_editor('send_email_message');
		let content = opt_bud.wp_editor.get_content('send_email_message');
		/* //if quicktags = true then we must check if the textarea is being used
		if (!editor || editor.hidden) {
			editor = $(editor.container).parent().find('textarea');
			content = editor.val();
		}*/

		if ($field.is(':checked')) {
			// Must contain a subject and message
			if ($.trim($subject_field.val()) === '') {
				handle_error('email-settings', $subject_field, 'You must specify a custom email subject.');
				return false;
			}
			if ($.trim(content) === '') {
				handle_error('email-settings', editor, 'You must specify a custom email message.');
				return false;
			}
		} else {
			if ($.trim($subject_field.val()) !== '' || $.trim(content) !== '') {
				handle_error('email-settings', $field, 'Your custom message will not be sent unless you check the box next to: "Send custom email to subscriber?"');
				return false;
			}
		}

		$('input[name="nonce"]').val(AdminAddFormAjax.nonce);

		// Place all the selected categories into an array then add them to the input field
		const categories = $('ul.categories li.selected').map((i, el) => {
			return $(el).data('term-id');
		}).get();
		$('input[name="target_categories"]', $this).val(categories.join(','));

		opt_bud.utils.loader.show();

		return true;
	});

	/**
	 * This will update the associated fields with the selected form type and template
	 */
	const update_form_details = () => {
		$('.page-location, .form-location, .page-timing').hide();

		// If inline form type...
		if (_form_type_id == 1) {
			$('.page-location').show();
			$('.section-inview').show();

			// floating_box form type
		} else if (_form_type_id == 2) {
			$('.form-location').show();
		}

		if (_form_type_id != 1) {
			$('.page-timing').show();
			$('.section-inview').hide();
			$('input[name="inview"]').attr('checked', false);
		}

		// Update the selected icons in the details section
		const $selectedType = $('.section-choose-type .icon.selected');
		const $selectedTemplate = $('.section-choose-template .icon.selected');

		$('input[name="type_id"]').val(_form_type_id);
		$('.section-form-selection .form-type img').attr('src', $('img', $selectedType).attr('src'));
		$('.section-form-selection .form-type .name').text($('.title', $selectedType).text());

		$('input[name="template_id"]').val(_form_template_id);
		$('.section-form-selection .form-template img').attr('src', $('img', $selectedTemplate).attr('src'));
		$('.section-form-selection .form-template .name').text($('.title', $selectedTemplate).text());
	}

	/**
	 * Loads the css for the wp-editor(s)
	 */
	const init_editor_styles = () => {
		const params = {
			'action': 'handle_load_content',
			'nonce': AdminAddFormAjax.nonce,
			'template': 'css-wp-editor'
		}

		// Load the styles and add it to the editor(s)
		opt_bud.utils.ajax(AdminAddFormAjax.ajax_url, 'POST', params, (error, response) => {
			if (response && response !== 'not found') {
				response = response.replace(/\s{2,}/g, '');
				opt_bud.wp_editor.add_styles(response);
			} else {
				console.log('wp-editor styles could not be loaded.', error);
			}
		}, true);
	}

	/**
	 * Various triggers used to initialize the form on load
	 */
	const init = () => {
		// If the form_id is present then we must be in edit mode
		if (Number($('input[name="id"]').val()) > 0) {
			_edit_mode = true;
		} else {
			$('.section-choose-type').show();
		}

		// Prepopulates the selected categories if any
		$('ul.categories li.selected').trigger('click');

		// Update type_id and template_id selections
		const type_id = $('input[name="type_id"]').val();
		const template_id = $('input[name="template_id"]').val();
		$(`.icon[data-type_id="${type_id}"`).trigger('click');
		$(`.icon[data-template_id="${template_id}"`).trigger('click');

		update_form_details();

		// Request new nonce every hour and on tab change to ensure the form submits
		opt_bud.utils.setup_nonce_renewal('handle_add_form_new_nonce', AdminAddFormAjax.ajax_url, nonce => {
			if (nonce) {
				AdminAddFormAjax.nonce = nonce;
			}
		});

		// Initialize the editor css
		init_editor_styles();
	}

	init();
});