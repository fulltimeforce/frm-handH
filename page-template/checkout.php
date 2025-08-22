<?php
/*
    Template name: checkout
*/

get_header();

?>

<?php if (!isset($_GET['key'])): ?>
    <section class="checkout_page">
        <div class="container">
            <div class="checkout_page-title">
                <h1>Checkout</h1>
            </div>
            <div class="checkout_page-body">
                <?php echo do_shortcode('[woocommerce_checkout]'); ?>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="page_404">
        <div class="container">
            <div class="page_404-title">
                <h1>Thank you for your purchase!</h1>
            </div>
            <div class="page_404-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="65" height="64" viewBox="0 0 65 64" fill="none">
                    <path d="M15.9693 30.132L5.83594 58.6654L34.3693 48.5587M11.1693 7.9987H11.1959M59.1693 21.332H59.1959M40.5026 5.33203H40.5293M59.1693 53.332H59.1959M59.1693 5.33203L53.1959 7.33203C51.4956 7.8984 50.0448 9.03774 49.0915 10.5553C48.1381 12.0729 47.7415 13.8744 47.9693 15.652C48.2359 17.9454 46.4493 19.9987 44.1026 19.9987H43.0893C40.7959 19.9987 38.8226 21.5987 38.3959 23.8387L37.8359 26.6654M59.1693 34.6654L56.9826 33.7854C54.6893 32.8787 52.1293 34.3187 51.7026 36.7454C51.4093 38.612 49.7826 39.9987 47.8893 39.9987H45.8359M29.8359 5.33203L30.7159 7.5187C31.6226 9.81203 30.1826 12.372 27.7559 12.7987C25.8893 13.0654 24.5026 14.7187 24.5026 16.612V18.6654M29.8359 34.6654C34.9826 39.812 37.3826 45.7854 35.1693 47.9987C32.9559 50.212 26.9826 47.812 21.8359 42.6654C16.6893 37.5187 14.2893 31.5454 16.5026 29.332C18.7159 27.1187 24.6893 29.5187 29.8359 34.6654Z" stroke="#8C6E47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <h2>We’re getting your order ready to be shipped. We will notify you when it has been sent.</h2>
                <a href="<?php echo esc_url(home_url('/')) ?>">Return to Homepage</a>
            </div>
            <div class="page_404-cta">
                <h3>Please note catalogues are sent out one week prior to sale</h3>
                <br>
                <p>If you order your catalogue online within three days of the auction, you will need to print out your receipt and use it to collect your catalogue on the door.</p>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>