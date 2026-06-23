<?php
/*
    Template name: other-forms
*/

get_header();

$name = get_the_title();

get_banner('Homepage / ' . $name, '', $name);

$vehicle_id = isset($_GET['vehicle']) ? absint($_GET['vehicle']) : '';

?>

<style>
	.none{opacity:0 !important;pointer-events:none !important;user-select:none !important;}.text-center{text-align:center !important;}
	.decision .gchoice{align-items:center !important;}.decision .gchoice label{margin:0 !important;padding-left:10px;}
	.checklist{font-family:GothamMedium !important;font-weight:300 !important;line-height:normal !important;color:#000 !important;opacity:0.6 !important;}
	.text-center .gsection_title{display:block !important;font-family: GoudyTitlingSemiBold !important;font-weight:300 !important;text-transform:uppercase !important;color:black !important;}
	@media (min-width: 1420px){.text-center .gsection_title{font-size:1.42vw !important;}.checklist{font-size:.7291666667vw!important;letter-spacing:.0729166667vw!important;}
		.gform-theme--foundation .gfield textarea.large{height:20vw !important;min-block-size:20vw !important;}}
	@media (max-width: 1420px){.checklist{font-size:11.6px !important;}}
</style>

<section class="advertise">
    <div class="advertise_container">
        <div class="advertise_form apply_to_form mb0">
            <?php
            if (is_page('telephone-bid')) {
                echo do_shortcode('[gravityform id="8" title="true" ajax="true"]');
            } elseif (is_page('commision-bid')) {
                echo do_shortcode('[gravityform id="12" title="true" ajax="true"]');
            } elseif (is_page('request-condition-report')) {
                echo do_shortcode('[gravityform id="13" title="true" ajax="true"]');
            } elseif (is_page(201848)) {
                echo do_shortcode('[gravityform id="15" title="true" ajax="true"]');
            } elseif (is_page(201852)) {
                echo do_shortcode('[gravityform id="16" title="true" ajax="true"]');
            } elseif (is_page(201855)) {
                echo do_shortcode('[gravityform id="17" title="true" ajax="true"]');
            }
            ?>
        </div>
    </div>
</section>

<?php if (!empty($vehicle_id)): ?>
    <?php
    $vehicle_title = '';
    $lot_number = '';
    $vehicle_post = get_post($vehicle_id);

    if ($vehicle_post && $vehicle_post->post_type === 'vehicles') {
        $vehicle_title = get_the_title($vehicle_id);
        $lot_number = get_field('lot_number_latest', $vehicle_id) ?: null;
    }

    if (!empty($vehicle_title)):
    ?>
        <script>
            const vehicleTitle = <?php echo json_encode(html_entity_decode($vehicle_title, ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>;
            console.log(vehicleTitle);
            function setVehicle() {
                setTimeout(() => {
                    if (document.querySelector('.lots_list .gfield_list_group_item:nth-child(1) input')) {
                        document.querySelector('.lots_list .gfield_list_group_item:nth-child(1) input').value = '<?php echo $lot_number; ?>';
                    }
                    if (document.querySelector('.lots_list .gfield_list_group_item:nth-child(2) input')) {
                        document.querySelector('.lots_list .gfield_list_group_item:nth-child(2) input').value = vehicleTitle;
                    }
                }, 500);
            }
            window.onpaint = setVehicle();
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php get_footer(); ?>