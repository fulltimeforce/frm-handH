<?php
/**
 * Helpers Index
 * 
 * Loads all helper modules
 */
if (!defined('ABSPATH')) {
  exit;
}

// Load auction helpers
require_once __DIR__ . '/auctions/index.php';

// Load vehicle helpers
require_once __DIR__ . '/vehicles/index.php';