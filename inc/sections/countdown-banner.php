<?php
// Obtener campos globales de ACF Options
$countdown_text = get_field('global_countdown_text', 'options');
$countdown_btn_bid = get_field('global_countdown_how_to_bid', 'options');

// Obtener la fecha y hora actual en la timezone de WordPress (UK)
$current_timestamp = current_time('timestamp');
$current_date_only = current_time('Y-m-d');

// Obtener todos los auctions
$args = array(
  'post_type' => 'auction',
  'posts_per_page' => -1,
  'post_status' => 'publish',
  'meta_key' => 'auction_date',
  'orderby' => 'meta_value',
  'order' => 'ASC',
);

$auctions_query = new WP_Query($args);
$upcoming_auction = null;
$venue_name = '';
$target_date = '';
$target_timestamp = 0;
$auction_permalink = '#';

if ($auctions_query->have_posts()) {
  while ($auctions_query->have_posts()) {
    $auctions_query->the_post();
    $auction_date = get_field('auction_date');

    if (!empty($auction_date)) {
      // Convertir la fecha del auction a timestamp (en la timezone de WordPress)
      // La fecha viene en formato: 2026-03-25 15:00:00 (hora UK)
      $auction_timestamp = strtotime($auction_date);

      // Extraer solo la fecha (sin hora) para comparaciones de día
      $auction_date_only = substr($auction_date, 0, 10);

      // Si la fecha del auction es hoy o en el futuro
      if ($auction_timestamp >= $current_timestamp || $auction_date_only >= $current_date_only) {
        $upcoming_auction = get_the_ID();
        $target_date = $auction_date;
        $target_timestamp = $auction_timestamp;
        $auction_permalink = get_permalink();

        // Obtener el venue
        $venue_id = get_field('template_venue');
        if ($venue_id) {
          $venue_name = get_the_title($venue_id);
        }

        break; // Tomamos el primer auction (el más próximo)
      }
    }
  }
  wp_reset_postdata();
}

// Si no hay auction próximo, no mostrar el banner
if (!$upcoming_auction) {
  return;
} ?>

<section class="countdown-banner" id="countdown-banner">
  <div class="countdown-banner__container">
    <!-- Venue Section -->
    <div class="countdown-banner__venue">
      <div class="countdown-banner__venue-label">
        <span>Auction Venue</span>
      </div>
      <p class="countdown-banner__venue-name">
        <?php echo esc_html($venue_name); ?>
      </p>
    </div>

    <!-- Countdown Timer Section -->
    <div class="countdown-banner__timer">
      <?php if (!empty($countdown_text)): ?>
        <p class="countdown-banner__timer-title">
          <?= esc_html($countdown_text) ?>
        </p>
      <?php endif; ?>
      <div class="countdown-banner__countdown" data-target-date="<?php echo esc_attr($target_date); ?>"
        data-target-timestamp="<?php echo esc_attr($target_timestamp); ?>">
        <div class="countdown-banner__time-unit">
          <div class="countdown-banner__time-value" data-countdown="days">040</div>
          <div class="countdown-banner__time-label">Day(s)</div>
        </div>
        <div class="countdown-banner__separator">:</div>
        <div class="countdown-banner__time-unit">
          <div class="countdown-banner__time-value" data-countdown="hours">09</div>
          <div class="countdown-banner__time-label">Hour(s)</div>
        </div>
        <div class="countdown-banner__separator">:</div>
        <div class="countdown-banner__time-unit">
          <div class="countdown-banner__time-value" data-countdown="minutes">21</div>
          <div class="countdown-banner__time-label">Minutes(s)</div>
        </div>
        <div class="countdown-banner__separator">:</div>
        <div class="countdown-banner__time-unit">
          <div class="countdown-banner__time-value" data-countdown="seconds">32</div>
          <div class="countdown-banner__time-label">Seconds(s)</div>
        </div>
      </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="countdown-banner__actions">
      <?php if (!empty($countdown_btn_bid)):
        $btn_text = $countdown_btn_bid['text'] ?: 'How to Bid';
        $btn_url = $countdown_btn_bid['page'] ?: '#'; ?>
        <a href="<?php echo esc_url($btn_url); ?>" class="countdown-banner__btn countdown-banner__btn--outline">
          <?php echo esc_html($btn_text); ?>
        </a>
      <?php endif; ?>

      <a href="<?php echo esc_url($auction_permalink); ?>" class="countdown-banner__btn countdown-banner__btn--primary">
        View Auction
      </a>
    </div>
  </div>
</section>

