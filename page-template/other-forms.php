<?php
/*
    Template name: other-forms
*/

if (!isset($_GET['vehicle']) || !absint($_GET['vehicle'])) {
    wp_safe_redirect(home_url('/'));
    exit;
}

get_header();

$name = get_the_title();

get_banner('Homepage / Private Sales / ' . $name, '', $name);

$vehicle_id = isset($_GET['vehicle']) ? absint($_GET['vehicle']) : '';

?>

<section class="advertise">
    <div class="advertise_container">
        <div class="advertise_form apply_to_form mb0">
            <?php
            if (is_page('telephone-bid')) {
                echo do_shortcode('[gravityform id="6" title="true" ajax="true"]');
            } elseif (is_page('commision-bid')) {
                echo do_shortcode('[gravityform id="7" title="true" ajax="true"]');
            } elseif (is_page('request-condition-report')) {
                echo do_shortcode('[gravityform id="8" title="true" ajax="true"]');
            }
            ?>
        </div>
    </div>
</section>

<?php if (!empty($vehicle_id)): ?>
    <?php
    $vehicle_title = '';
    $vehicle_post = get_post($vehicle_id);

    if ($vehicle_post && $vehicle_post->post_type === 'vehicles') {
        $vehicle_title = get_the_title($vehicle_id);
    }

    if (!empty($vehicle_title)):
    ?>
        <script>
            function setVehicle() {
                setTimeout(() => {
                    if (document.querySelector('form [type="hidden"]')) {
                        document.querySelector('form [type="hidden"]').value = '<?php echo $vehicle_id; ?>';
                    }
                    if (document.querySelector('.lots_list input')) {
                        document.querySelector('.lots_list input').value = '<?php echo $vehicle_title; ?>';
                    }
                }, 500);
            }
            window.onpaint = setVehicle();
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php get_footer(); ?>