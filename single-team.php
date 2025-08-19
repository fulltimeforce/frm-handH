<?php
/*
 * Single template for Team
 */
get_header(); ?>

<main class="single_team_page">
  <div class="container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class('single_team'); ?>>
        
        <div class="single_team_wrapper">
          
          <?php if (has_post_thumbnail()) : ?>
            <div class="single_team_image">
              <?php the_post_thumbnail('large'); ?>
            </div>
          <?php endif; ?>

          <div class="single_team_info">
            
            <?php if (get_field('job_position')) : ?>
              <p class="single_team_job"><?php the_field('job_position'); ?></p>
            <?php endif; ?>

            <h1 class="single_team_name"><?php the_title(); ?></h1>

            <div class="single_team_content">
              <?php the_content(); ?>
            </div>

            <?php if (get_field('team_email')) : ?>
              <p class="single_team_email">
                Email:
                <a href="mailto:<?php the_field('team_email'); ?>">
                  <?php the_field('team_email'); ?>
                </a>
              </p>
            <?php endif; ?>

            <?php if (get_field('team_phone')) : ?>
              <p class="single_team_phone">
                Tel:<strong><?php the_field('team_phone'); ?></strong>
              </p>
            <?php endif; ?>

            <div class="single_team_back">
              <a class="link_btn" href="/meet-the-team">
                <span>Meet The Team</span>
                <img src="<?php echo IMG; ?>/arrow.svg">
              </a>
            </div>

            <div class="single_team_back">
              <a class="link_btn" href="<?php the_field('linkedin_url'); ?>">
                <span>Connect via LinkedIn</span>
                <img src="<?php echo IMG; ?>/arrow.svg">
              </a>
            </div>

          </div>
        </div>
      </article>

    <?php endwhile; endif; ?>
  </div>
</main>

<?php get_footer(); ?>
