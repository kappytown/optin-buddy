<?php defined('ABSPATH') || exit; ?>

<section class="email-container form-5 <?php echo $has_name ? 'has-name' : '' ?>" data-id='{{form_id}}'>
	<div class="close">X</div>
	<div class="image" style="background-image: url('<?php echo !empty($image_url) ? $image_url : OPTIN_BUDDY_URL . 'assets/img/japanese-food.jpg' ?>');" title="<?php echo $image_title; ?>" alt="<?php echo $image_alt; ?>"></div>
	<form class="email-form-form" method="post">
		<!--{{fields}}-->
		<div class="content">
			<div class="box">
				<h3 class="header"><?php echo esc_html($header) ?></h3>
				<p class="body"><?php echo esc_html($body) ?></p>

				<div class="message hidden"><?php echo esc_html($success); ?></div>

				<div class="loader hidden">
					<div class="spinner white"></div>
				</div>

				<div class="field">
					<input type="text" name="name" placeholder="First Name" class="hidden" /><input type="email" name="email" placeholder="Email Address" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$" required /><button type="submit" class="button"><span><?php echo esc_html($button) ?></span></button>
				</div>

				<div class="disclaimer"><?php echo esc_html($disclaimer) ?></div>
			</div>
		</div>
	</form>
</section>