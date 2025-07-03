<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <title><?php echo wp_get_document_title(); ?></title>
</head>

<body <?php body_class(); ?>>

    <header class="header">
        <div class="header_container">
            <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo get_bloginfo('name'); ?>" class="header_logo d-block w-100">
                <img src="<?php echo IMG; ?>/logo.svg" title="<?php echo get_bloginfo('name'); ?>" alt="<?php echo get_bloginfo('name'); ?>" class="w-100" loading="lazy">
            </a>
            <?php wp_nav_menu(array('theme_location' => 'header-menu', 'container_class' => 'top-menus-wrapper', 'menu_class' => 'ul_menu')); ?>
            <div class="header_actions">
                <button type="button">
                    Search
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M8.38218 8.34672C6.62482 10.1041 3.77557 10.1041 2.01822 8.34672C0.260856 6.58936 0.260856 3.74012 2.01821 1.98276C3.77557 0.225402 6.62482 0.225403 8.38218 1.98276C10.1395 3.74012 10.1395 6.58936 8.38218 8.34672ZM8.38218 8.34672L11.5642 11.5287" stroke="white" />
                    </svg>
                </button>
                <a href="#">
                    Get a valuation
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="white" />
                    </svg>
                </a>
            </div>
        </div>
    </header>