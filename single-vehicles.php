<?php
/*
    Template name: listing
*/

get_header();

//get fields
$place = get_field('vehicle_place');
$current_lot = get_field('vehicle_current_lot');
$short_text = get_field('vehicle_short_description');
$estimate = get_field('vehicle_estimate');
$specialist = get_field('specialist');
$lot_details = get_field('vehicle_lot_details');
$tabs = get_field('vehicle_tabs');
$vehicle_video = get_field('vehicle_video');
$vehicle_notes = get_field('vehicle_notes');
?>

<section class="listing_head">
    <div class="container">
        <div class="listing_head-col">
            <div>
                <a class="listing_btn-white p14"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2.60156 8H2.60756M2.60156 14H2.60756M2.60156 2H2.60756M5.60156 8H13.4016M5.60156 14H13.4016M5.60156 2H13.4016" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Return to Auction List</a>
                <p class="p20"><?php echo get_the_date('jS M, Y g:i'); ?></p>
            </div>
            <?php if ($place): ?>
                <p class="listing_head-title"><?php echo $place; ?></p>
            <?php endif; ?>
        </div>
        <div class="listing_head-col">
            <div class="listing_head-count">
                <p class="p17">Current Lot</p>
                <?php if ($current_lot): ?>
                    <span class="p24"><?php echo $current_lot; ?></span>
                <?php endif; ?>
            </div>
            <div class="listing_head-actions">
                <a class="listing_btn-brown p14"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <g clip-path="url(#clip0_1921_36983)">
                        <path d="M14.3008 8L1.70078 8M1.70078 8L8.00078 15M1.70078 8L8.00078 0.999999" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_1921_36983">
                        <rect width="16" height="16" fill="white" transform="translate(16 16) rotate(-180)"/>
                        </clipPath>
                    </defs>
                    </svg>
                    Previous Lot</a>
                <a class="listing_btn-brown p14">Following Lot
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <g clip-path="url(#clip0_1921_36988)">
                        <path d="M1.69922 8H14.2992M14.2992 8L7.99922 1M14.2992 8L7.99922 15" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_1921_36988">
                        <rect width="16" height="16" fill="white"/>
                        </clipPath>
                    </defs>
                    </svg>
                </a>
                <a class="listing_btn-white p14"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M7.68266 1.97392C7.71187 1.9149 7.757 1.86522 7.81295 1.83048C7.8689 1.79575 7.93344 1.77734 7.9993 1.77734C8.06515 1.77734 8.12969 1.79575 8.18564 1.83048C8.24159 1.86522 8.28672 1.9149 8.31593 1.97392L9.85579 5.09297C9.95724 5.29827 10.107 5.47588 10.2922 5.61056C10.4774 5.74525 10.6925 5.83298 10.919 5.86624L14.3627 6.37019C14.428 6.37965 14.4893 6.40717 14.5397 6.44965C14.5901 6.49213 14.6277 6.54787 14.648 6.61057C14.6684 6.67327 14.6709 6.74043 14.6551 6.80444C14.6393 6.86846 14.6059 6.92678 14.5587 6.9728L12.0683 9.39792C11.904 9.55797 11.7811 9.75554 11.7102 9.97362C11.6392 10.1917 11.6223 10.4238 11.661 10.6498L12.2489 14.0762C12.2604 14.1414 12.2534 14.2085 12.2286 14.2699C12.2038 14.3313 12.1622 14.3845 12.1086 14.4235C12.055 14.4624 11.9916 14.4855 11.9255 14.4901C11.8594 14.4947 11.7934 14.4806 11.735 14.4495L8.65657 12.8309C8.45373 12.7244 8.22806 12.6688 7.99896 12.6688C7.76986 12.6688 7.54419 12.7244 7.34135 12.8309L4.26363 14.4495C4.20519 14.4804 4.13924 14.4943 4.07328 14.4896C4.00733 14.4849 3.94401 14.4618 3.89053 14.4229C3.83705 14.3841 3.79556 14.3309 3.77078 14.2696C3.746 14.2083 3.73892 14.1413 3.75035 14.0762L4.33763 10.6505C4.37642 10.4243 4.35961 10.1921 4.28866 9.9739C4.2177 9.75568 4.09472 9.55801 3.93033 9.39792L1.43989 6.97347C1.39229 6.9275 1.35856 6.86908 1.34254 6.80487C1.32652 6.74066 1.32886 6.67324 1.34928 6.6103C1.36971 6.54735 1.4074 6.49141 1.45807 6.44884C1.50874 6.40627 1.57035 6.37879 1.63587 6.36953L5.07889 5.86624C5.30571 5.83324 5.52111 5.74562 5.70656 5.61092C5.89201 5.47622 6.04194 5.29847 6.14346 5.09297L7.68266 1.97392Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                Watchlist</a>
                <a class="listing_btn-white p14">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M6.11222 9.057L10.8932 11.843M10.8862 4.157L6.11222 6.943M14.7992 3.1C14.7992 4.2598 13.859 5.2 12.6992 5.2C11.5394 5.2 10.5992 4.2598 10.5992 3.1C10.5992 1.9402 11.5394 1 12.6992 1C13.859 1 14.7992 1.9402 14.7992 3.1ZM6.39922 8C6.39922 9.1598 5.45902 10.1 4.29922 10.1C3.13942 10.1 2.19922 9.1598 2.19922 8C2.19922 6.8402 3.13942 5.9 4.29922 5.9C5.45902 5.9 6.39922 6.8402 6.39922 8ZM14.7992 12.9C14.7992 14.0598 13.859 15 12.6992 15C11.5394 15 10.5992 14.0598 10.5992 12.9C10.5992 11.7402 11.5394 10.8 12.6992 10.8C13.859 10.8 14.7992 11.7402 14.7992 12.9Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                Share</a>
            </div>
        </div>
    </div>
