<?php
if (!defined('ABSPATH')) {
  exit;
}
function get_banner($breadcrumb = '', $image_url = '', $title = '')
{
  if (empty($title)) {
    $title = get_the_title();
  }
  if (empty($image_url)) {
    $image_url = IMG . '/banner.png';
  }
  echo '<section class="banner">
        <div class="banner__bg">
            <img src="' . $image_url . '">
        </div>
        <div class="container">
            <div class="breadcrumb">
                <p>' . $breadcrumb . '</p>
            </div>
            <h1>' . $title . '</h1>
        </div>
    </section>';
}

function get_centered_banner($image_url = '', $title = '', $size = 'default')
{
  if (empty($title)) {
    $title = get_the_title();
  }
  if (empty($image_url)) {
    $image_url = IMG . '/banner.png';
  }
  $size_class = '';
  if ($size === 'small') {
    $size_class = 'small-banner';
  }

  echo '<section class="banner centered ' . esc_attr($size_class) . '">
        <div class="banner__bg">
            <img src="' . $image_url . '">
        </div>
        <div class="container">
            <h1>' . $title . '</h1>
        </div>
    </section>';
}