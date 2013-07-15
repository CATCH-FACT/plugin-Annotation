<?php echo head(); ?>
<div id="primary">
	<h1><?php echo __("Thank you for annotating!"); ?></h1>
	<p><?php echo __("Your annotation will show up in the archive once an administrator approves it. Meanwhile, feel free to %s or %s ." , annotation_link_to_annotate(__('make another annotation')), "<a href='" . url('items/browse') . "'>" . __('browse the archive') . "</a>"); ?>
	</p>
	<?php if(get_option('annotation_simple') && !current_user()): ?>
	<p><?php echo __("If you would like to interact with the site further, you can use an account that is ready for you. Visit %s, and request a new password for the email you used", "<a href='" . url('users/forgot-password') . "'>" . __('this page') . "</a>"); ?>
	<?php endif; ?>
</div>
<?php echo foot(); ?>
