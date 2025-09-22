<?php defined('ABSPATH') || exit; ?>

<section class="email-container form-1 <?php echo $has_name ? 'has-name' : '' ?>" data-id='{{form_id}}'>
	<div class="close">X</div>
	<form class="email-form-form" method="post">
		<!--{{fields}}-->
		<div class="content">
			<h3 class="header"><?php echo esc_html($header) ?></h3>
			<p class="small body"><?php echo esc_html($body) ?></p>
		</div>

		<div class="message hidden"><?php echo esc_html($success); ?></div>

		<div class="loader hidden">
			<div class="spinner"></div>
		</div>

		<div class="field">
			<input type="text" name="name" placeholder="First Name" class="hidden" /><input type="email" name="email" placeholder="Email Address" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$" required /><button type="submit" class="button"><span><?php echo esc_html($button) ?></span></button>
		</div>

		<div class="disclaimer"><?php echo esc_html($disclaimer) ?></div>
	</form>
</section>