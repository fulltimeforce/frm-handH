<?php
/*
    Template name: test
*/

get_header();

?>

<style>
	.wp-chatbot-ball{
		box-shadow: none !important;
	}
	#wp-chatbot-ball .wp-chatbot-ball img{
		filter: hue-rotate(200deg);
	}
	.wp-chatbot-animation-active .wp-chatbot-ball-animation-switch::before, .wp-chatbot-animation-active .wp-chatbot-ball-animation-switch::after{
		display: none !important;
	}
	.wp-chatbot-animation-active .wp-chatbot-ball-animation-switch{
		border-top-color: #967b57 !important;
	}
</style>

<div style="height:100dvh"></div>

<?php echo do_shortcode('[mwai_chatbot id="default"]'); ?>

<?php get_footer(); ?>