<?php
/*
    Template name: specialists
*/

get_header();

$bg_image = get_field('breadcrumb_image');
$subtitle = get_field('specialist_subtitle');
$text = get_field('specialist_text');
$button = get_field('specialist_button');

get_banner('Homepage / About / Meet the team', esc_url($bg_image), 'Meet the team');
?>

<section class="specialist_page">
    <div class="specialist_page-container">
        <div class="specialist_content">
            <?php if ($subtitle): ?>
                <h2><?php echo esc_html($subtitle); ?></h2>
            <?php endif; ?>
            <?php if ($text): ?>
                <div class="specialist_text text">
                    <?php echo $text; ?>
                    <?php if ($button): ?>
                        <a class="link_btn" href="<?php echo esc_url($button['url']); ?>" target="<?php echo esc_attr($button['target']); ?>">
                            <span><?php echo esc_html($button['title']); ?></span>
                            <img src="<?php echo IMG; ?>/arrow.svg">
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="specialist_list">
            <?php
            $members = get_users([
                'orderby' => 'user_order',
                'order'   => 'ASC',
                'meta_query' => [
                    [
                        'key'   => 'show_in_meet_the_team_page',
                        'value' => '1',
                    ]
                ],
            ]);

            if (! empty($members)) : ?>

                <div class="specialist_row">
                    <?php foreach ($members as $member) :

                        // Campos ACF del usuario
                        $job_position = get_field('job_position', 'user_' . $member->ID);
                        $team_email = $member->user_email;
                        $team_phone   = get_field('team_phone',   'user_' . $member->ID);
                        $thumbnail   = get_field('thumbnail_member', 'user_' . $member->ID);

                        // URL del perfil (author / member-team)
                        $profile_url  = get_author_posts_url($member->ID);
                    ?>

                        <div class="specialist_item_wrapper">
                            <div class="specialist_item">
                                <div class="specialist_item_header">
                                    <a href="<?php echo esc_url($profile_url); ?>" class="specialist_item_image">
                                        <img src="<?php echo $thumbnail['url']; ?>" alt="<?php echo esc_html($member->display_name); ?>" title="<?php echo esc_html($member->display_name); ?>">
                                    </a>

                                    <div class="specialist_item_container">
                                        <div class="specialist_item_info">
                                            <p class="specialist_item_name">
                                                <?php echo esc_html($member->display_name); ?>
                                            </p>

                                            <?php if ($job_position) : ?>
                                                <span class="specialist_item_job">
                                                    <?php echo esc_html($job_position); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <button class="specialist_toggle open" aria-expanded="false" type="button">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 11.9994L21 11.9994M11.9997 3L11.9997 21" stroke="#F5F2EE" stroke-width="2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="specialist_item_body">
                                    <div class="specialist_item_card_header">
                                        <div>
                                            <p class="specialist_item_body_name">
                                                <?php echo esc_html($member->display_name); ?>
                                            </p>

                                            <?php if ($job_position) : ?>
                                                <span class="specialist_item_body_job">
                                                    <?php echo esc_html($job_position); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <button class="specialist_toggle close" aria-expanded="false" type="button">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 12L21 12" stroke="#F5F2EE" stroke-width="2" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div>
                                        <?php if ($team_email) : ?>
                                            <p class="specialist_item_body_email">
                                                Email:
                                                <a style="color:black" href="mailto:<?php echo esc_attr($team_email); ?>">
                                                    <strong><?php echo esc_html($team_email); ?></strong>
                                                </a>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($team_phone) : ?>
                                            <p class="specialist_item_body_tel">
                                                Tel:
                                                <a style="color:black" href="tel:<?php echo esc_attr($team_phone); ?>">
                                                    <strong><?php echo esc_html($team_phone); ?></strong>
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="specialist_item_body_description">
                                        <?php
                                        // Si quieres una “bio” corta, crea un campo ACF tipo textarea:
                                        $bio = get_the_author_meta('description', $member->ID);
                                        if ($bio) {
                                            echo '<p>' . esc_html($bio) . '</p>';
                                        }
                                        ?>
                                    </div>

                                    <a href="<?php echo esc_url($profile_url); ?>" class="specialist_readmore">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>