<style>
  .animate-alert {
    display: inline-flex;
    align-items: center;
    animation: livePulse 1.6s ease-in-out infinite;
  }

  @keyframes livePulse {
    0% {
      opacity: 1;
      transform: scale(1);
    }

    50% {
      opacity: 0.4;
      transform: scale(1.08);
    }

    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  .countdown-banner {
    background: rgba(140, 110, 71, 0.9);
    width: 100%;
    padding: 24px 178px 24px 356px;
    color: white;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 99;
  }

  .countdown-banner__container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-inline: auto;
  }

  .countdown-banner__venue {
    flex: 1;
  }

  .countdown-banner__venue-label {
    display: flex;
    align-items: center;
    gap: 12px;
    height: 14px;
    margin-bottom: 18px;
  }

  .countdown-banner__venue-label::before {
    content: '';
    width: 20px;
    height: 1px;
    background: white;
    flex-shrink: 0;
  }

  .countdown-banner__venue-label span {
    font-family: GothamLight;
    font-size: 13px;
    letter-spacing: 1.3px;
    text-transform: uppercase;
    white-space: nowrap;
    line-height: 1;
  }

  .countdown-banner__venue-name {
    font-family: GoudyTitlingSemiBold;
    font-weight: 400;
    font-size: 24px;
    line-height: 1.15;
    text-transform: uppercase;
    margin: 0 !important;
  }

  .countdown-banner__timer {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    width: 604px;
    flex-shrink: 0;
  }

  .countdown-banner__timer-title {
    font-family: GothamLight;
    font-size: 20px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    text-align: center;
    margin: 0 !important;
  }

  .countdown-banner__countdown {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 16px;
    width: 100%;
    font-family: GothamMedium;
    font-weight: 300;
  }

  .countdown-banner__time-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    width: 120px;
    text-align: center;
  }

  .countdown-banner__time-value {
    font-size: 29px;
    letter-spacing: 0.58px;
    line-height: 1;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .countdown-banner__time-label {
    font-size: 13px;
    letter-spacing: 0.26px;
    height: 12px;
    font-family: 'GothamLight';
  }

  .countdown-banner__separator {
    font-size: 29px;
    letter-spacing: 0.58px;
    line-height: 1;
    height: 22px;
    display: flex;
    align-items: center;
  }

  .countdown-banner__actions {
    display: flex;
    align-items: center;
    gap: 18px;
    width: 480px;
    justify-content: flex-end;
    flex-shrink: 0;
  }

  .countdown-banner__countdown.is-hidden {
    display: none !important;
  }

  .countdown-banner__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 22px 16px;
    font-family: GothamMedium;
    font-weight: 300;

    font-size: 16px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    text-decoration: none;
    color: white;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }

  .countdown-banner__btn--outline {
    background: transparent;
  }

  .countdown-banner__btn--primary {
    background: #a38b6c;
    width: 240px;
  }

  .countdown-banner__btn--primary:hover {
    background: #fff;
    color: #000;

  }

  /* Relativo al viewport */
  @media (min-width: 1201px) {
    .countdown-banner {
      padding: calc(24 / 1920 * 100vw) calc(178 / 1920 * 100vw) calc(24 / 1920 * 100vw) calc(356 / 1920 * 100vw);
    }

    .countdown-banner__container {
      gap: calc(20 / 1920 * 100vw);
    }

    .countdown-banner__venue-label {
      gap: calc(12 / 1920 * 100vw);
      height: calc(14 / 1920 * 100vw);
      margin-bottom: calc(18 / 1920 * 100vw);
    }

    .countdown-banner__venue-label::before {
      width: calc(20 / 1920 * 100vw);
      height: calc(1 / 1920 * 100vw);
    }

    .countdown-banner__venue-label span {
      font-size: calc(13 / 1920 * 100vw);
      letter-spacing: calc(1.3 / 1920 * 100vw);
    }

    .countdown-banner__venue-name {
      font-size: calc(24 / 1920 * 100vw);
    }

    .countdown-banner__timer {
      gap: calc(18 / 1920 * 100vw);
      width: calc(604 / 1920 * 100vw);
    }

    .countdown-banner__timer-title {
      font-size: calc(20 / 1920 * 100vw);
      letter-spacing: calc(0.4 / 1920 * 100vw);
    }

    .countdown-banner__countdown {
      gap: calc(16 / 1920 * 100vw);
    }

    .countdown-banner__time-unit {
      gap: calc(12 / 1920 * 100vw);
      width: calc(120 / 1920 * 100vw);
    }

    .countdown-banner__time-value {
      font-size: calc(29 / 1920 * 100vw);
      letter-spacing: calc(0.58 / 1920 * 100vw);
      height: calc(22 / 1920 * 100vw);
    }

    .countdown-banner__time-label {
      font-size: calc(13 / 1920 * 100vw);
      letter-spacing: calc(0.26 / 1920 * 100vw);
      height: calc(12 / 1920 * 100vw);
    }

    .countdown-banner__separator {
      font-size: calc(29 / 1920 * 100vw);
      letter-spacing: calc(0.58 / 1920 * 100vw);
      height: calc(22 / 1920 * 100vw);
    }

    .countdown-banner__actions {
      gap: calc(18 / 1920 * 100vw);
      width: calc(480 / 1920 * 100vw);
    }

    .countdown-banner__btn {
      padding: calc(22 / 1920 * 100vw) calc(16 / 1920 * 100vw);
      font-size: calc(16 / 1920 * 100vw);
      letter-spacing: calc(0.8 / 1920 * 100vw);
    }

    .countdown-banner__btn--primary {
      width: calc(240 / 1920 * 100vw);
    }
  }

  /* Responsive Styles */
  @media (max-width: 1420px) {}

  @media (max-width: 1200px) {
    .countdown-banner {
      padding: 24px 16px;
      position: relative;
      background-color: #7E6340;
    }

    .countdown-banner__container {
      flex-direction: column;
      justify-content: center;
      display: grid;
      grid-template-columns: 1fr 1fr;
      max-width: 768px;
    }

    .countdown-banner__venue {
      align-items: center;
    }

    .countdown-banner__venue-label {}

    .countdown-banner__venue-name {
      font-size: 20px;
    }

    .countdown-banner__timer {
      width: auto;
    }

    .countdown-banner__timer-title {
      font-size: 18px;
    }

    .countdown-banner__countdown {
      gap: 12px;
    }

    .countdown-banner__time-unit {
      width: 80px;
    }

    .countdown-banner__time-value {
      font-size: 24px;
    }

    .countdown-banner__time-label {
      font-size: 11px;
    }

    .countdown-banner__separator {
      font-size: 24px;
    }

    .countdown-banner__actions {
      justify-content: center;
      gap: 12px;
      width: 100%;
      grid-column: 1 / -1;
    }

    .countdown-banner__btn {
      font-size: 14px;
      padding: 18px 16px;
    }

    section.first-section:not(.centered) {
      margin-top: -87px;
    }
  }

  @media (max-width: 1024px) {
    .countdown-banner {
      top: 86.25px;
      padding: 24px 16px;
    }

    .countdown-banner__venue-label span {
      font-size: 11px;
    }

    .countdown-banner__venue-name {
      font-size: 18px;
    }

    .countdown-banner__timer-title {
      font-size: 16px;
    }

    .countdown-banner__countdown {
      gap: 8px;
      flex-wrap: wrap;
    }

    .countdown-banner__time-unit {
      width: 70px;
    }

    .countdown-banner__time-value {
      font-size: 20px;
    }

    .countdown-banner__time-label {
      font-size: 10px;
    }

    .countdown-banner__separator {
      display: none;
    }

    .countdown-banner__btn {
      font-size: 14px;
      padding: 18px 16px;
    }
  }

  @media (max-width: 768px) {
    .countdown-banner__container {
      display: flex;
    }

    .countdown-banner__venue-label {
      margin-bottom: 12px;
      justify-content: center;
    }

    .countdown-banner__venue-name {
      text-align: center;
    }

    .countdown-banner__timer {
      gap: 12px;
    }

    .countdown-banner__actions {
      flex-direction: column;
      gap: 3px;
    }

    .countdown-banner__timer-title svg {
      margin-right: 0 !important;
    }

  }
