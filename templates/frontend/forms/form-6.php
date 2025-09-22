<?php defined('ABSPATH') || exit; ?>

<section class="email-container form-6 bottom <?php echo $has_name ? 'has-name' : '' ?>" data-id='{{form_id}}'>
	<div class="close">close</div>
	<form class="email-form-form" method="post">
		<!--{{fields}}-->
		<div class="content">
			<div class="field">
				<h3 class="header hidden"><?php echo esc_html($header) ?></h3>
				<div class="body"><?php echo esc_html($body) ?></div>

				<div class="message hidden"><?php echo esc_html($success); ?></div>

				<div class="loader hidden">
					<div class="spinner"></div>
				</div>

				<input type="text" name="name" placeholder="First Name" class="hidden" /><input type="email" name="email" placeholder="Email Address" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$" required /><button type="submit" class="button"><span><?php echo esc_html($button) ?></span></button>

				<div class="disclaimer hidden"><?php echo esc_html($disclaimer) ?></div>
			</div>
		</div>
	</form>
</section>