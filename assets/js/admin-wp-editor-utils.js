opt_bud = window['opt_bud'] || {};
jQuery(document).ready(function ($) {
	opt_bud.wp_editor = {
		/**
		 * 
		 * @param {string} id - if of the editor
		 * @returns instance of the editor
		 */
		get_editor: (id) => {
			let editor = tinyMCE.get(id);
			if (!editor || editor.hidden) {
				return jQuery(`#${id}`);
			}
			return editor;
		},

		/**
		 * 
		 * @param {string} id - if of the editor
		 * @returns string contents of the editor
		 */
		get_content: (id) => {
			let editor = tinyMCE.get(id);
			if (!editor || editor.hidden) {
				return jQuery(`#${id}`).val();
			}
			return editor.getContent();
		},

		/**
		 * This appends the styles to the iframe of every editor
		 * If for some reason no editors were found, this will retry 10 times before giving up
		 * 
		 * @param {string} styles 
		 * @param {int} count 
		 * @returns null
		 */
		add_styles: (styles, count) => {
			count = count || 0;

			// Stop trying after 10 attempts
			if (count >= 10) return;

			count++;

			if (typeof tinyMCE !== 'undefined') {
				const iFrameElement = tinyMCE.editors[0].iframeElement;

				if (iFrameElement) {
					for (let i = 0; i < tinyMCE.editors.length; i++) {
						const editor = tinyMCE.editors[i];
						const iframe = $(editor.iframeElement).contents().find('head').append(styles);
					}
					return;
				}
			}

			// Let's try again if failed
			setTimeout(function () {
				opt_bud.wp_editor.add_styles(styles, count);
			}, 500);
		}
	}
});