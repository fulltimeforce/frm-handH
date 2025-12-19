<?php

// Users WP
$hh_team_users = get_users([
    'orderby'    => 'user_order',
    'order'      => 'ASC',
    'meta_query' => [
        [
            'key'   => 'show_in_meet_the_team_page',
            'value' => '1',
        ]
    ],
]);

define('HH_TEAM_USERS', $hh_team_users);
define('HH_TRACKING_VIEW_CAP', 'hh_tracking_view'); // admin + member_team
define('HH_TRACKING_ADMIN_CAP', 'manage_options');  // solo admin
define('HH_TRACKING_VIEW_JUST_MT', 'member_team');  // solo member_team