</style>

<!-- countdown script -->
<script>
  (function () {
    const countdownEl = document.querySelector('.countdown-banner__countdown');
    const timerTitleEl = document.querySelector('.countdown-banner__timer-title');
    if (!countdownEl) return;

    const targetDateStr = countdownEl.getAttribute('data-target-date');
    // Usar el timestamp que viene de PHP (ya está en la timezone correcta de UK)
    const targetTimestamp = parseInt(countdownEl.getAttribute('data-target-timestamp'));
    const targetDate = targetTimestamp * 1000; // Convertir a milisegundos

    const daysEl = countdownEl.querySelector('[data-countdown="days"]');
    const hoursEl = countdownEl.querySelector('[data-countdown="hours"]');
    const minutesEl = countdownEl.querySelector('[data-countdown="minutes"]');
    const secondsEl = countdownEl.querySelector('[data-countdown="seconds"]');

    // Obtener la fecha del evento (sin hora) para comparar días
    const targetDateObj = new Date(targetDate);
    const eventDateOnly = new Date(targetDateObj.getFullYear(), targetDateObj.getMonth(), targetDateObj.getDate());

    let isLive = false;
    let eventDayEnded = false;

    function padZero(num, size = 2) {
      return String(num).padStart(size, '0');
    }

    function updateCountdown() {
      const now = new Date().getTime();
      const currentDate = new Date();
      const currentDateOnly = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());

      const distance = targetDate - now;

      // Si el evento ya pasó (countdown llegó a 0 o menos)
      if (distance <= 0 && !isLive) {
        // Estamos en el día del evento y la hora ya pasó - mostrar LIVE
        if (currentDateOnly.getTime() === eventDateOnly.getTime()) {
          countdownEl.classList.add('is-hidden');
          if (timerTitleEl) {
            timerTitleEl.innerHTML = `
              <span class="animate-alert">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 512 300"
                  width="40"
                  height="24"
                  aria-hidden="true"
                  role="img"
                  style="display: inline-block; vertical-align: middle; margin-right: 8px;"
                >
                  <g fill="none" stroke="#ffffff" stroke-linecap="round">
                    <circle cx="256" cy="150" r="70" fill="#ffffff" stroke="none" />
                    <path d="M180 95 C150 125, 150 175, 180 205" stroke-width="16" />
                    <path d="M332 95 C362 125, 362 175, 332 205" stroke-width="16" />
                    <path d="M110 55 C60 110, 60 190, 110 245" stroke-width="34" />
                    <path d="M402 55 C452 110, 452 190, 402 245" stroke-width="34" />
                  </g>
                </svg>
                LIVE
              </span>
            `;
          }
          isLive = true;
        }

        daysEl.textContent = '00';
        hoursEl.textContent = '00';
        minutesEl.textContent = '00';
        secondsEl.textContent = '00';

        return;
      }

      // Si ya pasó el día del evento (es el día siguiente o posterior)
      if (currentDateOnly.getTime() > eventDateOnly.getTime() && !eventDayEnded) {
        eventDayEnded = true;
        // Recargar la página para mostrar el siguiente auction
        window.location.reload();
        return;
      }

      // Countdown normal
      if (distance > 0) {
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        daysEl.textContent = days === 0 ? '00' : padZero(days, 3);
        hoursEl.textContent = padZero(hours);
        minutesEl.textContent = padZero(minutes);
        secondsEl.textContent = padZero(seconds);
      }
    }

    // Update immediately
    updateCountdown();

    // Update every second
    setInterval(updateCountdown, 1000);
  })();
