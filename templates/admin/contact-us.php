<?php defined('ABSPATH') || exit; ?>

<section class="obp-admin contact-us wrap">
	<h1>Contact Us</h1>
	<p>Let us know if you have any questions, ideas, or concerns. You can also contact us at <?php echo OPTIN_BUDDY_EMAIL ?></p>
	<p><strong>Note:</strong> If you do not receive an immediate automated response from us, then we may not have recieved your email. Please send us an email at <?php echo OPTIN_BUDDY_EMAIL ?></p>

	<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
		<input type="hidden" name="action" value="contact_us_form_action" />
		<?php wp_nonce_field('contact_us_form_action', OPTIN_BUDDY_PREFIX . 'contact_nonce'); ?>

		<div class="field">
			<label for="name">Name: <span class="required">*</span></label>
			<input type="text" name="name" placeholder="" required value="" />
		</div>

		<div class=" field">
			<label for="name">Email: <span class="required">*</span></label>
			<input type="email" name="email" placeholder="" required value="<?php echo $admin_email; ?>" />
		</div>

		<div class="field">
			<label for="subject">Subject: <span class="required">*</span></label>
			<input type="text" name="subject" placeholder="" required value="" />
		</div>

		<div class="field">
			<label for="massage">Message: <span class="required">*</span></label>
			<textarea name="message" placeholder="" rows="4" required></textarea>
		</div>

		<p>
			<button type="submit" class="button button-primary">Send Message</button>
		</p>
	</form>
</section>