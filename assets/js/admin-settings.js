jQuery(document).ready(function ($) {
	// Prevents multiple submissions
	let _processing = false;

	// Set to true if we have advanced to the last step
	let _setup_complete = false;

	/**
	 * Listen for the click event on each email provider
	 */
	$(document).on('click', '.provider:not(.disabled, .connection)', function (e) {
		e.preventDefault();

		// Prevent multiple clicks
		if (_processing) return;
		_processing = true;

		const $this = $(this);
		const provider = $this.data('key');

		$('.provider-settings-container').empty();

		$('input[name="provider"]').val(provider);

		// Fade all other non selected providers
		$this.siblings().removeClass('active').addClass('fade');
		$this.addClass('active').removeClass('fade');

		const params = {
			'action': 'handle_settings_select_provider',
			'nonce': AdminSetupAjax.nonce,
			'provider': provider
		}

		opt_bud.utils.ajax(AdminSetupAjax.ajax_url, 'POST', params, selectProviderCompletionHandler);
	});

	/**
	 * Ajax handler when selecting a provider to get their settings
	 * This will iterate over all the providers settings and display them.
	 * 
	 * @param {string} error 
	 * @param {JSON} response 
	 */
	const selectProviderCompletionHandler = (error, response) => {
		_processing = false;

		const provider = $('.provider.active').data('key');
		const provider_name = $('.provider.active .name').text();

		if (error) {
			opt_bud.utils.showAdminNotice(error, 'error');
		}
		if (response.success) {
			const { fields, oauth, cron_disabled, account_connected, details } = response;

			// Loop over fields and add them
			let $container = $('.provider-settings-container');
			let warning = '';

			// If this provider required oauth and cron is disabled, show a warning
			if (oauth && cron_disabled) {
				warning = `<div class="warning"><strong>Warning:</strong> WordPress cron jobs must be enabled for ${provider_name} to receive emails. The access token required for authentication must be continually refreshed using a scheduled cron job. In your wp-config.php file, comment out the following line: define('DISABLE_WP_CRON', true);<br/><br/><strong>Note:</strong> If you decide to connect this provider and do not enable cron, all emails will be captured in wordpress and you can view/export them in our Reports section.</div>`;
			}

			$container.append(`<p><strong>${provider_name} Settings:</strong>${warning}</p>`);

			$container.append(`<input type="hidden" name="provider" value="${provider}"/>`);

			let save_button = !fields.length || account_connected ? 'Select Provider' : 'Save Provider';

			// If this provider has settings fields, display them
			if (fields.length) {
				for (let i = 0; i < fields.length; i++) {
					const { key, name, desc, type, value, required, handler, options } = fields[i];
					const requiredEl = required ? '<span class="required">*</span>' : '';
					const requiredLbl = required ? 'required' : '';
					const descEl = desc !== '' ? `<span class="desc">${desc}</span>` : '';

					// All fields are hidden if oath
					if (type === 'hidden') {
						$container.append(`<input type="hidden" id="${key}" name="${key}" value="${value}" ${requiredLbl} />`);
					} else if (type === 'checkbox') {
						const checked = value !== '' ? 'checked="checked"' : '';
						$container.append(`<div class="field"><label for="${key}"><input type="${type}" id="${key}" name="${key}" value="1" placeholder="${name}" ${requiredLbl} ${checked}/> ${name} ${requiredEl} ${descEl}</label></div>`);
					} else if (type === 'select') {
						$container.append(`<div class="field"><label for="${key}">${name}: ${requiredEl} ${descEl}</label><div style="display: flex; gap: 10px;"><select id="${key}" name="${key}" data-handler="${handler}" ${requiredLbl}></select><button type="button" class="button button-small btn-refresh-list">Refresh List</button></div>`)
						opt_bud.utils.append_select_options($(`select[name="${key}"]`, $container), options);

					} else {
						$container.append(`<div class="field"><label for="${key}">${name}: ${requiredEl} ${descEl}</label><input type="${type}" id="${key}" name="${key}" value="${value}" placeholder="${name}" ${requiredLbl}/></div>`);
					}
				}

				/**
				 * Click handler for the refresh list button used to populate the select list if any
				 */
				$('.btn-refresh-list', $container).click(function (e) {
					e.preventDefault();

					const $this = $(this);
					const $form = $this.closest('form');
					const $parent = $this.closest('.field');
					const $select = $('select', $parent);
					const handler = $select.data('handler');

					if (_processing) return;
					_processing = true;

					const params = {
						'action': 'handle_settings_refresh_list',
						'nonce': AdminSetupAjax.nonce,
						'provider': provider,
						'handler': handler
					}

					// Send all provider fields along with request
					$('input', $form).each((index, element) => {
						if (element.name.includes(provider)) {
							params[element.name] = element.value.trim();
						}
					})

					// make ajax call to remove account and then refresh the settings
					opt_bud.utils.ajax(AdminSetupAjax.ajax_url, 'POST', params, (error, response) => {
						_processing = false;
						response = response || {};
						if (error) {
							opt_bud.utils.showAdminNotice(error, 'error');
						}

						opt_bud.utils.append_select_options($select, response.options);
					});
				});

				// If this provider requires authentication (auth)...
				if (oauth) {
					if (!account_connected) {
						// Connect Provider
						$container.append('<p><button type="button" class="button btn-connect">Connect Account</button></p>');
						$('.btn-connect').click(function (e) {
							e.preventDefault;

							const provider = $('.provider.active').data('key');
							const client_id = $('input[name$="client_id"]').val().trim();
							const client_secret = $('input[name$="client_secret"]').val().trim();
							const redirect_uri = $('input[name="redirect_uri"]').val();

							if (client_id === '' || client_secret === '') {
								alert('Client Id and Client Secret are required fields.');
								return;
							}
							window.open(`${redirect_uri}&provider=${provider}&client_id=${client_id}&client_secret=${client_secret}`, "authPopup", "width=600,height=600");
						});
					}
				}

				// This provider has no settings
			} else {
				const message = details ? details : 'There are no settings for this provider';
				$container.append(`<div>${message}</div>`);
			}

			if (account_connected) {
				// Disconnect Provider
				$container.append(`<p class="provider-settings-actions"><button type="button" class="button btn-disconnect">Disconnect Provider</button><button type="submit" class="button button-primary btn-save-settings">${save_button}</button></p>`);
				$('.btn-disconnect').click(function (e) {
					e.preventDefault;

					// Show confirmation dialog
					const id = 'disconnect_provider';
					opt_bud.utils.confirm('', `Are you sure? <p>Disconnecting from your ${provider_name} account will remove all associated provider specific fields in your forms.</p>`, 'Disconnect Provider', (confirmed) => {
						if (confirmed) {
							_processing = true;
							const params = {
								'action': 'handle_settings_disconnect_provider',
								'nonce': AdminSetupAjax.nonce,
								'provider': provider
							}

							// make ajax call to remove account and then refresh the settings
							opt_bud.utils.ajax(AdminSetupAjax.ajax_url, 'POST', params, disconnectProviderCompletionHandler);
						}
					});
				});
			} else {
				$container.append(`<p><button type="submit" class="button button-primary btn-save-settings" ${oauth ? 'disabled' : ''}>${save_button}</button></p>`);
			}

			$('.field input', $container).first().focus();
		}
	}

	/**
	 * Ajax handler for when the user is disconnecting a provider
	 * 
	 * @param {string} error 
	 * @param {JSON} resonse 
	 */
	const disconnectProviderCompletionHandler = (error, response) => {
		_processing = false;

		if (error) {
			opt_bud.utils.showAdminNotice(error, 'error');
		}

		$('.provider.active').trigger('click');
	}

	/**
	 * Handle form submission for all settings ( refreshes the page )
	 */
	$(document).on('submit', 'section-select-provider form', function (e) {
		// Prevent multiple clicks
		if (_processing) {
			e.preventDefault();
			return false;
		}
		_processing = true;

		opt_bud.utils.loader.show();
	});

	/**
	 * Update Email Provider Settings click handler
	 * This is simply an alternate to clicking on step 1 in the progress bar
	 */
	$(document).on('click', '.update-provider-settings', function (e) {
		e.preventDefault();
		$('.section-select-provider').show();
		$('.section-forms').hide();
	});

	const load_forms = ($page) => {
		// Empty the results
		const $results = $('.forms .results').empty();
		$results.append(`
				<table>
					<tr>
						<th>Title:</th>
						<th>Type:</th>
						<th>Template:</th>
						<th>Date:</th>
						<th>Actions:</th>
					</tr>
				</table>`);

		const $table = $('table', $results);

		const onCompleteHandler = (error, response) => {
			hasResults = false;

			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			// Loop over the results and add them to the table
			if (response.results) {
				const results = response.results;

				for (let key in results) {
					hasResults = true;
					const result = results[key];
					$table.append(`
					<tr data-id="${result.id}">
						<td>${result.header}${result.deactivate == 1 ? '<span class="deactivated">deactivated</span>' : ''}</td>
						<td>${result.type_name}</td>
						<td>${result.template_name}</td>
						<td>${result.date}</td>
						<td><button type="button" class="button button-small edit-existing">Edit</button> <button type="button" class="button button-small button-danger delete-existing">Delete</button></td>
					</tr>`);
				};

				// Handle pagination
				opt_bud.utils.paginate(response.pagination, $results, (selected_page) => {
					if (page == selected_page) return false;

					load_forms(selected_page);
				});
			}

			if (!hasResults) {
				$table.append('<tr><td colspan="5">No results found</td></tr>');
			}
		}

		const params = {
			'action': 'handle_settings_list_forms',
			'nonce': AdminSetupAjax.nonce
		}
		opt_bud.utils.ajax(AdminSetupAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * Handle edit and delete for all saved forms
	 */
	$(document).on('submit', '.section-forms form', function (e) {
		// Prevent multiple clicks
		if (_processing) {
			e.preventDefault();
			return false;
		}
		_processing = true;

		opt_bud.utils.loader.show();
	});

	/**
	 * Step 4 delete button click event
	 */
	$(document).on('click', '.section-forms button.delete-existing', function (e) {
		const $this = $(this);
		const $parent = $this.closest('tr');
		const id = Number($parent.data('id'));

		// Add a confirmation before deleting
		opt_bud.utils.confirm('', 'Are you sure you want to delete this form?', 'Yes, delete it!', function (confirmed) {
			if (confirmed) {
				$('.section-forms form input[name="id"]').val(id);
				$('.section-forms form').trigger('submit');
			}
		});
	});

	/**
	 * Step 4 edit button click event
	 */
	$(document).on('click', '.section-forms button.edit-existing', function (e) {
		const $this = $(this);
		const $parent = $this.closest('tr');
		const id = Number($parent.data('id'));

		window.location.href = `admin.php?page=opt_bud_add_new&id=${id}`;
	});

	/**
	 * Oauth callback handler - must be a public function in the current window object
	 * This will iterate over the data to set the required fields that make a successfull connection
	 * 
	 * @param {JSON} data 
	 * @returns 
	 */
	window['onAuthCodeRecieved'] = (data) => {
		if (!Object.keys(data).length) return;

		if (data && data.has_invalid_credentials) {
			alert('Client Id and/or Client Secret are incorrect.');
		}

		// Loop over all the key/value pairs and set the associated field values
		for (let key of Object.keys(data)) {
			document.querySelector(`input[name$="${key}"]`).value = data[key];
		}
		document.querySelector('.btn-save-settings').removeAttribute('disabled');
		document.querySelector('.btn-connect').classList.add('hidden');

		// If this provider has a select list to handle...
		$('.btn-refresh-list').trigger('click');
	}

	/**
	 * Various triggers used to initialize the form on load
	 */
	const init = () => {
		// Selects the previously chosen provider so that the details are loaded initially
		const provider = $('.values').data('provider');
		if (provider) {
			const $provider = $(`.provider[data-key="${provider}"]`);
			$provider.trigger('click');
			$('.provider.connection img').attr('src', $('img', $provider).attr('src'));

			// Loads all the forms
			load_forms(1);
		}

		// Request new nonce every hour and on tab change to ensure the form submits
		opt_bud.utils.setup_nonce_renewal('handle_settings_new_nonce', AdminSetupAjax.ajax_url, nonce => {
			if (nonce) {
				AdminSetupAjax.nonce = nonce;
			}
		});
	}

	init();
});