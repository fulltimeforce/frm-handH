<?php

add_action('admin_footer', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'vehicles') return;
    ?>
    <script>
    jQuery(function($){
        const $auctionNumber = $('[data-key="field_auction_number_latest"] input');
        const $auctionDate = $('[data-key="field_auction_date_latest"] input');

        let timer = null;

		if($auctionNumber && $auctionDate){
        	$auctionNumber.on('input', function(){
            	const value = $(this).val().trim();

            	clearTimeout(timer);

            	if (!value) {
                	$auctionDate.val('');
                	return;
            	}

            	// Espera 500ms después de dejar de escribir
            	timer = setTimeout(() => {
                	fetch(ajaxurl, {
                    	method: 'POST',
                    	headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    	body: new URLSearchParams({
                        	action: 'get_auction_date_by_number',
                        	number: value
                    	})
                	})
                	.then(res => res.json())
                	.then(data => {
                    	if (data && data.success) {
                        	$auctionDate.val(data.data.date);
                    	} else {
                        	$auctionDate.val('');
                    	}
                	})
                	.catch(err => console.error('Error fetching auction date:', err));
            	}, 500);
        	});
        }
    });
    </script>
    <?php
});

add_action('wp_ajax_get_auction_date_by_number', function() {
    if (empty($_POST['number'])) {
        wp_send_json_error(['message' => 'Missing number']);
    }

    $number = sanitize_text_field($_POST['number']);

    $query = new WP_Query([
        'post_type' => 'auction',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'sale_number',
                'value' => $number,
                'compare' => '='
            ]
        ]
    ]);

    if ($query->have_posts()) {
        $auction = $query->posts[0];
        $auction_date = get_field('auction_date', $auction->ID);
        wp_send_json_success(['date' => $auction_date]);
    } else {
        wp_send_json_error(['message' => 'Not found']);
    }
});