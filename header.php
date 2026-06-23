<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <title><?php echo wp_get_document_title(); ?></title>

    <?php if (is_singular('vehicles')) :
        $vehicle_id = get_the_ID();

        // ======= OG:IMAGE (galería → thumbnail → placeholder) =======
        $og_image = '';
        $gallery  = get_field('gallery_vehicle', $vehicle_id);

        if (is_array($gallery) && !empty($gallery)) {
            $first = $gallery[0];
            if (is_numeric($first)) {
                $og_image = wp_get_attachment_image_url((int) $first, 'full');
            } elseif (is_array($first)) {
                if (!empty($first['ID'])) {
                    $og_image = wp_get_attachment_image_url((int) $first['ID'], 'full');
                } elseif (!empty($first['url'])) {
                    $og_image = $first['url'];
                }
            }
        }
        if (empty($og_image) && has_post_thumbnail($vehicle_id)) {
            $og_image = get_the_post_thumbnail_url($vehicle_id, 'full');
        }
        if (empty($og_image)) {
            $og_image = IMG . '/placeholder-vehicle.png';
        }

        // ======= OTROS DATOS =======
        $og_title = get_the_title($vehicle_id);
        $og_desc  = get_the_excerpt($vehicle_id) ?: wp_trim_words(strip_tags(get_the_content(null, false, $vehicle_id)), 30);
        $og_url   = get_permalink($vehicle_id);
    ?>
        <!-- Open Graph -->
        <meta property="og:type" content="article">
        <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
        <meta property="og:description" content="<?php echo esc_attr($og_desc); ?>">
        <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
        <meta property="og:image" content="<?php echo esc_url($og_image); ?>">

        <!-- Opcional: metadatos extra de la imagen -->
        <meta property="og:image:alt" content="<?php echo esc_attr($og_title); ?>">
        <?php
        // Obtener dimensiones si la imagen es adjunto de WP
        if ($attachment_id = attachment_url_to_postid($og_image)) {
            $meta = wp_get_attachment_metadata($attachment_id);
            if (!empty($meta['width']) && !empty($meta['height'])) {
                echo '<meta property="og:image:width" content="' . esc_attr($meta['width']) . '">' . "\n";
                echo '<meta property="og:image:height" content="' . esc_attr($meta['height']) . '">' . "\n";
            }
        }
        ?>
    <?php endif; ?>

</head>

<body <?php body_class(); ?>>

    <?php
    $class = 'white_bg';
    // if (!is_front_page()) {
    //     $class = 'white_bg';
    // }
    ?>

    <header class="header <?php echo $class; ?>">
        <div class="header_container">
            <a href="<?php echo esc_url(home_url('/')); ?>" alt="<?php echo get_bloginfo('name'); ?>" class="header_logo d-block w-100">
                <img src="<?php echo IMG; ?>/logo.svg" title="<?php echo get_bloginfo('name'); ?>" alt="<?php echo get_bloginfo('name'); ?>" class="w-100" loading="lazy">
            </a>
            <nav>
                <div class="header_navigation">
                    <div class="header_navigation--bg header_toggle"></div>
                    <div class="header_navigation--list">
                        <?php
                            wp_nav_menu([
                                'theme_location' => 'header-menu',
                                'container'      => false,
                                'menu_class'     => 'ul_menu',
                                'walker'         => new Header_Menu_Walker(),
                            ]);
                        ?>
                    </div>
                </div>
				
				<?php 
					$toogle_header_search = get_field('toogle_header_search', 'option');
					if(!$toogle_header_search):
				?>
				<style>.toggle_search{pointer-events:none;user-select:none;opacity:0;visibility:hidden;}</style>
				<?php endif; ?>
				
                <div class="header_actions">
					<?php //if($toogle_header_search): ?>
                    <button type="button" class="toggle_search" title="Search">
                        Search
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M8.38218 8.34672C6.62482 10.1041 3.77557 10.1041 2.01822 8.34672C0.260856 6.58936 0.260856 3.74012 2.01821 1.98276C3.77557 0.225402 6.62482 0.225403 8.38218 1.98276C10.1395 3.74012 10.1395 6.58936 8.38218 8.34672ZM8.38218 8.34672L11.5642 11.5287" stroke="white" />
                        </svg>
                    </button>
					<?php //endif; ?>
					
                    <?php if(!empty(get_field('get_a_valuation_button', 'option'))): ?>
                    <a href="<?php echo get_field('get_a_valuation_button', 'option'); ?>" alt="Get a valuation" title="Get a valuation">
                        Get a valuation
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="white" />
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
            <button class="header_toggle header_button-menu" type="button" alt="Menú" title="Menú">
                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M80 160h352M80 256h352M80 352h352" />
                </svg>
            </button>
        </div>
    </header>

    <img src="<?php echo IMG; ?>/lines.svg" alt="Separator" title="Separator" class="header_lines">
	
	<?php //if($toogle_header_search): ?>
    <div class="search_viewport">
        <div class="search_viewport-bg"></div>
        <button type="button" class="search_viewport-close toggle_search" alt="Close" title="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M5.63644 5.63504L18.3644 18.363M18.3637 5.63522L5.63582 18.3631" stroke="white" />
            </svg>
        </button>
        <div class="search_viewport-box">
            <?php get_search_form(); ?>
        </div>
    </div>
	<?php //endif; ?>
	
    <?php get_template_part('inc/sections/countdown-banner'); ?>