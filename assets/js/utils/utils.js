/**
 * Utility closure for shared admin settings
 * 
 * @param {object} $ reference to jQuery
 */
opt_bud = window['opt_bud'] || {};
opt_bud.utils = (($) => {
	// Timeout handler for the notices so that we can clear them when needed
	let _noticeTimeout = null;

	// Timeout handler for the loader animation
	let _loaderTimeout = null;

	/**
	 * Initializes all slide-toggle classes
	 */
	$(document).on('click', '.slide-toggle .title', function (e) {
		const $this = $(this);
		const $parent = $this.closest('.slide-toggle');
		$parent.toggleClass('show');
		$('.body', $parent).slideToggle(200);
	});

	/**
	 * Displays the WordPress admin notice
	 * 
	 * @param {string} message 
	 * @param {string} type 
	 * @param {int} length
	 */
	const _showAdminNotice = (message, type, length) => {
		clearTimeout(_noticeTimeout);
		$('.notice').remove();

		const dismissButton = '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
		const notice = `<div class="notice notice-${type} is-dismissible">${dismissButton}<p>${message}</p></div>`;

		// Append our notice to the <div> containing all notices
		$('h1:first-child').after(notice);

		// Make the notice dismissible
		$('.notice.is-dismissible').on('click', '.notice-dismiss', function () {
			$(this).parent().fadeOut('slow');
			clearTimeout(_noticeTimeout);
		});

		// Scroll to top
		const $body = document.querySelector('#wpbody');
		if ($body) $body.scrollIntoView();

		if (length !== 0) {
			_noticeTimeout = setTimeout(function () {
				$('.notice').fadeOut('slow');
			}, length ? length : 10000);
		}
	}

	/**
	 * Removes any visible notice
	 */
	const _removeAdminNotice = () => {
		clearTimeout(_noticeTimeout);
		$('.notice').remove();
	}

	/**
	 * Returns a more descriptive value
	 * 
	 * @param {string} area 
	 * @returns {string}
	 */
	const _get_area_pretty_name = (area) => {
		switch (area) {
			case 'all':
				return 'All Pages';
			case 'post':
				return 'Only Post Pages';
			case 'category':
				return 'Only Category Pages';
			default:
				return area;
		}
	}

	/**
	 * Returns a more descriptive value
	 * 
	 * @param {string} type 
	 * @returns {string}
	 */
	const _get_type_pretty_name = (type) => {
		switch (type) {
			case 'before_paragraph':
				return 'Before Paragraph';
			case 'after_paragraph':
				return 'After Paragraph';
			case 'before_element':
				return 'Before Element';
			case 'after_element':
				return 'After Element';
			case 'popup_time_delay':
				return 'Popup Time-Delayed';
			case 'popup_scroll_delay':
				return 'Popup Scroll-Delayed';
			case 'popup_exit_intent':
				return 'Popup Exit-Intent';
			default:
				return type;
		}
	}

	/**
	 * Returns the form template to be displayed
	 * 
	 * @param {string} url 
	 * @param {JSON} data 
	 * @param {jQuery Element} $context 
	 * @param {function} callback 
	 */
	const _show_preview = (url, atts, $context, callback) => {
		_loader.show($context);

		const data = new URLSearchParams();
		for (const key in atts) {
			data.append(key, atts[key]);
		}

		fetch(url, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			}
		})
			.then(response => response.text())
			.then(response => {
				_loader.remove($context);

				if (response !== false) {
					let html = `<section class="preview-form">
						<div class="header">
							<button type="button" class="button button-small btn-desktop selected">Desktop View</button>
							<button type="button" class="button button-small btn-mobile">Mobile View</button>
						</div>
						<div class="preview-wrapper">
							<div class="show-preview desktop">${response}</div>
						</div>
					</section>`;

					opt_bud.modal.show('preview-form-dialog', { title: 'Preview Form', closeButton: true, body: html });
					$('#preview-form-dialog .modal-body .show-preview').show();

					// Button click handler
					$('.btn-desktop').on('click', function (e) {
						$('.show-preview').removeClass('mobile').addClass('desktop');
						$(this).closest('.header').find('button').removeClass('selected');
						$(this).addClass('selected');
					});
					$('.btn-mobile').on('click', function (e) {
						$('.show-preview').removeClass('desktop').addClass('mobile');
						$(this).closest('.header').find('button').removeClass('selected');
						$(this).addClass('selected');
					});
				}

				if (callback) callback(response !== false);
			})
			.catch(error => {
				console.log(error);
				_loader.remove($context);

				if (callback) callback(false);
			});
	}

	/**
	 * 
	 * @param {string} title 
	 * @param {string} body 
	 * @param {string} buttonText 
	 * @param {function} callback 
	 */
	const _confirm = (title, body, buttonText, callback) => {
		const id = 'modal_confirmation';
		opt_bud.modal.show(id, { class: 'info', title: title, body: body, closeButton: true, actionButtonText: buttonText });
		// Yes handler
		$('#' + id + ' .modal-footer .button-primary').click(function (e) {
			opt_bud.modal.hide(id);
			callback(true);
		});
		// Cancel handler
		$('#' + id + ' .modal-footer .btn-close, .modal-header button.close').click(function (e) {
			opt_bud.modal.hide(id);
			callback(false);
		});
	}

	/**
	 * Adds and removes the spinner div to the specified element
	 */
	const _loader = {
		/**
		 * Appends the loader(spinner) animation to the body
		 */
		show: () => {
			$context = $('body');
			// Only show 1 loader at a time
			clearTimeout(_loaderTimeout);
			_loader.remove($context);

			$('<div class="loader-wrapper"><div class="loader"><div class="spinner"></div></div></div>').prependTo($context);

			// Clear the time out
			clearTimeout(_loaderTimeout);
			// Hide after 30 seconds
			_loaderTimeout = setTimeout(function () {
				_loader.remove($context);
			}, 30000);

		},

		/**
		 * Adds the loader to the specified context
		 * 
		 * @param {jQuery Element} $context 
		 */
		show_in: ($context) => {
			$context.append($('<div class="loader-wrapper context"><div class="loader"><div class="spinner"></div></div></div>'));
		},

		/**
		 * Removes the loader from the body
		 */
		remove: () => {
			$('.loader-wrapper:not(.context)', $('body')).remove();
		},

		/**
		 * Removes the loader from the specified context
		 * 
		 * @param {jQuery Element} $context 
		 */
		remove_from: ($context) => {
			$('.loader-wrapper.context', $context).remove();
		}
	}

	/**
	 * Makes an ajax request to the specified url and returns the response
	 * 
	 * @param {string} url 
	 * @param {string} method
	 * @param {json} data 
	 * @param {function} callback 
	 * @param {boolean} ignoreJSON 
	 */
	const _ajax = (url, method, params, callback, ignoreJSON) => {
		const errorDefault = 'Unable to process your request, please try again';

		// Remove any admin notices
		_removeAdminNotice();

		const data = new URLSearchParams();
		if (params) {
			for (const key in params) {
				data.append(key, params[key]);
			}
		}

		_loader.show();

		fetch(url, {
			method: method,
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			}
		})
			.then(response => {
				if (ignoreJSON === true) return response.text();
				return response.json();
			})
			.then(response => {
				let error = '';

				if (ignoreJSON !== true && !response.success) {
					error = response.error || errorDefault;
				}

				if (callback) callback(error, response)

				_loader.remove();
			})
			.catch(error => {
				console.log(error);
				if (callback) callback(errorDefault, null);

				_loader.remove();
			});
	}

	/**
	 * Requests a new nonce every hour and on tab change
	 * 
	 * @param {string} action 
	 * @param {string} url 
	 * @param {function} callback 
	 */
	const _setup_nonce_renewal = (action, url, callback) => {
		// Make the request to get the new nonce
		const refreshNonce = () => {
			const data = new URLSearchParams();
			data.append('action', action);

			fetch(url, {
				method: 'POST',
				body: data,
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
				}
			})
				.then(response => response.json())
				.then(response => {
					let nonce = null;
					if (response && response.nonce) {
						nonce = response.nonce;
					}
					callback(nonce);
				}).catch(error => {
					console.log(error);
					callback(null);
				});
		}

		// Request new nonce every hour to ensure the form submits
		setInterval(function () {
			refreshNonce();
		}, 3600000);

		// Request a new nonce when this tab becomes active
		document.addEventListener('visibilitychange', function () {
			if (!document.hidden) {
				// The tab has become active, let's refresh the nonce
				refreshNonce();
			}
		}, false);
	}

	/**
	 * Formats date to Mon day, year
	 * Date must be in the following format: 2023-12-01, 12/01/2023
	 * 
	 * @param {string} date 
	 * @returns {string} formatted date
	 */
	const _pretty_date = (date) => {
		// Turn date to the following format: month/day/year
		date = date.replace(/^(\d{4})-(\d{1,2})-(\d{1,2})$/, "$2/$3/$1");

		// If the date is NOT in the following format: month/day/year, return date
		if (!/^\d{1,2}(-|\/)\d{1,2}(-|\/)\d{4}$/.test(date)) {
			return date;
		}
		// invalid date if 2016/12/01 - must but 12/01/2016
		date = date.replace('-', '/');
		const d = new Date(date);
		const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		const month = months[d.getMonth()];
		month = month.substring(0, 3);
		return `${month} ${d.getDate()}, ${d.getFullYear()}`;
	}

	/**
	 * This will append all the options provided to the select element
	 * 
	 * @param {jQuery Element} $select - Select element to append options list to
	 * @param {array} options - List of select options to append
	 * @param {bool} append - True if we are to append items to the list (default = false|null)
	 */
	const _append_select_options = ($select, options, append) => {
		if (options && options.length) {
			if (!append) $select.empty();

			for (let i = 0; i < options.length; i++) {
				const { name, value, selected } = options[i];
				$select.append(`<option value="${value}"${selected ? 'selected' : ''}>${name}</option>`);
			}
		}
	}

	/**
	 * Adds pagination links to any result set
	 * 
	 * @param {int} current_page 
	 * @param {int} total_pages 
	 * @param {jQuery Element} $container 
	 * @param {function} callback 
	 */
	const _paginate = (pagination, $container, callback) => {
		pagination = pagination || { current_page: 1, total_items: 1, total_pages: 1, items_per_page: 10 };
		let { current_page, total_items, total_pages, items_per_page } = pagination;

		current_page = current_page < 1 ? 1 : current_page;
		total_items = total_items < 1 ? 1 : total_items;
		total_pages = total_pages < 1 ? 1 : total_pages;

		//const msg = `${((current_page - 1) * items_per_page) + 1} - ${total_items < items_per_page ? total_items : current_page * items_per_page} of ${total_items} record(s)`;
		const msg = `Page ${current_page} of ${total_pages} (${total_items} record${total_items > 1 ? 's' : ''})`;

		const $paginationContainer = $(`<div class="pagination-container"><div class="pagination"></div><div class="desc">${msg}</div></div>`);
		const $pagination = $('.pagination', $paginationContainer);
		const range = 2; // Number of page links to show before and after the current page
		const showFirst = current_page > range + 1;
		const showLast = current_page < total_pages - range;

		$pagination.append(`<a href="#" data-page="1" class="${current_page === 1 ? 'disabled' : ''}">First</a>`);
		$pagination.append(`<a href="#" data-page="${current_page - 1}" class="${current_page === 1 ? 'disabled' : ''}">&#8592; Prev</a>`);

		// Ensures only 2 
		for (let i = 1; i <= total_pages; i++) {
			if ((i === current_page) || i >= current_page - range && i <= current_page + range) {
				let link = $(`<a href="#" data-page="${i}">${i}</a>`);
				if (i === current_page) {
					link.addClass('current-page');
				}
				$pagination.append(link);
			}
		}

		$pagination.append(`<a href="#" data-page="${current_page + 1}" class="${current_page === total_pages ? 'disabled' : ''}">Next &#8594;</a>`);
		$pagination.append(`<a href="#" data-page="${total_pages}" class="${current_page === total_pages ? 'disabled' : ''}">Last</a>`);

		$container.append($paginationContainer);
		$('.pagination a').click(function (e) {
			e.preventDefault();
			const page = $(this).data('page') || 1;
			callback(page);
		});
	}

	/**
	 * Truncates the specified string by the specified length then appends ... to the end.
	 * 
	 * @param {string} str 
	 * @param {int} length 
	 * @returns 
	 */
	const _truncate = (str, length) => {
		if (str.length > length) {
			return `${str.slice(0, length)}...`;
		}
		return str;
	}

	return {
		showAdminNotice: _showAdminNotice,
		removeAdminNotice: _removeAdminNotice,
		get_area_pretty_name: _get_area_pretty_name,
		get_type_pretty_name: _get_type_pretty_name,
		show_preview: _show_preview,
		confirm: _confirm,
		loader: _loader,
		ajax: _ajax,
		setup_nonce_renewal: _setup_nonce_renewal,
		pretty_date: _pretty_date,
		append_select_options: _append_select_options,
		paginate: _paginate,
		truncate: _truncate
	}
})(jQuery);