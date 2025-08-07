<?php get_header(); ?>

<section class="page_404">
    <div class="container">
        <div class="page_404-title">
            <h1>404 Page Not Found</h1>
        </div>
        <div class="page_404-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="65" height="64" viewBox="0 0 65 64" fill="none">
                <path d="M16.5026 26.6654H11.1693C9.75478 26.6654 8.39823 26.1035 7.39803 25.1033C6.39784 24.1031 5.83594 22.7465 5.83594 21.332V10.6654C5.83594 9.25088 6.39784 7.89432 7.39803 6.89413C8.39823 5.89393 9.75478 5.33203 11.1693 5.33203H53.8359C55.2504 5.33203 56.607 5.89393 57.6072 6.89413C58.6074 7.89432 59.1693 9.25088 59.1693 10.6654V21.332C59.1693 22.7465 58.6074 24.1031 57.6072 25.1033C56.607 26.1035 55.2504 26.6654 53.8359 26.6654H48.5026M16.5026 37.332H11.1693C9.75478 37.332 8.39823 37.8939 7.39803 38.8941C6.39784 39.8943 5.83594 41.2509 5.83594 42.6654V53.332C5.83594 54.7465 6.39784 56.1031 7.39803 57.1033C8.39823 58.1035 9.75478 58.6654 11.1693 58.6654H53.8359C55.2504 58.6654 56.607 58.1035 57.6072 57.1033C58.6074 56.1031 59.1693 54.7465 59.1693 53.332V42.6654C59.1693 41.2509 58.6074 39.8943 57.6072 38.8941C56.607 37.8939 55.2504 37.332 53.8359 37.332H48.5026M16.5026 15.9987H16.5293M16.5026 47.9987H16.5293M35.1693 15.9987L24.5026 31.9987H40.5026L29.8359 47.9987" stroke="#8C6E47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h2>Oops! The page you are looking for does not exist. It may have been removed or renamed.</h2>
            <a href="<?php echo esc_url(home_url('/')) ?>">Return to Homepage</a>
        </div>
        <div class="page_404-cta">
            <p>If you believe this is an error, please contact our support team for assistance.</p>
            <a href="<?php echo esc_url(home_url('contact')) ?>">Contact Support</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>