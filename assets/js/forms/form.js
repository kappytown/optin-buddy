/**
 * This file handles all the front-end form functionality
 * needed to display and submit all email subscription forms.
 */
ocument.addEventListener('DOMContentLoaded', function () {
	// Prevents multiple submissions
	let _processing = 0;

	// Used as an identifier for each form
	let _counter = 0;

	// Set to true once the scroll criteria (scroll percentage) has been met
	let _has_met_scroll_criteria = false;

	// We use local storage for saving forms that the user submits so that they don't resubmit them
	const _local_storage_key = 'optin_buddy_forms';
	const _local_storage_expiration = 1000 * 60 * 60 * 24 * 14; // 14 days

	/**
	 * This is a replacement for jQuery $(document).on(...)
	 * 
	 * @param {DOMElement} element 
	 * @param {string} type 
	 * @param {string} selector 
	 * @param {function} handler 
	 */
	const on = (element, type, selector, handler) => {
		element.addEventListener(type, (event) => {
			const el = event.target.closest(selector);
			if (el) {
				handler.call(el, event);
			}
		});
	}

	/**
	 * Shows the form
	 * 
	 * @param {string} type 
	 * @param {string} value 
	 * @param {DOMElement} container 
	 */
	const show_form = (type, value, container) => {
		container.style.display = 'block';

		if (type === 'fixed_top') {
			// Shows the form and by setting top to 0, it will have a slide down effect
			container.style.top = 0;

		} else if (type === 'floating_box') {
			// Shows the form and by setting left or right to 0, it will have a slide in effect
			if (value === 'bottom_left') {
				container.style.left = 0;
			} else {
				container.style.right = 0;
			}

		} else {
			// modal_popup, exit_intent
			container.style.margin = 0;
			opt_bud.modal.show('preview-form-dialog', { title: 'Preview Form', closeButton: true, body: container.outerHTML });
			container.remove();
		}
	}

	/**
	 * This will present the modal popup when the users mouse is close to the top of the page
	 * 
	 * @param {string} type 
	 * @param {string} value 
	 * @param {DOMElement} container 
	 */
	const handle_exit_intent = (type, value, container) => {
		function handleMouseout(e) {
			if (!e.toElement && !e.relatedTarget && e.clientY < 50) {
				show_form(type, value, container);
				document.removeEventListener('mouseout', handleMouseout);
			}
		}
		// Start the listener
		document.addEventListener('mouseout', handleMouseout);
	}

	/**
	 * Loop over all the hidden forms and place them in their designated locations
	 * Depending on the selector, a single form can be placed multiple times
	 */
	const init_forms = () => {
		document.querySelectorAll('.opt-bud-hidden').forEach(el => {
			const type = el.getAttribute('data-type');
			const value = el.getAttribute('data-value');
			const timing = el.getAttribute('data-timing');
			const timing_value = el.getAttribute('data-timing-value');

			el.querySelector('.opt-bud').setAttribute('data-id', _counter);

			switch (type) {
				case 'before_element':
					document.querySelectorAll(value).forEach(foundEl => {
						foundEl.insertAdjacentHTML('beforebegin', el.innerHTML);
					});
					break;

				case 'after_element':
					document.querySelectorAll(value).forEach(foundEl => {
						foundEl.insertAdjacentHTML('afterend', el.innerHTML);
					});
					break;

				case 'floating_box':
				case 'modal_popup':
				case 'fixed_top':
				case 'exit_intent':
					let form = el.querySelector('.opt-bud');
					let container = form;
					form.classList.add('popup');
					form.setAttribute('data-type', type);
					form.setAttribute('data-value', value);
					form.setAttribute('data-timing', timing);
					form.setAttribute('data-timing-value', timing_value);

					if (type == 'fixed_top') {
						form.style.display = 'block';
						// Insert in the beginning of the body
						document.body.insertAdjacentHTML('afterbegin', el.innerHTML);
						// The email container will do the css animation
						container = document.body.querySelector(`.opt-bud[data-id="${_counter}"] .email-container`);

						// Set position to relative so it pushes down the content but only when the css animation completes
						container.addEventListener('transitionend', () => {
							setTimeout(function () {
								container.style.position = 'relative';
							}, 100);
						}, { once: true });

					} else if (type === 'floating_box') {
						form.style.display = 'block';
						// Apply the floating_box styling
						form.classList.add('floating_box');
						// value = bottom_right, bottom_left
						form.classList.add(value);
						// Insert in the beginning of the body
						document.body.insertAdjacentHTML('afterbegin', el.innerHTML);
						// The form will do the css animation
						container = document.body.querySelector(`.opt-bud[data-id="${_counter}"]`);

					} else {
						// Insert the inner content before the element that we will be removing
						el.insertAdjacentHTML('beforebegin', el.innerHTML);
						container = document.querySelector(`.opt-bud[data-id="${_counter}"]`);
					}


					// Start timer
					if (timing === 'delay') {
						// Show the form after specified time has elapsed
						setTimeout(function (e) {
							if (type === 'exit_intent') {
								handle_exit_intent(type, value, container);
							} else {
								show_form(type, value, container);
							}
						}, Number(timing_value) * 1000);

					} else if (timing === 'scroll') {
						// Show the form after the user has scrolled past the specified amount
						function handleScroll() {
							const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
							const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
							const scrollAmount = Number(timing_value) / 100; // turns 50 into 0.5
							const scrolled = (winScroll / height) > scrollAmount;

							if (scrolled && !_has_met_scroll_criteria) {
								_has_met_scroll_criteria = true;
							}

							if (_has_met_scroll_criteria) {
								if (type === 'exit_intent') {
									handle_exit_intent(type, value, container);
								} else {
									show_form(type, value, container);
								}
								window.removeEventListener('scroll', handleScroll);
							}
						}
						// Start the listener after specified time has elapsed
						window.addEventListener('scroll', handleScroll);
					}
					break;
			}
			_counter++;

			// Cleanup
			el.remove();
		});

		// Make all forms visible
		document.querySelectorAll('.opt-bud:not(.popup)').forEach(el => {
			el.style.display = 'block';
		});
	};

	/**
	 * Close form click handler
	 */
	on(document, 'click', '.opt-bud .email-container .close', function (e) {
		e.preventDefault();

		e.target.closest('.opt-bud').style.display = 'none';
	});

	/**
	 * Every forms submit handler
	 */
	on(document, 'submit', '.email-form-form', function (e) {
		e.preventDefault();

		if (_processing) return;
		_processing = true;

		const _this = this;
		const name = _this.querySelector('input[name="name"]').value.trim();
		const email = _this.querySelector('input[name="email"]').value.trim();
		const _parent = _this.closest('.email-container');
		let params = {
			action: 'handle_form_submission_request',
			nonce: EmailFormAjax.nonce,
			email: email,
			name: name
		};

		if (email === '') {
			alert('Please enter a valid email address.');
			_processing = false;
			return;
		}

		// Loop over all the hidden fields and add them
		const hiddenFields = _this.querySelectorAll('input[type="hidden"]').forEach(element => {
			params[element.name] = element.value;
		});

		// Hide the form fields and show the loader
		_parent.classList.add('submitted');
		_this.querySelector('.loader').classList.remove('hidden');

		// We ignore the response and immediately display that thank you message.
		send_request(EmailFormAjax.ajax_url, 'POST', params, (success, response) => {
			_processing = false;

			// Hide the loader and display the message
			_parent.querySelector('.loader').classList.add('hidden');
			_this.querySelector('.message').classList.remove('hidden');

			// Save to local storage so we don't show the form again to the user
			let data = JSON.parse(window.localStorage.getItem(_local_storage_key)) || {};
			const id = _parent.getAttribute('data-id');
			const postID = _parent.querySelector('input[name="post_id"]').value;
			data[id] = { postID: postID, timestamp: (new Date()).getTime() };
			window.localStorage.setItem(_local_storage_key, JSON.stringify(data));
		});

		params = {
			action: 'handle_form_impression_request',
			nonce: EmailFormAjax.nonce,
			type: 'submissions',
			post_id: params.post_id,
			form_id: params.form_id
		}

		// Send impression
		send_request(EmailFormAjax.ajax_url, 'POST', params, (success, response) => {
			console.log('Form Viewed Impression', success, response);
		});
	});

	/**
	 * 
	 * @param {string} url 
	 * @param {string} method 
	 * @param {JSON} params 
	 * @param {function(success, response)} handler 
	 */
	const send_request = (url, method, params, handler) => {
		const urlParams = new URLSearchParams();
		for (key in params) {
			urlParams.append(key, params[key]);
		}

		fetch(EmailFormAjax.ajax_url, {
			method: method,
			body: urlParams,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			}
		})
			.then(response => response.json())
			.then(response => {
				if (handler) handler(true, response);
			}).catch(error => {
				console.log(error);
				if (handler) handler(false, error);
			});
	}

	/**
	 * Requests a new nonce
	 * 
	 * @param {function} callback 
	 */
	const requestNewNonce = (callback) => {
		send_request(EmailFormAjax.ajax_url, 'POST', { action: 'handle_new_nonce_request' }, (success, response) => {
			if (success && response && response.nonce) {
				EmailFormAjax.nonce = response.nonce;
				if (callback) callback(success);
			} else {
				console.log(`Request New Nonce Error: ${response}`);
				if (callback) callback(false);
			}
		});
	};

	/**
	 * Refreshes the nonce to prevent errors from outdated nonces
	 */
	const handle_nonce_refresher = () => {
		// Request a new nonce every hour to ensure our form is submitted
		setInterval(function () {
			requestNewNonce();
		}, 3600000);

		// Request a new nonce when this tab becomes active
		document.addEventListener('visibilitychange', function () {
			if (!document.hidden) {
				// The tab has become active, let's refresh the nonce
				requestNewNonce();
			}
		}, false);
	}

	/**
	 * This will show the thank you message for every form that has previously been submitted
	 * This will also remove all non-inline forms that have already been submitted
	 */
	const handle_submitted_forms = () => {
		let forms = JSON.parse(window.localStorage.getItem(_local_storage_key));
		if (forms) {
			// Get a list of all the form ids
			let ids = [];
			document.querySelectorAll('.email-container').forEach(element => {
				ids.push(element.getAttribute('data-id'));
			});

			if (ids.length) {
				const now = (new Date()).getTime();
				let requiresUpdate = false;

				// Loop over all the saved submitted forms and delete the ones that have expired
				for (let savedID in forms) {
					// Delete the entry from local storage if expired
					if (now - forms[savedID].timestamp > _local_storage_expiration) {
						delete forms[savedID];
						requiresUpdate = true;
					}
				}
				if (requiresUpdate) {
					window.localStorage.setItem(_local_storage_key, JSON.stringify(forms));
				}

				// Loop over all the form IDs on the page and check if they have previously been submitted
				ids.forEach(id => {
					// Has this form been submitted?
					if (forms[id]) {
						// Get the submitted form element
						let container = document.querySelector(`.email-container[data-id="${id}"]`);
						if (container) {
							// Get the post id to verify that this is the page it was submitted on
							const postID = container.querySelector('input[name="post_id"]').value;

							// Was this form submitted on this page?
							if (forms[id].postID == postID) {
								// If this is a popup form, then lets just remove it from the page to prevent it from showing
								const parent = container.closest('.opt-bud');
								if (parent && parent.classList.contains('popup')) {
									parent.remove();
									return;
								}
								// Show the thank you message
								container.classList.add('submitted');
								container.querySelector('.message').classList.remove('hidden');
								// Remove inview
								container.closest('.opt-bud').setAttribute('data-inview', "0");
							}
						}
					}
				});
			}
		}
	}

	/**
	 * This will remove all overlapping forms so that you don't have forms sitting on top of one another
	 */
	const remove_overlapping_forms = () => {
		const hashes = [];
		const submitted = [];
		const unsubmitted = [];

		// Get all opt-bud elements UNIQUE hashes
		const elements = document.querySelectorAll('.opt-bud').forEach(element => {
			// Get the hash (location+value)
			const hash = element.getAttribute('data-hash');
			if (hash) {
				if (hashes.indexOf(hash) === -1) hashes.push(hash);
			}
		});

		if (hashes.length) {
			// Loop over all the hashes and get the duplicates
			hashes.forEach(hash => {
				// Get all the forms with the same hash so we can locate and remove overlapping ones
				const submittedElements = document.querySelectorAll(`.opt-bud[data-hash="${hash}"] .email-container.submitted`);
				const unsubmittedElements = document.querySelectorAll(`.opt-bud[data-hash="${hash}"] .email-container:not(.submitted)`);

				// If this hash has already been submitted and there are ones that have not...
				// We hide these since there are other forms (in the same location) that have not
				if (submittedElements.length > 0 && unsubmittedElements.length > 0) {
					submitted.push(submittedElements);
				}

				// If there are more than one hash unsubmitted...
				// We hide all of these forms except 1 random form because we do NOT want to show forms on top of another (overlapping)
				if (unsubmittedElements.length > 1) {
					unsubmitted.push(unsubmittedElements);
				}

				// If we have multiple submitted and no unsubmitted then lets get all submitted but 1
				// This will leave 1 submitted and the others will be removed below
				if (submittedElements.length > 0 && unsubmittedElements.length == 0) {
					submitted.push([].slice.call(submittedElements, 1));
				}
			});

			// Remove all the submitted
			submitted.forEach(elementList => {
				elementList.forEach(element => {
					element.closest('.opt-bud').remove();
				});
			});

			// Remove all, except the selected randomly selected form, unsubmitted forms
			if (unsubmitted.length) {
				unsubmitted.forEach(elementList => {
					const list = Array.from(elementList);
					// Only if there are more than 1 forms
					if (list.length > 1) {
						const rand = Math.floor(Math.random() * list.length);
						list.splice(rand, 1);
						list.forEach(element => {
							element.closest('.opt-bud').remove();
						});
					}
				});
			}
		}
	}

	/**
	 * Applies the inview effect to all the specied elements that were found
	 */
	const apply_inview_effect = () => {
		// Only apply inview effect to first element: opt_bud.inview.init('.opt-bud', 1);
		const list = document.querySelectorAll('.opt-bud[data-inview="1"]');
		if (list.length) {
			// Only apply inview effect to the first element
			const inviewList = Array.from(list);		// Turn NodeList into an Array
			const inviewElement = inviewList.shift();	// Get the first item

			// Removes inview effect from all others
			if (inviewList.length) {
				inviewList.forEach(element => {
					element.removeAttribute('data-inview');
					element.removeAttribute('data-inview-id');
				});
			}

			// Initialize the inview effect
			opt_bud.inview.initWithElements([inviewElement]);
		}
	}

	/**
	 * Adds a form load impression for all visible unsubmitted forms on the page
	 * 
	 * @returns null
	 */
	const add_form_load_impression = () => {
		let params = {
			action: 'handle_form_impression_request',
			nonce: EmailFormAjax.nonce,
			type: 'loads',
			post_id: 0,
			form_id: []
		}

		// Get all the forms on the page that have not been submitted
		document.querySelectorAll('.opt-bud .email-container:not(.submitted)').forEach(element => {
			params.form_id.push(element.getAttribute('data-id'));

			if (params.post_id === 0) params.post_id = element.querySelector('input[name="post_id"]').value;
		});

		// Exit if no forms on this page
		if (params.form_id.length === 0) return;

		// to be sent as a array string
		params.form_id = params.form_id.toString();

		send_request(EmailFormAjax.ajax_url, 'POST', params, (success, response) => {
			console.log('Form Load Impression', success, response);
		});
	}

	/**
	 * This will loop over all the unsubmitted forms on the page 
	 * and add an intersect observer to each one so we know if they become visible.
	 * Once visible, we send a viewed impression
	 */
	const setup_form_viewed_observer = () => {
		// Get all the forms on the page that have not been submitted
		const forms = document.querySelectorAll('.opt-bud .email-container:not(.submitted)');

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						// Form is now visible, get the id
						const params = {
							action: 'handle_form_impression_request',
							nonce: EmailFormAjax.nonce,
							type: 'views',
							post_id: entry.target.querySelector('input[name="post_id"]').value,
							form_id: entry.target.getAttribute('data-id')
						}

						// Send impression
						send_request(EmailFormAjax.ajax_url, 'POST', params, (success, response) => {
							console.log('Form Viewed Impression', success, response);
						});

						// Stop observing the form
						observer.unobserve(entry.target);
					}
				});
			}, { threshold: [0.3] }); // Trigger when at least 30% of the form is visible

			forms.forEach(form => observer.observe(form));
		}
	}

	/**
	 * 
	 */
	const init = () => {
		// Refreshes the nonce to prevent errors from outdated nonces
		handle_nonce_refresher();

		// Places the forms in their designated locations
		init_forms();

		// Shows the submitted view to all forms that have already been submitted by this user
		handle_submitted_forms();

		// Removes all overlapping forms like if multiple forms were showing under paragraph #1
		remove_overlapping_forms();

		// Applies the inview effect if desired
		apply_inview_effect();

		// Wait a couple of seconds before triggering events
		setTimeout(function () {
			// Add form load impression
			add_form_load_impression();
			// This will add a form view impression to every form that has been viewed on the page
			setup_form_viewed_observer();
		}, 2000);
	}

	init();
});