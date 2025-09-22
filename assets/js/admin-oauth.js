jQuery(document).ready(function ($) {
	// Prevents multiple submissions
	let _processing = false;

	/**
	 * Auth Form Submit Handler
	 */
	$(document).on('submit', '.auth form', function (e) {
		if (_processing) {
			e.preventDefault();
			return false;
		}
		_processing = true;

		const client_id = $('input[name$="client_id"]', $(this)).val();
		const client_secret = $('input[name$="client_secret"]', $(this)).val();

		if (client_id === "" || client_secret === "") {
			alert("Please fill are required fields.");
			e.preventDefault();
			_processing = false;
			return false;
		}

		$('button[type="submit"]', $(this)).prop('disabled', true);
		//window.location = auth_uri;
		return true;
	});

	/**
	 * Contact Lists Form Submit Handler
	 */
	$(document).on('submit', '.contact_lists form', function (e) {
		e.preventDefault();

		if (_processing) return;
		_processing = true;

		$('button[type="submit"]', $(this)).prop('disabled', true);

		const data = {};

		// Get the key value pairs of each form element
		$('input, select', $(this)).each(function (index, element) {
			data[element.getAttribute('name')] = $(element).val().trim();
		});

		if (window.opener) {
			window.opener.onAuthCodeRecieved(data);
			window.close();
		}
	});
});

/**
 * Closes the popup
 */
function close_page() {
	if (window.opener) {
		window.opener.onAuthCodeRecieved({});
		window.close();
	}
}