jQuery(document).ready(function ($) {
	/**
	 * Upload image click handler
	 */
	$('#upload-button').on('click', function (e) {
		e.preventDefault();

		let image_frame;

		image_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select Image',
			button: {
				text: 'Use this image'
			},
			multiple: false
		});

		image_frame.on('select', function () {
			let attachment = image_frame.state().get('selection').first().toJSON();
			$('#image_id').val(attachment.id);
			$('#image-url').val(attachment.url);

			// To preview within the template
			$('.email-container .image, #image-preview').css('background-image', `url('${attachment.url}')`);
		});

		image_frame.open();
	});
});