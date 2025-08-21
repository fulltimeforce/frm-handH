<?php
/*
  Template name: buying at auction
*/

get_header();

$bg_image = get_field('buying_at_auction_hero_image');
$subtitle = get_field('buying_at_auction_hero_subtitle');
$text = get_field('buying_at_auction_hero_text');
$button = get_field('buying_at_auction_hero_button');

get_banner('Homepage / Classic Auctions / Buying at auction', esc_url($bg_image), 'Buying at auction');
?>

<div class="buying_at_auction_page">
  <div class="container">
    <section class="how-to-buy">
      <h2>How to Buy at Auction with H&H Classics</h2>
      <p>Register for free in advance to enjoy the flexibility of bidding in person, online, or by phone—whichever suits you best! Follow the guide below, and if you need any assistance, feel free to give us a call. We're here to ensure a seamless experience! +44 (0)1925 210035 or email info@handh.co.uk</p>
      <div class="cta_links">
        <a href="">Get a complimentary Valuation</a>
        <a href="">View Our Upcoming Auctions</a>
      </div>
    </section>

    <section class="auction-tabs">
  <div class="container">
    <!-- Tabs Navigation -->
    <ul class="auction-tabs-nav">
      <li class="active" data-tab="before">Before the Auction</li>
      <li data-tab="bidding">Bidding at Auction</li>
      <li data-tab="after">After the Auction</li>
    </ul>

    <!-- Tabs Content -->
    <div class="auction-tabs-content">
      
      <!-- TAB 1 -->
      <div class="tab-panel active" id="before">
        <div class="auction-grid">
          <div class="auction-image">
            <img src="https://via.placeholder.com/600x400" alt="Auction">
          </div>
          <div class="auction-info">
            <h2>Register & Get Started</h2>
            <p><strong>FREE Online Registration:</strong> Register for free by clicking "Register To Bid/Sign In" at the top of this page. Please note that online bidding incurs an additional fee of 1% + VAT.</p>
            <p><strong>FREE In-Person Registration:</strong> Prefer to register at the venue? Simply bring a photo ID to the auction and sign up as a bidder upon arrival.</p>

            <div class="auction-cards">
              <div class="auction-card">
                <h3>Get the information you need</h3>
                <p>While you may already know what you're interested in...</p>
              </div>
              <div class="auction-card">
                <h3>Attending in person?</h3>
                <p>You can inspect every lot and review any supporting documents...</p>
              </div>
              <div class="auction-card">
                <h3>Can’t attend?</h3>
                <p>No problem—just request Condition Reports...</p>
              </div>
              <div class="auction-card">
                <h3>We are here to help:</h3>
                <p>Buying a classic might feel intimidating...</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2 -->
      <div class="tab-panel" id="bidding">
        <div class="auction-grid">
          <div class="auction-image">
            <img src="https://via.placeholder.com/600x400" alt="Bidding">
          </div>
          <div class="auction-info">
            <h2>Bidding at Auction</h2>
            <p>Second tab content...</p>
          </div>
        </div>
      </div>

      <!-- TAB 3 -->
      <div class="tab-panel" id="after">
        <div class="auction-grid">
          <div class="auction-image">
            <img src="https://via.placeholder.com/600x400" alt="After">
          </div>
          <div class="auction-info">
            <h2>After the Auction</h2>
            <p>Third tab content...</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

    <section class="insurance">
      <div clas="insurance_banner">
        <h2>INSURANCE</h2>
      </div>
      <p>You’ll also need to think about insuring your new vehicle. At H&H Classics we aim to make every aspect of buying a classic vehicle as straightforward as possible which is why we’re delighted to be able to introduce a new insurance quote and comparison service. Click the button below for more information.</p>
      <div class="cta_links">
        <a href="">Get an Insurance Quote</a>
      </div>
      <hr/>
      <h3>Thinking of Selling?</h3>
      <div class="cta_links">
        <a href="">View Welcome Booklet</a>
      </div>
    </section>
  </div>
</div>

<?php get_template_part('inc/sections/cta'); ?>

<div class="buying_at_auction_page">
  <div class="container">
      
    <section class="upcoming" id="upcoming-auctions">
      <?php get_template_part('inc/sections/upcoming'); ?>
    </section>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".auction-tabs-nav li");
  const panels = document.querySelectorAll(".tab-panel");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      const target = tab.getAttribute("data-tab");

      // remove active
      tabs.forEach(t => t.classList.remove("active"));
      panels.forEach(p => p.classList.remove("active"));

      // add active
      tab.classList.add("active");
      document.getElementById(target).classList.add("active");
    });
  });
});
</script>

<?php get_footer(); ?>

