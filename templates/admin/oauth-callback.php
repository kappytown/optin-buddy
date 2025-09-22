<?php defined('ABSPATH') || exit ?>;

<section class="obp-admin section_oauth">
	<input type="hidden" name="access_token" value="<?php echo $this->access_token ?>" />
	<input type="hidden" name="refresh_token" value="<?php echo $this->refresh_token ?>" />
	<input type="hidden" name="expires_in" value="<?php echo $this->expires_in ?>" />
	<input type="hidden" name="expiration_date" value="<?php echo $this->expiration_date ?>" />

	<?php if ($this->close_page) { ?>
		<script>
			const data = {};
			//data['has_invalid_credentials'] = <?php echo $this->has_invalid_credentials ? 1 : 0; ?>;

			// Get the key value pairs of each form element
			document.querySelectorAll('input').forEach(element => {
				data[element.getAttribute('name')] = element.value;
			});

			if (window.opener) {
				window.opener.onAuthCodeRecieved(data);
				window.close();
			}
		</script>
	<?php } ?>
</section>