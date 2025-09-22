<?php defined('ABSPATH') || exit; ?>

<section class="admin-setup">
	<div class="providers-container">
		<?php foreach ($this->vendors as $vendor) { ?>
			<?php if ($vendor['is_enabled']) { ?>
				<article class="provider<?php echo !$vendor['is_enabled'] ? ' disabled' : '' ?><?php echo $provider === $vendor['key'] ? ' active' : '' ?>" data-key="<?php echo $vendor['key'] ?>">
					<div class="logo">
						<img src="<?php echo OPTIN_BUDDY_URL . 'assets/img/' . $vendor['key'] . '.svg' ?>" />
					</div>
					<div class="name"><?php echo $vendor['name'] ?></div>
				</article>
		<?php }
		} ?>
	</div>
	<div class="provider-settings-container"></div>
</section>