</section>
<section class="listing_images">
    <div class="container">
        <div class="listing_images-main">
            <img class="w-100" src="<?php echo IMG; ?>/1.jpg" alt="vehicle">
        </div>
        <div class="listing_images-slider"></div>
    </div>
</section>
<section class="listing_info">
    <div class="container">
        <h1><?php the_title(); ?></h1>
        <?php if ($short_text): ?>
                <p class="listing_info-subtitle p24"><?php echo $short_text; ?></p>
        <?php endif; ?>
        <div class="listing_divider"></div>
        <div class="listing_info-bid">
            <div>
                <p class="p17">Buyer's Premium applies (subject to a minimum charge and VAT)
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12.0007 15.6713V12.0007M12.0007 8.3301H12.0099M21.1772 12.0007C21.1772 17.0687 17.0687 21.1772 12.0007 21.1772C6.93266 21.1772 2.82422 17.0687 2.82422 12.0007C2.82422 6.93266 6.93266 2.82422 12.0007 2.82422C17.0687 2.82422 21.1772 6.93266 21.1772 12.0007Z" stroke="black" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></p>
            </div>
            <?php if ($estimate): ?>
            <div>
                <p class="p17">Estimate</p>
                <p class="gold-text"><?php echo $estimate; ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php 
            $specialist = get_field('specialist'); 

            if( $specialist ): 
                $spec_id = $specialist->ID;
                $name   = get_the_title($specialist->ID); 
                $role   = get_field('job_position', $specialist->ID); 
                $email  = get_field('team_email', $specialist->ID);
                $phone  = get_field('team_phone', $specialist->ID);
                $spec_img = get_the_post_thumbnail_url($spec_id, 'thumbnail'); 
                if( !$spec_img ) {
                    $spec_img = get_template_directory_uri() . '/images/face2.png';
                }
                $bio_url = get_permalink($specialist->ID);
            ?>
                <div class="listing_info-contact">
                    <div class="listing_info-contact-info">
                        <?php if( $spec_img ): ?>
                            <img src="<?php echo esc_url($spec_img); ?>" alt="<?php echo esc_attr($name); ?>">
                        <?php else: ?>
                            <img src="<?php echo IMG; ?>/face2.png" alt="<?php echo esc_attr($name); ?>">
                        <?php endif; ?>

                        <div>
                            <p class="listing_info-contact-subtitle">
                                If you would like to enquire further, please contact:
                            </p>
                            <p class="listing_info-contact-title"><?php echo esc_html($name); ?></p>
                            <?php if( $role ): ?>
                                <p class="listing_info-contact-subtitle">- <?php echo esc_html($role); ?></p>
                            <?php endif; ?>
                            <div>
                                <?php if( $email ): ?>
                                    <p class="listing_info-contact-text">
                                        Email: <span><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></span>
                                    </p>
                                <?php endif; ?>
                                <?php if( $phone ): ?>
                                    <p class="listing_info-contact-text">
                                        Tel: <span><?php echo esc_html($phone); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="listing_info-contact-btn w-100">
                        <a href="<?php echo esc_url($bio_url); ?>" class="listing_btn-white p14">
                            View Bio
                            <svg xmlns="http://www.w3.org/2000/svg" width="29" height="16" viewBox="0 0 29 16" fill="none">
                                <path d="M2.5 8H26.5M26.5 8L21.122 2M26.5 8L21.122 14" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <div class="listing_info-details">
            <?php if ($lot_details): ?>
                <div class="listing_info-details-first">
                    <h3>Lot Details</h3>
                    <div><?php echo $lot_details; ?></div>
                </div>
            <?php endif; ?>
            
            <div class="listing_info-details-tabs">
                <div class="faq_list">
                    <?php if( have_rows('vehicle_tabs') ): ?>
                        <ul id="listingTab" class="accordionjs">
                            <?php while( have_rows('vehicle_tabs') ): the_row(); 
                                $tab_title   = get_sub_field('vehicle_tabs_title');
                                $tab_content = get_sub_field('vehicle_tabs_content');
                            ?>
                                <li>
                                    <div>
                                        <h3><?php echo esc_html($tab_title); ?></h3>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="description">
                                            <?php echo wp_kses_post($tab_content); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($vehicle_video): ?>
            <div class="listing_info-image w-100">
                <video class="w-100" autoplay playsinline muted loop>
                    <source src="<?php echo $vehicle_video; ?>">
                </video>
            </div>
        <?php endif; ?>
        <div class="listing_info-share">
            <p>Share:</p>
            <div>
                <?php 
                $post_url   = urlencode( get_permalink() ); 
                $post_title = urlencode( get_the_title() ); 
                ?>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $post_url; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo IMG; ?>/linkedin.svg" alt="linkedin">
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo IMG; ?>/facebook.svg" alt="facebook">
                </a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener">
                    <img src="<?php echo IMG; ?>/instagram.svg" alt="instagram">
                </a>
                <a href="mailto:?subject=<?php echo $post_title; ?>&body=<?php echo $post_url; ?>">
                    <img src="<?php echo IMG; ?>/mail.svg" alt="email">
                </a>
            </div>
        </div>

        <?php if ($vehicle_notes): ?>
            <div class="listing_info-notes">
                <div class="listing_info-notes-head">
                    <p>Notes for Intending Purchases</p>
                </div>
                <div class="listing_info-notes-body">
                    <?php echo $vehicle_notes; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
<script>
    jQuery(document).ready(function($) {
        jQuery("#listingTab").accordionjs({
            closeAble: true,
            closeOther: true,
            slideSpeed: 150,
            activeIndex: 100
        });
    });
</script>