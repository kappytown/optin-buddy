/**
 * Displays a modal window by appending the modal content to the document body
 * 
 * How to use: opt_bud.modal.show('my_id', {title: 'This is the title', closeButton:true, body:'This is the body<p>Do not place a paragraph tag on the first paragraph</p><p>More content goes here</p>', actionButtonText:'Do It'});
 */
opt_bud = window['opt_bud'] || {};
opt_bud.modal = (() => {
	/**
	 * Not using ES6 syntax so we can reference self using this keyword
	 * otherwise, this would refer to the window object
	 * 
	 * @param {int} id 
	 * @param {object} settings 
	 * @returns self
	 */
	function _show(id, settings) {
		const _this = this;
		// Prevent duplicates
		if (document.querySelector(`#${id}`)) {
			_hide(id);
		}

		const $body = document.body;
		$body.classList.add('modal-open');
		$body.insertAdjacentHTML('beforeend', _getModalView(id, settings));
		$body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop fade in"></div>');

		const $el = document.querySelector(`#${id}`);
		$el.querySelectorAll('.close, .btn-close').forEach(element => {
			element.addEventListener('click', function (e) {
				_hide(id);
			});
		});

		return this;
	}

	/**
	 * Creates the body of the modal window
	 * 
	 * @param {int} id 
	 * @param {object} settings 
	 * @returns string
	 */
	function _getModalView(id, settings) {
		let content = '';
		content += `<section id="${id}" class="modal fade in ${(settings.class ? settings.class : '')}" tabindex="-1" role="dialog">`;
		content += '<div class="modal-dialog" role="document">';
		content += '<div class="modal-content">';

		// Header
		if (settings.title) {
			content += '<div class="modal-header">';

			if (settings.closeButton) {
				content += '<button type="button" class="close"><span>X</span></button>';
			}

			content += `<h4 class="modal-title">${settings.title}</h4>`;
			content += '</div>';
		}

		// Body
		content += `<div class="modal-body">${settings.body}</div>`;

		// Footer
		if (settings.closeButton || settings.actionButtonText) {
			content += '<div class="modal-footer">';

			if (settings.closeButton) {
				content += `<button type="button" class="button btn-close">${settings.closeButtonTitle || 'Close'}</button>`;
			}

			if (settings.actionButtonText) {
				content += `<button type="button" class="button button-primary">${settings.actionButtonText}</button>`;
			}
			content += '</div>';
		}

		content += '</div>';
		content += '</div>';
		content += '</section>';

		return content;
	}

	/**
	 * Removes the modal window and modal backdrop from the DOM
	 * 
	 * @param {int} id 
	 * @returns self
	 */
	function _hide(id) {
		document.body.classList.remove('modal-open');
		document.querySelectorAll('.modal-backdrop').forEach(element => {
			element.remove();
		});

		document.querySelectorAll(`#${id}`).forEach(element => {
			element.remove();
		});
		return this;
	}

	/**
	 * Checks if an element exists in the DOM
	 * 
	 * @param {*} id 
	 * @returns int
	 */
	function _isVisible(id) {
		return document.querySelector(`#${id}`);
	}

	/**
	 * Public APIs
	 */
	const _public = {
		show: _show,
		hide: _hide,
		isVisible: _isVisible
	};

	return _public;
})();