</script>

<!-- UI Section -->
<script>
  function positionBannerBelowHeader() {
    const header = document.querySelector('body > .header');
    const banner = document.querySelector('#countdown-banner');
    if (!header || !banner) return;

    if (window.innerWidth >= 1024) {
      const headerHeight = header.offsetHeight;
      banner.style.top = headerHeight + 'px';
    } else {
      banner.style.top = '';
    }
  }

  function applyBannerMarginToNextSection() {
    const banner = document.querySelector('#countdown-banner');
    const body = document.querySelector('body');
    if (!banner || !body) return;

    if (window.innerWidth >= 1201) {
      const bannerHeight = banner.offsetHeight;
      body.style.marginTop = bannerHeight + 'px';
    } else {
      body.style.marginTop = '';
    }
  }

  function addClassToNextSection() {
    const nextSection = document.querySelector('#countdown-banner ~ section');
    if (!nextSection) return;
    nextSection.classList.add('first-section');
  }

  document.addEventListener('DOMContentLoaded', () => {
    positionBannerBelowHeader();
    addClassToNextSection();
    applyBannerMarginToNextSection();

    // Observer para detectar cambios de tamaño en el header
    const header = document.querySelector('body > .header');
    if (header && window.ResizeObserver) {
      const headerResizeObserver = new ResizeObserver(() => {
        positionBannerBelowHeader();
      });
      headerResizeObserver.observe(header);
    }

    // Observer para detectar cambios de tamaño en el banner
    const banner = document.querySelector('#countdown-banner');
    if (banner && window.ResizeObserver) {
      const bannerResizeObserver = new ResizeObserver(() => {
        applyBannerMarginToNextSection();
      });
      bannerResizeObserver.observe(banner);
    }
  });

  window.addEventListener('resize', () => {
    positionBannerBelowHeader();
    applyBannerMarginToNextSection();
  });
</script>