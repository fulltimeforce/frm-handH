<?php
get_header();

// Usuario actual (author page)
$author = get_queried_object();

if (! ($author instanceof WP_User)) {
    status_header(404);
    get_template_part('404');
    get_footer();
    exit;
}

// ✅ Solo permitir perfiles que tengan el checkbox activado
$show_in_team = get_field('show_in_meet_the_team_page', 'user_' . $author->ID);

if (! $show_in_team) {
    status_header(404);
    get_template_part('404');
    get_footer();
    exit;
}

// Ejemplo de campos ACF del grupo "Member Info"
$job_title   = get_field('job_position',   'user_' . $author->ID);
$phone       = get_field('team_phone',       'user_' . $author->ID);
$email       = esc_html($author->user_email);
$description = get_field('description_member', 'user_' . $author->ID);
$linkedin    = get_field('linkedin_url', 'user_' . $author->ID);
$thumbnail   = get_field('thumbnail_member', 'user_' . $author->ID);
$team_achievements = get_field('team_achievements', 'user_' . $author->ID);

if ($email == 'contact@handh.com') {
    $email = '';
}

?>

<main class="single_team_page">
    <div class="single_team_page_wrapper">
        <article id="post-<?php $author->ID; ?>" <?php post_class('single_team'); ?>>

            <div class="single_team_wrapper">

                <?php if ($thumbnail && !empty($thumbnail)) : ?>
                    <div class="single_team_image">
                        <img src="<?php echo $thumbnail['url']; ?>" alt="<?php echo esc_html($author->display_name); ?>" title="<?php echo esc_html($author->display_name); ?>">
                    </div>
                <?php endif; ?>

                <div class="single_team_info">

                    <?php if ($job_title && !empty($job_title)) : ?>
                        <div class="single_team_job_wrapper">
                            <div class="breadlines">
                                <p class="single_team_job"><?php echo $job_title; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h1 class="single_team_name">
                        <?php echo esc_html($author->first_name); ?> <?php echo esc_html($author->last_name); ?>
                    </h1>

                    <?php if ($description && !empty($description)): ?>
                        <div class="single_team_content">
                            <?php echo $description; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($email)) : ?>
                        <p class="single_team_email">
                            Email:
                            <a href="mailto:<?php echo $email; ?>">
                                <?php echo $email; ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if ($phone && !empty($phone)) : ?>
                        <p class="single_team_phone">
                            Tel: <strong><?php echo $phone; ?></strong>
                        </p>
                    <?php endif; ?>

                    <?php if ($linkedin && !empty($linkedin)) : ?>
                        <div class="single_team_back">
                            <a class="link_btn" href="<?php echo $linkedin; ?>">
                                <span>Connect via LinkedIn</span>
                                <img src="<?php echo IMG; ?>/arrow.svg">
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="single_team_back">
                        <a class="link_btn" href="<?php echo esc_url(home_url('meet-the-team')); ?>">
                            <span>Meet The Team</span>
                            <img src="<?php echo IMG; ?>/arrow.svg">
                        </a>
                    </div>

                    <?php if ($team_achievements && !empty($team_achievements)): ?>
                        <div class="single_team_achievements">
                            <img src="<?php echo $team_achievements['url']; ?>" alt="Team achievements logos">
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </article>
    </div>
</main>

<?php get_footer(); ?>