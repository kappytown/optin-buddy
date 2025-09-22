<?php defined('ABSPATH') || exit; ?>

<section class="obp-admin wrap">
	<h1 class="inline">Settings</h1>
	<p>Here you can add/update your email provider settings and view your forms.</p>

	<!-- Sets the values used on initialization -->
	<div class="hidden values" data-provider="<?php echo $provider; ?>"></div>

	<!-- Select Your Email Provider Section -->
	<section class="section-select-provider <?php echo !empty($provider) ? 'hidden' : '' ?>">
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<input type="hidden" name="action" value="save_settings_form_action" />
			<input type="hidden" name="redirect_uri" value="<?php echo admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'oauth_callback'); ?>" />
			<?php wp_nonce_field('save_settings_form_action', 'save_settings_nonce'); ?>
			<p>
				<strong>Email Provider Settings:</strong><br />
				<span class="desc">Select your email provider and fill out all required fields. The selected provider is where all of your email subscription forms will be sent to.<br /><strong>Note:</strong>If you do not yet have a provider, select WordPress as your provider.</span>
			</p>
			<?php echo $this->render_providers($provider); ?>
		</form>
	</section>

	<!-- Selected Email Provider and List Forms Section -->
	<section class="section-forms <?php echo empty($provider) ? 'hidden' : '' ?>">
		<!-- Selected Email Provider -->
		<article class="provider connection">
			Selected Provider:
			<div class="logo">
				<img src="">
			</div>
		</article>

		<p><button type="button" class="button button-secondary update-provider-settings">Update Email Provider Settings</button></p>

		<!-- List Forms -->
		<div class="forms">
			<strong>Forms:</strong><br />
			<span class="desc">List of forms that you had created.</span>

			<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				<input type="hidden" name="action" value="delete_form_action" />
				<?php wp_nonce_field('delete_form_action', 'delete_form_nonce'); ?>
				<input type="hidden" name="id" value="" />
				<div class="results"></div>
			</form>
		</div>

		<!-- Add New Form -->
		<form method="post" action="<?php echo admin_url('admin.php?page=' . OPTIN_BUDDY_PREFIX . 'add_new') ?>">
			<p><button type="submit" class="button button-primary">Add New Form</button></p>
		</form>
	</section>
</section>