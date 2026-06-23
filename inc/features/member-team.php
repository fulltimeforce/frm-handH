<?php
if (!defined('ABSPATH')) {
  exit;
}

function hnh_add_member_team_role()
{
  // 1) Crear rol si NO existe
  if (!get_role('member_team')) {
    add_role(
      'member_team',
      'Member Team',
      [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
        'publish_posts' => false,
      ]
    );
  }
}
add_action('init', 'hnh_add_member_team_role', 20);


// Cambiar la base de la URL para usuarios visibles en "Meet the Team"
function hnh_member_team_author_link($link, $author_id, $author_nicename)
{
  $show_in_team = get_field('show_in_meet_the_team_page', 'user_' . $author_id);

  if ($show_in_team) {
    // Nueva base: /member/{username}/
    return home_url('/member/' . $author_nicename . '/');
  }

  return $link;
}
add_filter('author_link', 'hnh_member_team_author_link', 10, 3);

// Regla de rewrite para que /member-team/usuario/ funcione
function hnh_member_team_rewrite()
{
  add_rewrite_rule(
    '^member/([^/]+)/?$',
    'index.php?author_name=$matches[1]',
    'top'
  );
}
add_action('init', 'hnh_member_team_rewrite');

add_action('init', function () {
  $role = get_role('member_team');
  if (!$role)
    return;

  $role->add_cap('read');
  $role->add_cap('edit_posts');
  $role->add_cap('edit_published_posts');
  $role->add_cap('edit_others_posts');
  $role->add_cap('read_private_posts');
  $role->add_cap('edit_private_posts');
  $role->add_cap('publish_posts');
  $role->add_cap('delete_posts');
  $role->add_cap('delete_published_posts');
  $role->add_cap('delete_others_posts');
  $role->add_cap('delete_private_posts');
}, 99);