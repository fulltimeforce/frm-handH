<?php
if (!defined('ABSPATH')) {
  exit;
}

add_action(
  'after_setup_theme',
  'hnh_setup_theme_support'
);
add_action(
  'wp_enqueue_scripts',
  'hnh_remove_block_css',
  100
);
// === Excerpt Length ===
add_filter(
  'excerpt_length',
  'hnh_excerpt_length'
);
// === Register Navigation Menus ===
add_action(
  'init',
  'hnh_register_menus'
);

remove_action(
  'wp_head',
  'print_emoji_detection_script',
  7
);
remove_action(
  'wp_print_styles',
  'print_emoji_styles'
);

function hnh_setup_theme_support(): void
{
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support(
    'html5',
    ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']
  );
}

function hnh_register_menus(): void
{
  register_nav_menus([
    'header-menu' => __('Header Menu', 'handh'),
    'footer-auctions-menu' => __('Footer Auctions Menu', 'handh'),
    'footer-private-sales-menu' => __('Footer Private Sales Menu', 'handh'),
    'footer-account-bidding-menu' => __('Footer Account & Bidding Menu', 'handh'),
    'footer-bold' => __('Footer Bold', 'handh'),
    'footer-terms' => __('Footer Terms', 'handh'),
  ]);
}

function hnh_remove_block_css(): void
{
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');
}

function hnh_excerpt_length(int $length): int
{
  return 30;
}