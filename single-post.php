<?php get_header(); ?>

<div class="single-post">
	<div class="container">
		<?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail('full'); ?>
        </div>
		<?php endif; ?>

		<div class="post-content">
			<h1 class="post-title" style="font-family:GoudyTitlingSemiBold;padding-block:0 1em;"><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>


<style>
	.single-post {
			padding-block: 100px 80px;
	}
	.post-thumbnail img {
		width: 100%;
		border: 1px solid #8c6e47;
	}
	.post-content {
		padding-block: 5em 0;
	}
	@media(max-width:768px) {
		.single-post {
			padding-block: 60px 4em;
		}
		.post-content {
		padding-block: 40px 0;
	}
	}
</style>