<?php
/**
 * Template Name: Pre-Order Standalone Hero
 * Full-viewport Hero layout tailored with RAKA branding and the theme's cinematic fullscreen menu overlay.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_url       = get_template_directory_uri() . '/assets/images/a9-horizontal.mp4';
$logo_horizontal = get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png';
$logo_icon       = get_template_directory_uri() . '/assets/images/logo-raka-icon.png';
$shop_url        = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' );
$account_url     = home_url( '/account' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> lang="en">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php wp_title( '|', true, 'right' ); ?> <?php bloginfo( 'name' ); ?> — Pre-Order Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
  <?php wp_head(); ?>
  <style>
    :root {
      --ink: #0d0f0f;
      --ink-soft: #5b6260;
      --line: #e8ebea;
      --panel: #ffffff;
      --box: #f5f7f6;
      --radius-panel: 30px;
      --radius-box: 18px;
      --pad: clamp(16px, 2vw, 32px);
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      margin: 0 !important;
      padding: 0 !important;
      width: 100%;
      height: 100%;
    }

    body {
      font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      color: var(--ink);
      background: #eef2f1;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
      -webkit-tap-highlight-color: transparent;
    }

    html.nav-lock, body.nav-lock {
      overflow: hidden !important;
    }

    img {
      max-width: 100%;
      display: block;
    }

    button {
      font: inherit;
      color: inherit;
      border: 0;
      background: none;
      cursor: pointer;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* ═════════ 5. LAYOUT CSS ═════════ */
    .hero {
      position: relative;
      height: 100vh;
      height: 100svh;
      min-height: 640px;
      width: 100%;
      overflow: hidden;
      isolation: isolate;
    }

    .hero__bg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -2;
      transform-origin: center;
      animation: in-bg 1.35s cubic-bezier(.16, 1, .3, 1) both;
    }

    .hero__scrim {
      position: absolute;
      inset: 0;
      z-index: -1;
      background: linear-gradient(90deg, rgba(255, 255, 255, .55) 0%, rgba(255, 255, 255, .10) 45%, rgba(255, 255, 255, 0) 70%);
      animation: in-fade 1s ease both .08s;
    }

    .hero__inner {
      position: relative;
      height: 100%;
      width: 100%;
      padding: clamp(10px, 1.1vw, 20px);
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.72fr);
      gap: clamp(10px, 1.1vw, 20px);
    }

    .panel {
      position: relative;
      background: var(--panel);
      border-radius: var(--radius-panel);
      padding: var(--pad);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 0;
      box-shadow: 0 24px 60px -30px rgba(16, 32, 28, .28);
      overflow-y: auto;
      overscroll-behavior: contain;
    }

    /* ═════════ 6. TOPBAR ═════════ */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex: 0 0 auto;
    }

    .brand {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .brand img {
      height: clamp(22px, 1.8vw, 28px);
      width: auto;
      object-fit: contain;
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: clamp(14px, 2vw, 36px);
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 6px 4px;
      margin: -6px -4px;
      position: relative;
      z-index: 50;
      cursor: pointer;
    }

    .menu__bars {
      display: flex;
      flex-direction: column;
      gap: 5px;
      width: 28px;
    }

    .menu__bars span {
      display: block;
      height: 2px;
      background: var(--ink);
      border-radius: 2px;
      transform-origin: center;
      transition: transform .5s cubic-bezier(.16, 1, .3, 1), width .4s cubic-bezier(.16, 1, .3, 1), opacity .3s ease;
    }

    .menu:hover .menu__bars span:nth-child(2) {
      width: 70%;
    }

    .menu__label {
      font-size: clamp(13px, 0.95vw, 15px);
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .connect {
      display: flex;
      align-items: center;
      gap: clamp(8px, .7vw, 12px);
      border: 1px solid var(--line);
      border-radius: 999px;
      padding: 5px 6px 5px clamp(12px, 1.1vw, 18px);
    }

    .connect__label {
      font-size: clamp(10px, .78vw, 12px);
      font-weight: 600;
      letter-spacing: .09em;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .socials {
      display: flex;
      gap: 5px;
    }

    .social {
      width: clamp(26px, 2vw, 32px);
      height: clamp(26px, 2vw, 32px);
      border-radius: 50%;
      background: var(--ink);
      color: #fff;
      display: grid;
      place-items: center;
      transition: opacity .2s ease;
    }

    .social:hover {
      opacity: 0.85;
    }

    .social svg {
      width: 52%;
      height: 52%;
      fill: currentColor;
    }

    /* ═════════ 7. STAGE ═════════ */
    .stage {
      flex: 1 1 auto;
      min-height: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      padding: clamp(10px, 1.4vh, 20px) 0;
      width: 100%;
    }

    .stage__emblem {
      width: clamp(48px, 4.2vw, 68px);
      height: clamp(48px, 4.2vw, 68px);
      border-radius: 50%;
      background: var(--box);
      border: 1px solid var(--line);
      display: grid;
      place-items: center;
      margin-bottom: clamp(8px, 1.2vh, 14px);
      box-shadow: 0 8px 24px -10px rgba(0,0,0,0.12);
      flex: 0 0 auto;
    }

    .stage__emblem img {
      width: 60%;
      height: 60%;
      object-fit: contain;
    }

    .headline {
      margin: 0;
      font-size: clamp(24px, 2.7vw, 38px);
      line-height: 1.1;
      letter-spacing: -.04em;
      font-weight: 400;
    }

    .headline b {
      font-weight: 800;
    }

    .squiggle {
      width: clamp(140px, 15vw, 240px);
      margin: clamp(5px, 0.8vh, 10px) auto clamp(10px, 1.4vh, 16px);
      display: block;
      color: var(--ink);
    }

    .stage__form-mount {
      width: 100%;
      max-width: 520px;
      margin: 0 auto;
      text-align: left;
    }

    /* ═════════ 8. BOTTOM ROW — 3 CARDS ═════════ */
    .cards {
      flex: 0 0 auto;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: clamp(8px, 0.85vw, 14px);
      margin-top: clamp(8px, 1.2vh, 14px);
    }

    .card {
      background: var(--box);
      border-radius: var(--radius-box);
      padding: clamp(10px, 1vw, 16px);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: clamp(8px, 1.2vh, 14px);
      min-height: clamp(120px, 14vh, 160px);
      border: 1px solid var(--line);
    }

    .card__top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 8px;
    }

    .card__title {
      font-size: clamp(11.5px, 0.88vw, 13.5px);
      font-weight: 600;
      line-height: 1.3;
      letter-spacing: -.015em;
      margin: 0;
      flex: 1 1 auto;
      min-width: 0;
      text-wrap: pretty;
    }

    .info {
      width: clamp(18px, 1.4vw, 22px);
      height: clamp(18px, 1.4vw, 22px);
      border-radius: 50%;
      background: var(--ink);
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 10px;
      font-weight: 700;
      font-style: italic;
      flex: 0 0 auto;
    }

    .card__row {
      display: flex;
      align-items: center;
      gap: clamp(8px, .75vw, 12px);
    }

    .bubble {
      width: clamp(28px, 2.2vw, 36px);
      height: clamp(28px, 2.2vw, 36px);
      border: 1px solid #d9dedc;
      border-radius: 50%;
      display: grid;
      place-items: center;
      flex: 0 0 auto;
      color: var(--ink);
    }

    .bubble svg {
      width: 50%;
      height: 50%;
    }

    .card__note {
      font-size: clamp(10.5px, 0.75vw, 12px);
      line-height: 1.4;
      color: var(--ink-soft);
      margin: 0;
      flex: 1 1 auto;
      min-width: 0;
      text-wrap: pretty;
    }

    .stats {
      display: flex;
      align-items: flex-end;
      gap: clamp(8px, .9vw, 16px);
    }

    .stat__num {
      font-size: clamp(22px, 2.3vw, 34px);
      font-weight: 700;
      letter-spacing: -.05em;
      line-height: 1;
    }

    .stat--muted .stat__num, .stat--muted .stat__label {
      color: #b6bfbc;
    }

    .stat__label {
      display: block;
      margin-top: 4px;
      font-size: clamp(9.5px, 0.7vw, 11px);
      color: var(--ink-soft);
    }

    .stats__div {
      width: 1px;
      align-self: stretch;
      background: #dfe4e2;
      margin-bottom: 4px;
    }

    .teaser {
      display: flex;
      align-items: center;
      gap: clamp(8px, .8vw, 14px);
      justify-content: space-between;
    }

    .teaser__text {
      font-size: clamp(11.5px, 0.85vw, 13.5px);
      line-height: 1.3;
      color: var(--ink);
      margin: 0;
    }

    .teaser__thumb {
      position: relative;
      width: clamp(56px, 4.8vw, 76px);
      aspect-ratio: 1/1.06;
      border-radius: 10px;
      overflow: hidden;
      flex: 0 0 auto;
      background: #e3ecea;
    }

    .teaser__thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .play {
      position: absolute;
      inset: 0;
      margin: auto;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: var(--ink);
      color: #fff;
      display: grid;
      place-items: center;
      box-shadow: 0 4px 12px -4px rgba(0, 0, 0, .5);
      transition: transform .2s ease;
    }

    .play:hover {
      transform: scale(1.08);
    }

    .play svg {
      width: 9px;
      height: 9px;
      fill: currentColor;
      margin-left: 1px;
    }

    /* ═════════ 9. COLUMN 2 — FLOATING CONTROLS OVER VIDEO ═════════ */
    .side {
      position: relative;
      min-height: 0;
      padding: clamp(6px, 1vw, 18px);
    }

    .side__top {
      position: absolute;
      top: clamp(6px, 1vw, 18px);
      right: clamp(6px, 1vw, 18px);
      display: flex;
      align-items: center;
      gap: clamp(6px, .6vw, 10px);
    }

    .pill-btn {
      width: clamp(40px, 3.2vw, 52px);
      height: clamp(40px, 3.2vw, 52px);
      border-radius: 50%;
      background: rgba(255, 255, 255, .92);
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
      display: grid;
      place-items: center;
      box-shadow: 0 10px 26px -14px rgba(10, 28, 24, .45);
      transition: transform .2s ease, background-color .2s ease;
    }

    .pill-btn:hover {
      background: #ffffff;
      transform: translateY(-2px);
    }

    .pill-btn svg {
      width: 38%;
      height: 38%;
    }

    .account {
      display: flex;
      align-items: center;
      gap: clamp(6px, .6vw, 10px);
      background: rgba(255, 255, 255, .92);
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
      border-radius: 999px;
      padding: 4px 6px 4px clamp(10px, 0.9vw, 16px);
      box-shadow: 0 10px 26px -14px rgba(10, 28, 24, .45);
      transition: transform .2s ease, background-color .2s ease;
      cursor: pointer;
    }

    .account:hover {
      background: #ffffff;
      transform: translateY(-2px);
    }

    .account__plus {
      width: 14px;
      height: 14px;
      flex: 0 0 auto;
    }

    .account__label {
      font-size: clamp(12px, 0.9vw, 15px);
      color: #7d8785;
      font-weight: 600;
    }

    .account__avatar {
      width: clamp(28px, 2.3vw, 38px);
      height: clamp(28px, 2.3vw, 38px);
      border-radius: 50%;
      overflow: hidden;
      background: linear-gradient(150deg, #dfe7e5, #c3d2cf);
      flex: 0 0 auto;
      display: grid;
      place-items: center;
    }

    .account__avatar img {
      width: 65%;
      height: 65%;
      object-fit: contain;
    }

    .arrow-fab {
      position: absolute;
      right: clamp(10px, 2.2vw, 44px);
      bottom: clamp(10px, 2.2vw, 44px);
      width: clamp(58px, 5vw, 86px);
      height: clamp(58px, 5vw, 86px);
      border-radius: 50%;
      background: #fff;
      display: grid;
      place-items: center;
      box-shadow: 0 18px 40px -20px rgba(10, 28, 24, .5);
      transition: transform .3s ease;
    }

    .arrow-fab:hover {
      transform: rotate(45deg);
    }

    .arrow-fab svg {
      width: 34%;
      height: 34%;
    }

    /* ═════════ 10. ENTRY ANIMATIONS ═════════ */
    @keyframes in-up {
      from { opacity: 0; transform: translate3d(0, 18px, 0); }
      to   { opacity: 1; transform: translate3d(0, 0, 0); }
    }

    @keyframes in-fade {
      from { opacity: 0; }
      to   { opacity: 1; }
    }

    @keyframes in-bg {
      from { opacity: 0; transform: scale(1.06); }
      to   { opacity: 1; transform: scale(1); }
    }

    @keyframes in-draw {
      from { stroke-dashoffset: 420; }
      to   { stroke-dashoffset: 0; }
    }

    .topbar .brand,
    .topbar .menu,
    .topbar .connect,
    .stage .stage__emblem,
    .stage .headline,
    .stage .squiggle,
    .cards .card,
    .side .pill-btn,
    .side .account,
    .side .arrow-fab {
      animation: in-up .78s cubic-bezier(.16, 1, .3, 1) both;
    }

    .topbar .brand       { animation-delay: .06s; }
    .topbar .menu        { animation-delay: .14s; }
    .topbar .connect     { animation-delay: .22s; }
    .stage .stage__emblem{ animation-delay: .18s; }
    .stage .headline     { animation-delay: .28s; }
    .stage .squiggle     { animation-delay: .38s; }
    .cards .card:nth-child(1) { animation-delay: .42s; }
    .cards .card:nth-child(2) { animation-delay: .52s; }
    .cards .card:nth-child(3) { animation-delay: .62s; }
    .side .pill-btn      { animation-delay: .2s; }
    .side .account       { animation-delay: .3s; }
    .side .arrow-fab     { animation-delay: .7s; }

    .stage .squiggle path {
      stroke-dasharray: 420;
      stroke-dashoffset: 420;
      animation: in-draw 1.15s cubic-bezier(.16, 1, .3, 1) both .48s;
    }

    .anim-done {
      animation: none !important;
    }

    /* ═════════ 13. RESPONSIVE ═════════ */
    @media (max-width: 1100px) {
      .hero__inner {
        grid-template-columns: minmax(0, 1fr);
      }
      .side {
        position: absolute;
        inset: clamp(10px, 1.1vw, 20px);
        pointer-events: none;
      }
      .side__top {
        pointer-events: auto;
      }
      .arrow-fab {
        display: none;
      }
      .connect__label {
        display: none;
      }
      .connect {
        padding: 5px;
      }
      .panel {
        padding-top: clamp(60px, 8vw, 84px);
      }
    }

    @media (max-width: 820px) {
      .hero {
        height: auto;
        min-height: 100svh;
      }
      .hero__scrim {
        background: linear-gradient(180deg, rgba(255, 255, 255, .35), rgba(255, 255, 255, .05));
      }
      .hero__inner {
        padding: 12px;
        min-height: calc(100svh - 24px);
      }
      .panel {
        padding: 16px;
        padding-top: 68px;
        border-radius: 26px;
      }
      .menu__label {
        display: none;
      }
      .menu__bars {
        width: 24px;
      }
      .stage {
        padding: 18px 0 16px;
      }
      .headline {
        font-size: clamp(22px, 6.5vw, 32px);
      }
      .squiggle {
        width: min(72%, 220px);
      }
      .cards {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      }
      .card {
        min-height: 0;
      }
      .side__top {
        top: 20px;
        right: 20px;
      }
    }

    @media (max-width: 420px) {
      .headline {
        font-size: clamp(20px, 6.5vw, 28px);
      }
      .account__label {
        display: none;
      }
      .stats {
        gap: 10px;
      }
      .stat__num {
        font-size: clamp(20px, 6.5vw, 28px);
      }
      .teaser__thumb {
        width: 68px;
      }
    }
  </style>
</head>
<body <?php body_class( 'mindora-preorder-body' ); ?>>

  <section class="hero">
    <!-- Background Video from assets/images/a9-horizontal.mp4 -->
    <video class="hero__bg" autoplay muted loop playsinline preload="auto" poster="https://images.higgs.ai/?default=1&output=webp&url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260831_232824_93a0252b-e9f2-432f-9961-555a2e458100.png&w=1920&q=85">
      <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
    </video>
    <div class="hero__scrim"></div>
    <div class="hero__inner">
      <!-- COLUMN 1: the white boxed container -->
      <div class="panel">
        <header class="topbar">
          <div class="nav-left">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">
              <img src="<?php echo esc_url( $logo_horizontal ); ?>" alt="RAKA GADGET">
            </a>
            <button class="menu" id="raka-menu-btn" aria-label="Open menu" aria-expanded="false">
              <div class="menu__bars">
                <span id="burger-top"></span>
                <span id="burger-mid"></span>
                <span id="burger-bot"></span>
              </div>
              <span class="menu__label">menu</span>
            </button>
          </div>
          <div class="connect">
            <span class="connect__label">Follow along</span>
            <div class="socials">
              <a href="#" class="social" aria-label="Facebook">
                <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </a>
              <a href="#" class="social" aria-label="Twitter">
                <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              </a>
              <a href="#" class="social" aria-label="Instagram">
                <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              </a>
            </div>
          </div>
        </header>

        <div class="stage">
          <div class="stage__emblem">
            <img src="<?php echo esc_url( $logo_icon ); ?>" alt="Raka">
          </div>
          <h1 class="headline"><b>Track</b> your gear<br />with <b>Raka Pre-Order</b></h1>
          <svg class="squiggle" viewBox="0 0 320 26" aria-hidden="true">
            <path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
          </svg>
          
          <!-- Pre-Order Form Mount (V2) -->
          <div class="stage__form-mount">
            <?php echo do_shortcode( '[preorder_raka_v2]' ); ?>
          </div>
        </div>

        <div class="cards">
          <!-- CARD 1 -->
          <div class="card">
            <div class="card__top">
              <h2 class="card__title">Evidence-based tracking for your gear</h2>
              <span class="info" aria-hidden="true">i</span>
            </div>
            <div class="card__row">
              <div class="bubble">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M8 5a4 4 0 1 1 4 4 4 4 0 0 0-4 4 4 4 0 1 0 4 4"/>
                </svg>
              </div>
              <p class="card__note">100% Genuine guaranteed delivery.</p>
            </div>
          </div>

          <!-- CARD 2 -->
          <div class="card">
            <div class="card__top">
              <h2 class="card__title">Powered by our community</h2>
            </div>
            <div class="stats">
              <div class="stat">
                <span class="stat__num">99.8%</span>
                <span class="stat__label">On-Time</span>
              </div>
              <div class="stats__div"></div>
              <div class="stat stat--muted">
                <span class="stat__num">24k+</span>
                <span class="stat__label">Orders</span>
              </div>
            </div>
          </div>

          <!-- CARD 3 -->
          <div class="card">
            <div class="card__top">
              <h2 class="card__title">Official Showcase</h2>
            </div>
            <div class="teaser">
              <p class="teaser__text">Preview the<br />flagship gear</p>
              <div class="teaser__thumb">
                <img src="https://images.higgs.ai/?default=1&amp;output=webp&amp;url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260831_233659_e22fa9db-261e-4bf3-82c2-b5626c27e820.png&amp;w=1920&amp;q=85" loading="lazy" alt="Raka preview">
                <button class="play" aria-label="Play video">
                  <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- COLUMN 2: floating controls over the video -->
      <div class="side">
        <div class="side__top">
          <a href="<?php echo esc_url( $shop_url ); ?>" class="pill-btn" aria-label="Explore Shop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
            </svg>
          </a>
          <a href="<?php echo esc_url( $account_url ); ?>" class="account">
            <svg class="account__plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="account__label">Account</span>
            <div class="account__avatar">
              <img src="<?php echo esc_url( $logo_icon ); ?>" alt="Account">
            </div>
          </a>
        </div>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="arrow-fab" aria-label="Explore more">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 17L17 7M8 7h9v9"/>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- ══════════ FULL THEME NAVIGATION OVERLAY (CLONED FROM THEME HEADER) ══════════ -->
  <div id="raka-menu-overlay" class="fixed inset-0 z-[80] transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] pointer-events-none overflow-hidden" style="clip-path: inset(0% 0% 100% 0%);">
			<div id="overlay-backdrop" class="absolute inset-0 bg-white/[0.99] dark:bg-black/[0.99] backdrop-blur-2xl theme-transition"></div>
			
			<div id="raka-menu-container" class="relative z-10 h-full w-full max-w-none flex flex-col justify-between px-6 sm:px-10 md:px-14 lg:px-20 xl:px-28 2xl:px-36 pt-6 sm:pt-8 md:pt-10 pb-8 overflow-y-auto">
				
				<!-- ── Overlay Top Bar (Dedicated Minimalist Close Header) ── -->
				<div id="overlay-top-bar" class="w-full flex items-center justify-between pb-5 mb-4 md:mb-6 border-b border-black/10 dark:border-white/10 shrink-0">
					<!-- Brand -->
					<div id="overlay-brand" class="shrink-0 flex items-center">
						<a id="overlay-brand-link" class="hover:opacity-80 transition-opacity flex items-center gap-2" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<img id="overlay-brand-img-light" class="h-6 sm:h-7 w-auto object-contain block dark:hidden" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
							<img id="overlay-brand-img-dark" class="h-6 sm:h-7 w-auto object-contain hidden dark:block" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal-white.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</a>
					</div>

					<!-- Center Meta Info -->
					<div id="overlay-center-tag" class="hidden sm:flex items-center gap-3 text-[11px] md:text-xs font-mono tracking-[0.25em] uppercase text-neutral-400 dark:text-neutral-500">
						<span id="overlay-tag-txt">DIRECTORY</span>
						<span id="overlay-tag-dot" class="opacity-40">·</span>
						<span id="overlay-tag-vol">MMXXV</span>
					</div>

					<!-- Right Controls: Cart + Theme Toggle & Minimalist X Button -->
					<div id="overlay-controls" class="flex items-center gap-3 sm:gap-4">
						<!-- Cart Button in Overlay -->
						<a
							id="overlay-cart-btn"
							href="<?php echo esc_url( $cart_url ); ?>"
							onclick="if(window.openRakaCart){window.openRakaCart(event);return false;}"
							class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-neutral-800 dark:text-white/90 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-105 focus:outline-none cursor-pointer"
							aria-label="Shopping Cart"
							title="Shopping Cart"
						>
							<i id="overlay-cart-icon" class="ri-shopping-bag-3-line text-lg block"></i>
							<span
								id="overlay-cart-count"
								class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 <?php echo ( $cart_count > 0 ) ? 'scale-100' : 'scale-0'; ?>"
							>
								<?php echo esc_html( (string) $cart_count ); ?>
							</span>
						</a>

						<!-- Theme Toggle in Overlay -->
						<button
							id="overlay-theme-btn"
							type="button"
							onclick="window.toggleRakaTheme(event)"
							class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-neutral-800 dark:text-white/90 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-105 focus:outline-none cursor-pointer"
							aria-label="Toggle dark / light mode"
							title="Toggle theme"
						>
							<i id="overlay-theme-sun" class="ri-sun-line text-lg block text-amber-500"></i>
							<i id="overlay-theme-moon" class="ri-moon-line text-lg hidden text-sky-400"></i>
						</button>

						<!-- Minimalist X Close Button -->
						<button
							id="raka-menu-close-btn"
							type="button"
							class="btn-menu-close group w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full border border-black/15 dark:border-white/20 bg-black/5 dark:bg-white/10 hover:bg-black/15 dark:hover:bg-white/25 text-neutral-900 dark:text-white transition-all duration-300 hover:scale-105 cursor-pointer focus:outline-none shadow-xs"
							aria-label="Close menu"
							title="Close menu"
						>
							<i id="icon-close-menu" class="ri-close-line text-xl sm:text-2xl transition-transform duration-300 group-hover:rotate-90"></i>
						</button>
					</div>
				</div>

				<?php
				$custom_nav_tree = raka_get_nav_menu_tree( 'primary-menu' );
				if ( ! empty( $custom_nav_tree ) ) :
				?>
					<div id="menu-panels-viewport" class="menu-panels-grid flex-1 w-full relative items-stretch min-h-[calc(100vh-220px)] py-2">
						<!-- ─── LEVEL 1: ROOT CUSTOM WORDPRESS MENU ──────────────── -->
						<div id="menu-panel-root" class="w-full h-full min-h-full flex flex-col justify-center opacity-100 transform translate-y-0">
							<div id="root-menu-list" class="flex flex-col gap-1 sm:gap-2">
								<?php
								$item_idx = 1;
								foreach ( $custom_nav_tree as $nav_item ) :
									$has_children = ! empty( $nav_item->children );
									$pad_idx      = str_pad( (string) $item_idx, 2, '0', STR_PAD_LEFT );
									$delay_ms     = ( $item_idx * 60 ) . 'ms';
								?>
									<div id="menu-item-wrap-<?php echo esc_attr( $nav_item->ID ); ?>" class="group block overflow-hidden py-1 sm:py-2">
										<?php if ( $has_children ) : ?>
											<button id="menu-trigger-<?php echo esc_attr( $nav_item->ID ); ?>" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-<?php echo esc_attr( $nav_item->ID ); ?>" data-delay="<?php echo esc_attr( $delay_ms ); ?>">
												<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
													<span id="menu-idx-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal"><?php echo esc_html( $pad_idx ); ?></span>
													<?php echo esc_html( $nav_item->title ); ?>
												</span>
												<span id="menu-meta-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
													<span id="menu-badge-<?php echo esc_attr( $nav_item->ID ); ?>" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800"><?php echo count( $nav_item->children ); ?> ITEMS</span>
													<i id="menu-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
												</span>
											</button>
										<?php else : ?>
											<a id="menu-link-<?php echo esc_attr( $nav_item->ID ); ?>" href="<?php echo esc_url( $nav_item->url ); ?>" class="nav-menu-link w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-delay="<?php echo esc_attr( $delay_ms ); ?>">
												<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
													<span id="menu-idx-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal"><?php echo esc_html( $pad_idx ); ?></span>
													<?php echo esc_html( $nav_item->title ); ?>
												</span>
												<span id="menu-meta-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
													<i id="menu-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-up-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
												</span>
											</a>
										<?php endif; ?>
									</div>
								<?php
									$item_idx++;
								endforeach;
								?>
							</div>
						</div>

						<!-- ─── LEVEL 2: DYNAMIC SLIDING MEGA PANELS ──────────────── -->
						<?php
						$p_idx = 1;
						foreach ( $custom_nav_tree as $nav_item ) :
							if ( empty( $nav_item->children ) ) {
								$p_idx++;
								continue;
							}
							$pad_idx = str_pad( (string) $p_idx, 2, '0', STR_PAD_LEFT );
						?>
							<div id="mega-panel-<?php echo esc_attr( $nav_item->ID ); ?>" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
							<!-- Header / Back Button -->
							<div id="bar-mega-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
								<button id="btn-back-<?php echo esc_attr( $nav_item->ID ); ?>" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
									<i id="icon-back-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
									<span>BACK</span>
								</button>
								<span id="tag-header-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $pad_idx . ' · ' . $nav_item->title ); ?></span>
							</div>

							<!-- Sub-links Grid (Fullscreen Ultra-Minimalist Editorial Layout) -->
							<div id="grid-mega-<?php echo esc_attr( $nav_item->ID ); ?>" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
								<!-- Main Columns: Categories & Brands -->
								<div id="sublinks-col-<?php echo esc_attr( $nav_item->ID ); ?>" class="lg:col-span-8 flex flex-col justify-start space-y-8 lg:space-y-12 py-2">
									<?php
									$c_idx = 1;
									foreach ( $nav_item->children as $child ) :
										$c_pad        = str_pad( (string) $c_idx, 2, '0', STR_PAD_LEFT );
										$has_subitems = ! empty( $child->children );
									?>
										<?php if ( $has_subitems ) : ?>
											<!-- Minimalist Group / Category -->
											<div id="group-block-<?php echo esc_attr( $child->ID ); ?>" class="flex flex-col space-y-4">
												<!-- Minimal Group Header -->
												<div id="group-head-<?php echo esc_attr( $child->ID ); ?>" class="flex items-center gap-2.5">
													<span id="group-pad-<?php echo esc_attr( $child->ID ); ?>" class="text-[11px] font-mono tracking-widest text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $c_pad ); ?>.</span>
													<span id="group-title-<?php echo esc_attr( $child->ID ); ?>" class="text-xs font-semibold uppercase tracking-[0.25em] text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $child->title ); ?></span>
												</div>

												<!-- Minimal Brand Links Typography List -->
												<div id="group-items-grid-<?php echo esc_attr( $child->ID ); ?>" class="flex flex-wrap items-center gap-x-8 lg:gap-x-12 gap-y-4 pt-1">
													<?php
													$sub_idx = 1;
													foreach ( $child->children as $grandchild ) :
													?>
														<a id="grandchild-link-<?php echo esc_attr( $grandchild->ID ); ?>" href="<?php echo esc_url( $grandchild->url ); ?>" class="group/item inline-flex items-center gap-2.5 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
															<span id="grandchild-txt-<?php echo esc_attr( $grandchild->ID ); ?>" class="font-hn"><?php echo esc_html( $grandchild->title ); ?></span>
															<i id="grandchild-icon-<?php echo esc_attr( $grandchild->ID ); ?>" class="ri-arrow-right-up-line text-base md:text-lg opacity-30 group-hover/item:opacity-100 group-hover/item:translate-x-0.5 group-hover/item:-translate-y-0.5 transition-all"></i>
														</a>
													<?php
														$sub_idx++;
													endforeach;
													?>
												</div>
											</div>
										<?php else : ?>
											<!-- Minimal Direct Sublink -->
											<a id="child-link-<?php echo esc_attr( $child->ID ); ?>" href="<?php echo esc_url( $child->url ); ?>" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
												<span id="child-pad-<?php echo esc_attr( $child->ID ); ?>" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal"><?php echo esc_html( $c_pad ); ?>.</span>
												<span id="child-txt-<?php echo esc_attr( $child->ID ); ?>" class="font-hn"><?php echo esc_html( $child->title ); ?></span>
												<i id="child-icon-<?php echo esc_attr( $child->ID ); ?>" class="ri-arrow-right-up-line text-lg md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
											</a>
										<?php endif; ?>
									<?php
										$c_idx++;
									endforeach;
									?>
								</div>

								<!-- Parent Spotlight / Overview Column (Ultra-Minimal, Open Air) -->
								<div id="spotlight-col-<?php echo esc_attr( $nav_item->ID ); ?>" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
									<div id="spotlight-tag-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">OVERVIEW</div>
									<h3 id="spotlight-title-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-extrabold uppercase tracking-tight text-neutral-900 dark:text-white font-hn"><?php echo esc_html( $nav_item->title ); ?></h3>
									<?php if ( ! empty( $nav_item->description ) ) : ?>
										<p id="spotlight-desc-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed max-w-md font-hn"><?php echo esc_html( $nav_item->description ); ?></p>
									<?php else : ?>
										<p id="spotlight-desc-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed max-w-md font-hn">Discover complete collections, verified brand lineups, and newly released hardware.</p>
									<?php endif; ?>
									<div id="spotlight-btn-wrap-<?php echo esc_attr( $nav_item->ID ); ?>" class="pt-4">
										<a id="spotlight-explore-<?php echo esc_attr( $nav_item->ID ); ?>" href="<?php echo esc_url( $nav_item->url ?: '#' ); ?>" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
											<span>EXPLORE ALL <?php echo esc_html( $nav_item->title ); ?></span>
											<i id="spotlight-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php
						$p_idx++;
					endforeach;
					?>
					</div>
				<?php else : ?>
					<div id="menu-panels-viewport-default" class="menu-panels-grid flex-1 w-full relative items-stretch min-h-[calc(100vh-200px)] py-2">
						<!-- ─── LEVEL 1: ROOT DEFAULT CURATED MENU ────────────────── -->
						<div id="menu-panel-root" class="w-full h-full min-h-full flex flex-col justify-center opacity-100 transform translate-y-0">
							<div id="root-menu-list" class="flex flex-col gap-1 sm:gap-2">
							
							<!-- Item 1: Atelier (Has Mega) -->
							<div id="menu-item-atelier-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-atelier" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-atelier" data-delay="60ms">
									<span id="menu-text-atelier" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-atelier" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">01</span>
										Atelier
									</span>
									<span id="menu-meta-atelier" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-atelier" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">4 SECTIONS</span>
										<i id="menu-icon-atelier" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 2: Artists (Has Mega) -->
							<div id="menu-item-artists-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-artists" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-artists" data-delay="120ms">
									<span id="menu-text-artists" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-artists" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">02</span>
										Artists
									</span>
									<span id="menu-meta-artists" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-artists" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">ROSTER</span>
										<i id="menu-icon-artists" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 3: Editions & Shop (Has Mega) -->
							<div id="menu-item-shop-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-shop" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-shop" data-delay="180ms">
									<span id="menu-text-shop" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-shop" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">03</span>
										Editions & Shop
									</span>
									<span id="menu-meta-shop" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-shop" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">CATALOGUE</span>
										<i id="menu-icon-shop" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 4: Gallery & Exhibitions (Has Mega) -->
							<div id="menu-item-gallery-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-gallery" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-gallery" data-delay="240ms">
									<span id="menu-text-gallery" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-gallery" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">04</span>
										Gallery
									</span>
									<span id="menu-meta-gallery" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-gallery" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">SHOWCASE</span>
										<i id="menu-icon-gallery" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 5: Concierge & Inquire (Has Mega) -->
							<div id="menu-item-inquire-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-inquire" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-inquire" data-delay="300ms">
									<span id="menu-text-inquire" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-inquire" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">05</span>
										Concierge & Inquire
									</span>
									<span id="menu-meta-inquire" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-inquire" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">CONTACT</span>
										<i id="menu-icon-inquire" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

						</div>
					</div>

					<!-- ─── LEVEL 2: DEFAULT SLIDING MEGA PANELS ──────────────── -->
					
					<!-- PANEL 1: ATELIER -->
					<div id="mega-panel-atelier" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="atelier-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-atelier" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-atelier" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-atelier" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">01 · ATELIER</span>
						</div>

						<!-- Content Grid -->
						<div id="atelier-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<!-- Sub-Links Column -->
							<div id="atelier-sublinks-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<a id="sublink-atelier-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
									<span id="sublink-txt-atelier-1" class="font-hn">Workshop & Philosophy</span>
									<i id="sublink-icon-atelier-1" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
									<span id="sublink-txt-atelier-2" class="font-hn">Bespoke Commissions</span>
									<i id="sublink-icon-atelier-2" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
									<span id="sublink-txt-atelier-3" class="font-hn">Materials & Foundry</span>
									<i id="sublink-icon-atelier-3" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-4" href="#sec_5" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-4" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">04.</span>
									<span id="sublink-txt-atelier-4" class="font-hn">Archive MMXX - MMXXV</span>
									<i id="sublink-icon-atelier-4" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
							</div>

							<!-- Details & Meta Column -->
							<div id="atelier-meta-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-6">
								<div id="atelier-meta-block-1">
									<div id="atelier-lbl-loc" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">FOUNDRY LOCATION</div>
									<div id="atelier-val-loc" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-light font-hn">Brooklyn Navy Yard, New York</div>
								</div>
								<div id="atelier-meta-block-2">
									<div id="atelier-lbl-stat" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">STATUS</div>
									<div id="atelier-val-stat" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-mono">BATCH 04 IN PRODUCTION</div>
								</div>
								<div id="atelier-meta-block-3">
									<div id="atelier-lbl-inq" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">CONCIERGE</div>
									<div id="atelier-val-inq" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-light font-hn">atelier@obsidian.studio</div>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 2: ARTISTS -->
					<div id="mega-panel-artists" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="artists-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-artists" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-artists" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-artists" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">02 · ARTISTS</span>
						</div>

						<!-- Content Grid -->
						<div id="artists-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<div id="artists-list-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<a id="artist-item-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
									<span id="artist-name-1" class="font-hn">Evelyn Vance</span>
									<span id="artist-role-1" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Lead Sculptor</span>
								</a>
								<a id="artist-item-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
									<span id="artist-name-2" class="font-hn">Kaito Kurogane</span>
									<span id="artist-role-2" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Master Metallurgist</span>
								</a>
								<a id="artist-item-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
									<span id="artist-name-3" class="font-hn">Seraphina Novak</span>
									<span id="artist-role-3" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Concept Director</span>
								</a>
							</div>

							<div id="artists-feature-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
								<div id="artists-prog-tag" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">RESIDENCY</div>
								<h3 id="artists-prog-title" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold uppercase tracking-tight text-neutral-900 dark:text-white font-hn">MMXXVI FELLOWSHIP</h3>
								<p id="artists-prog-desc" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Applications open for sculptors, miniaturists, and character artists seeking studio residency.</p>
								<div id="artists-prog-btn-wrap" class="pt-4">
									<a id="artists-prog-link" href="#sec_5" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
										<span>SUBMIT PORTFOLIO</span>
										<i id="artists-prog-icon" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 3: EDITIONS & SHOP -->
					<div id="mega-panel-shop" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="shop-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-shop" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-shop" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-shop" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">03 · EDITIONS & SHOP</span>
						</div>

						<!-- Content Grid -->
						<div id="shop-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<div id="shop-cats-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<?php
								$wc_cats = raka_get_wc_categories();
								if ( ! empty( $wc_cats ) ) :
									$cat_idx = 1;
									foreach ( array_slice( $wc_cats, 0, 5, true ) as $cid => $cname ) :
										$cterm = get_term( $cid, 'product_cat' );
										$clink = $cterm && ! is_wp_error( $cterm ) ? get_term_link( $cterm ) : '#';
										$c_pad = str_pad( (string) $cat_idx, 2, '0', STR_PAD_LEFT );
								?>
									<a id="wc-cat-link-<?php echo esc_attr( $cid ); ?>" href="<?php echo esc_url( $clink ); ?>" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="wc-cat-pad-<?php echo esc_attr( $cid ); ?>" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal"><?php echo esc_html( $c_pad ); ?>.</span>
										<span id="wc-cat-txt-<?php echo esc_attr( $cid ); ?>" class="font-hn"><?php echo esc_html( $cname ); ?></span>
										<i id="wc-cat-icon-<?php echo esc_attr( $cid ); ?>" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
								<?php
									$cat_idx++;
									endforeach;
								else :
								?>
									<a id="static-cat-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
										<span id="static-cat-txt-1" class="font-hn">Pre-Order Editions</span>
										<i id="static-cat-icon-1" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
									<a id="static-cat-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
										<span id="static-cat-txt-2" class="font-hn">Shadow Box Sculptures</span>
										<i id="static-cat-icon-2" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
									<a id="static-cat-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
										<span id="static-cat-txt-3" class="font-hn">1/6 Scale Collectibles</span>
										<i id="static-cat-icon-3" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
								<?php endif; ?>
							</div>

							<div id="shop-feature-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
								<div id="shop-feat-tag" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">CATALOGUE</div>
								<h3 id="shop-feat-title" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold uppercase tracking-tight text-neutral-900 dark:text-white font-hn">FULL STORE</h3>
								<p id="shop-feat-desc" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Browse all in-stock collectibles, active pre-orders, and archival editions.</p>
								<div id="shop-feat-btn-wrap" class="pt-4">
									<a id="shop-feat-link" href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink( 'shop' ) : home_url('/shop') ); ?>" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
										<span>VIEW COMPLETE STORE</span>
										<i id="shop-feat-icon" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 4: GALLERY -->
					<div id="mega-panel-gallery" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="gallery-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-gallery" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-gallery" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-gallery" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">04 · GALLERY</span>
						</div>

						<!-- Content Grid -->
						<div id="gallery-grid" class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 xl:gap-16 flex-1 w-full items-start py-2">
							<div id="gallery-exhibit-1" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-1" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">CURRENT</div>
								<h4 id="gallery-title-1" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">Forms of the Unseen</h4>
								<p id="gallery-desc-1" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Oct 12 – Dec 30, MMXXV<br>Obsidian Main Hall, Brooklyn</p>
							</div>

							<div id="gallery-exhibit-2" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-2" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">UPCOMING</div>
								<h4 id="gallery-title-2" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">Tokyo Figuration</h4>
								<p id="gallery-desc-2" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Feb 15 – Feb 22, MMXXVI<br>Mori Arts Center Gallery, Roppongi</p>
							</div>

							<div id="gallery-exhibit-3" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-3" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">SPATIAL</div>
								<h4 id="gallery-title-3" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">3D Viewing Chamber</h4>
								<p id="gallery-desc-3" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Interactive WebGL spatial gallery viewing for collectors worldwide.</p>
							</div>
						</div>
					</div>

					<!-- PANEL 5: INQUIRE & CONCIERGE -->
					<div id="mega-panel-inquire" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="inquire-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-inquire" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-inquire" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-inquire" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">05 · CONCIERGE</span>
						</div>

						<!-- Content Grid -->
						<div id="inquire-grid" class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 xl:gap-16 flex-1 w-full items-start py-2">
							<div id="inquire-box-1" class="flex flex-col space-y-2">
								<div id="inquire-tag-1" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">ACQUISITIONS</div>
								<h4 id="inquire-title-1" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Collector Concierge</h4>
								<p id="inquire-desc-1" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">Dedicated advisory for private collectors & insured delivery.</p>
								<div id="inquire-val-1" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">collector@obsidian.studio</div>
							</div>

							<div id="inquire-box-2" class="flex flex-col space-y-2">
								<div id="inquire-tag-2" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">VISITS</div>
								<h4 id="inquire-title-2" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Private Appointment</h4>
								<p id="inquire-desc-2" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">Foundry & salon visits by reservation only.</p>
								<div id="inquire-val-2" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">+1 (718) 555-0199</div>
							</div>

							<div id="inquire-box-3" class="flex flex-col space-y-2">
								<div id="inquire-tag-3" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">EDITORIAL</div>
								<h4 id="inquire-title-3" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Media Inquiries</h4>
								<p id="inquire-desc-3" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">High-res asset packs & exhibition releases.</p>
								<div id="inquire-val-3" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">press@obsidian.studio</div>
							</div>
						</div>
					</div>
					</div>
				<?php endif; ?>

				<!-- ─── BOTTOM INFO BAR (Fixed at footer of overlay) ────────── -->
				<div id="nav-bottom-info" class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 pt-6 border-t border-neutral-200/80 dark:border-neutral-800/80 transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] opacity-0 translate-y-[20px]">
					<div id="footer-info-loc">
						<div id="lbl-info-loc" class="text-[10px] tracking-[0.25em] uppercase text-black/40 dark:text-white/30 mb-1 font-hn">Location</div>
						<div id="val-info-loc" class="text-xs text-black/60 dark:text-white/50 font-light leading-relaxed font-hn">Brooklyn, New York</div>
					</div>
					<div id="footer-info-inq">
						<div id="lbl-info-inq" class="text-[10px] tracking-[0.25em] uppercase text-black/40 dark:text-white/30 mb-1 font-hn">Inquiries</div>
						<div id="val-info-inq" class="text-xs text-black/60 dark:text-white/50 font-light font-hn">hello@obsidian.studio</div>
					</div>
					<div id="footer-info-copy" class="hidden sm:block text-[10px] tracking-[0.2em] uppercase text-black/30 dark:text-white/20 font-hn">
						© OBSIDIAN MMXXV
					</div>
				</div>

			</div>
		</div>

  <script>
    /* ═════════ IIFE #1: Entry Animation Cleanup ═════════ */
    (function initAnimationCleanup() {
      const animSelectors = [
        '.topbar .brand', '.topbar .menu', '.topbar .connect',
        '.stage .stage__emblem', '.stage .headline', '.stage .squiggle',
        '.cards .card', '.side .pill-btn',
        '.side .account', '.side .arrow-fab'
      ];
      const elements = document.querySelectorAll(animSelectors.join(', '));
      elements.forEach(el => {
        el.addEventListener('animationend', function handler(e) {
          if (e.target === el) {
            el.classList.add('anim-done');
            el.removeEventListener('animationend', handler);
          }
        });
      });
    })();

    /* ═════════ IIFE #2: Fullscreen Theme Navigation Controller ═════════ */
    // ─── 2. Fullscreen Menu & Mega Menu Logic ─────────────────────────────────
		(function initMenu() {
			const menuBtn = document.getElementById('raka-menu-btn') || document.getElementById('obsidian-menu-btn');
			const overlay = document.getElementById('raka-menu-overlay') || document.getElementById('obsidian-menu-overlay');
			const burgerTop = document.getElementById('burger-top');
			const burgerMid = document.getElementById('burger-mid');
			const burgerBot = document.getElementById('burger-bot');
			const navBottom = document.getElementById('nav-bottom-info');
			const rootPanel = document.getElementById('menu-panel-root');
			const megaTriggers = document.querySelectorAll('.mega-menu-trigger');
			const megaPanels = document.querySelectorAll('.mega-panel');
			const backButtons = document.querySelectorAll('.btn-mega-back');

			if (!menuBtn || !overlay) return;

			let isOpen = false;
			let currentActiveMegaPanel = null;

			function animateRootLinksIn() {
				const linkTexts = rootPanel.querySelectorAll('.nav-link-text');
				linkTexts.forEach((span, idx) => {
					const trigger = span.closest('.mega-menu-trigger');
					const delay = trigger ? trigger.getAttribute('data-delay') : (idx * 60 + 'ms');
					span.style.transitionDelay = delay;
					span.style.transform = 'translateY(0)';
				});
			}

			function animateRootLinksOut() {
				const linkTexts = rootPanel.querySelectorAll('.nav-link-text');
				linkTexts.forEach((span) => {
					span.style.transitionDelay = '0ms';
					span.style.transform = 'translateY(110%)';
				});
			}

			function openMegaPanel(panelId) {
				const targetPanel = document.getElementById(panelId);
				if (!targetPanel) return;

				currentActiveMegaPanel = targetPanel;

				// 1. Animate root menu out smoothly
				if (rootPanel) {
					rootPanel.style.opacity = '0';
					rootPanel.style.transform = 'translate3d(0, -24px, 0)';
					rootPanel.style.pointerEvents = 'none';
					rootPanel.style.visibility = 'hidden';
				}

				// 2. Hide all other mega panels
				megaPanels.forEach(panel => {
					if (panel !== targetPanel) {
						panel.style.opacity = '0';
						panel.style.transform = 'translate3d(0, 28px, 0)';
						panel.style.pointerEvents = 'none';
						panel.style.visibility = 'hidden';
					}
				});

				// 3. Slide target mega panel in seamlessly
				targetPanel.style.visibility = 'visible';
				targetPanel.style.opacity = '1';
				targetPanel.style.transform = 'translate3d(0, 0, 0)';
				targetPanel.style.pointerEvents = 'auto';
			}

			function backToRootMenu() {
				if (currentActiveMegaPanel) {
					// Slide current mega panel down and out
					currentActiveMegaPanel.style.opacity = '0';
					currentActiveMegaPanel.style.transform = 'translate3d(0, 28px, 0)';
					currentActiveMegaPanel.style.pointerEvents = 'none';
					currentActiveMegaPanel.style.visibility = 'hidden';
					currentActiveMegaPanel = null;
				}

				// Slide root menu back into place
				if (rootPanel) {
					rootPanel.style.visibility = 'visible';
					rootPanel.style.opacity = '1';
					rootPanel.style.transform = 'translate3d(0, 0, 0)';
					rootPanel.style.pointerEvents = 'auto';
					animateRootLinksIn();
				}
			}

			function resetMenuToRootInstant() {
				if (currentActiveMegaPanel) {
					currentActiveMegaPanel.style.opacity = '0';
					currentActiveMegaPanel.style.transform = 'translate3d(0, 28px, 0)';
					currentActiveMegaPanel.style.pointerEvents = 'none';
					currentActiveMegaPanel.style.visibility = 'hidden';
					currentActiveMegaPanel = null;
				}
				if (rootPanel) {
					rootPanel.style.visibility = 'visible';
					rootPanel.style.opacity = '1';
					rootPanel.style.transform = 'translate3d(0, 0, 0)';
					rootPanel.style.pointerEvents = 'auto';
				}
				animateRootLinksOut();
			}

			function toggleMenu(open) {
				isOpen = typeof open === 'boolean' ? open : !isOpen;
				menuBtn.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

				if (isOpen) {
					document.body.style.overflow = 'hidden';
					overlay.style.pointerEvents = 'auto';
					overlay.style.clipPath = 'inset(0% 0% 0% 0%)';

					// Burger icon -> X
					burgerTop.style.transform = 'translateY(7.5px) rotate(45deg)';
					burgerTop.style.width = '100%';
					burgerMid.style.opacity = '0';
					burgerMid.style.transform = 'scaleX(0)';
					burgerBot.style.transform = 'translateY(-7.5px) rotate(-45deg)';
					burgerBot.style.width = '100%';

					// Ensure starting at root
					if (!currentActiveMegaPanel) {
						if (rootPanel) {
							rootPanel.style.visibility = 'visible';
							rootPanel.style.opacity = '1';
							rootPanel.style.transform = 'translate3d(0, 0, 0)';
							rootPanel.style.pointerEvents = 'auto';
						}
						animateRootLinksIn();
					}

					// Bottom info
					if (navBottom) {
						navBottom.style.transitionDelay = '450ms';
						navBottom.style.opacity = '1';
						navBottom.style.transform = 'translateY(0)';
					}
				} else {
					document.body.style.overflow = '';
					overlay.style.pointerEvents = 'none';
					overlay.style.clipPath = 'inset(0% 0% 100% 0%)';

					// Burger icon reset
					burgerTop.style.transform = '';
					burgerTop.style.width = '100%';
					burgerMid.style.opacity = '1';
					burgerMid.style.transform = '';
					burgerBot.style.transform = '';
					burgerBot.style.width = '80%';

					// Reset links
					animateRootLinksOut();

					// Bottom info reset
					if (navBottom) {
						navBottom.style.transitionDelay = '0ms';
						navBottom.style.opacity = '0';
						navBottom.style.transform = 'translateY(20px)';
					}

					// Reset state after animation completes
					setTimeout(() => {
						if (!isOpen) {
							resetMenuToRootInstant();
						}
					}, 700);
				}
			}

			// Hamburger button click
			menuBtn.addEventListener('click', () => toggleMenu());

			// Dedicated Cancel / Close buttons inside overlay
			const closeButtons = overlay.querySelectorAll('.btn-menu-close, #raka-menu-close-btn, #obsidian-menu-close-btn');
			closeButtons.forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					toggleMenu(false);
				});
			});

			// Trigger click: Slide to Mega Menu Panel
			megaTriggers.forEach(trigger => {
				trigger.addEventListener('click', (e) => {
					e.preventDefault();
					const target = trigger.getAttribute('data-mega-target');
					if (target) {
						openMegaPanel(target);
					}
				});
			});

			// Back buttons click: Slide back to Main Menu
			backButtons.forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					backToRootMenu();
				});
			});

			// Sublinks / Leaf anchors click: close overlay
			const leafLinks = overlay.querySelectorAll('a[href^="#"], a:not([data-mega-target])');
			leafLinks.forEach(link => {
				link.addEventListener('click', () => {
					toggleMenu(false);
				});
			});

			// Keyboard ESC to close or go back
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && isOpen) {
					if (currentActiveMegaPanel) {
						backToRootMenu();
					} else {
						toggleMenu(false);
					}
				}
			});
		})();

		// ─── 3. Global Product Card AJAX Add to Cart Handler ───────────────────────
		window.rakaCardAddToCart = function(productId, btn) {
			if (!btn || btn.disabled) return;
			btn.disabled = true;

			const txtSpan = btn.querySelector('span');
			const icoEl   = btn.querySelector('i');
			const origTxt = txtSpan ? txtSpan.textContent.trim() : 'ADD TO CART';
			const origIco = icoEl ? icoEl.className : 'ri-shopping-bag-3-line text-xs flex-shrink-0';
			const isPre = origTxt.toUpperCase().includes('PRE-ORDER');

			if (txtSpan) txtSpan.textContent = isPre ? 'ORDERING...' : 'ADDING...';
			if (icoEl) icoEl.className = 'ri-loader-4-line animate-spin text-xs flex-shrink-0';

			const formData = new FormData();
			formData.append('product_id', productId);
			formData.append('quantity', 1);

			fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_add_to_cart' ) ); ?>', {
				method: 'POST',
				body: formData
			})
			.then(res => res.json())
			.then(data => {
				if (txtSpan) txtSpan.textContent = isPre ? 'PRE-ORDER ADDED!' : 'ADDED!';
				if (icoEl) icoEl.className = 'ri-check-line text-xs text-green-400 flex-shrink-0';

				if (data && data.fragments && typeof window.updateCartDrawerFragments === 'function') {
					window.updateCartDrawerFragments(data.fragments);
				}

				// Remove any WooCommerce default injected .added_to_cart links
				setTimeout(() => {
					const parent = btn.parentElement;
					if (parent) {
						parent.querySelectorAll('.added_to_cart').forEach(el => el.remove());
					}
				}, 10);

				if (window.jQuery) {
					window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash]);
				}

				// Automatically open Slide-Over Cart Drawer upon adding
				if (typeof window.openRakaCart === 'function') {
					window.openRakaCart();
				}

				setTimeout(() => {
					if (txtSpan) txtSpan.textContent = origTxt;
					if (icoEl) icoEl.className = origIco;
					btn.disabled = false;
				}, 1800);
			})
			.catch(() => {
				window.location.href = '<?php echo esc_url( wc_get_cart_url() ); ?>?add-to-cart=' + productId;
			});
		};

		// ─── 4. Dynamic Sticky/Fixed Header on Scroll Controller ──────────────────
		(function() {
			const header = document.getElementById('raka-site-header') || document.getElementById('obsidian-site-header');
			if (!header) return;

			function handleScroll() {
				const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
				if (scrollY > 20) {
					header.classList.add('header-scrolled');
				} else {
					header.classList.remove('header-scrolled');
				}
			}

			window.addEventListener('scroll', handleScroll, { passive: true });
			handleScroll();
		})();

		// ─── 5. Luxury Slide-Over Cart Drawer Controller ──────────────────────────
		(function() {
			const drawer   = document.getElementById('raka-cart-drawer');
			const backdrop = document.getElementById('raka-cart-backdrop');
			const panel    = document.getElementById('raka-cart-panel');
			let isCartOpen = false;

			window.openRakaCart = function(e) {
				if (e && typeof e.preventDefault === 'function') e.preventDefault();
				if (!drawer || !backdrop || !panel) return false;
				isCartOpen = true;
				drawer.classList.remove('pointer-events-none');
				drawer.classList.add('pointer-events-auto');
				drawer.setAttribute('aria-hidden', 'false');

				// Backdrop fade in
				backdrop.classList.remove('opacity-0');
				backdrop.classList.add('opacity-100');

				// Panel slide in
				panel.classList.remove('translate-x-full');
				panel.classList.add('translate-x-0');

				document.body.style.overflow = 'hidden';
			};

			window.closeRakaCart = function() {
				if (!drawer || !backdrop || !panel) return;
				isCartOpen = false;

				// Panel slide out
				panel.classList.remove('translate-x-0');
				panel.classList.add('translate-x-full');

				// Backdrop fade out
				backdrop.classList.remove('opacity-100');
				backdrop.classList.add('opacity-0');

				drawer.classList.remove('pointer-events-auto');
				drawer.classList.add('pointer-events-none');
				drawer.setAttribute('aria-hidden', 'true');

				// If fullscreen menu overlay is not open, unlock body scroll
				const menuOverlay = document.getElementById('raka-menu-overlay') || document.getElementById('fullscreen-menu-overlay');
				const isMenuOpen = menuOverlay && menuOverlay.style.pointerEvents === 'auto';
				if (!isMenuOpen) {
					document.body.style.overflow = '';
				}
			};

			// Update Cart Item Quantity with Live AJAX (+ / -)
			window.rakaUpdateCartQty = function(cartItemKey, delta, btn) {
				if (!cartItemKey) return;
				const qtyEl = document.getElementById('cart-qty-val-' + cartItemKey);
				const itemRow = document.getElementById('cart-item-' + cartItemKey);
				let currentQty = qtyEl ? parseInt(qtyEl.textContent.trim(), 10) : 1;
				if (isNaN(currentQty)) currentQty = 1;

				const newQty = currentQty + delta;
				if (newQty < 1) {
					// If reduced below 1, remove item
					window.rakaRemoveCartItem(cartItemKey, btn);
					return;
				}

				if (qtyEl) qtyEl.textContent = newQty;
				if (itemRow) itemRow.style.opacity = '0.5';

				const formData = new FormData();
				formData.append('cart_item_key', cartItemKey);
				formData.append('quantity', newQty);

				fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_update_cart_item_qty' ) ); ?>', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (data && data.fragments) {
						updateCartDrawerFragments(data.fragments);
					}
					if (data && typeof data.cart_count === 'number') {
						updateBadges(data.cart_count);
					}
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
					// If on cart page, trigger on-page cart refresh if present
					if (window.location.pathname.includes('/cart')) {
						const updateCartBtn = document.querySelector('button[name="update_cart"]');
						if (updateCartBtn) {
							updateCartBtn.disabled = false;
							updateCartBtn.click();
						}
					}
				})
				.catch(() => {
					if (itemRow) itemRow.style.opacity = '1';
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
				});
			};

			// Remove Cart Item with Live AJAX
			window.rakaRemoveCartItem = function(cartItemKey, btn) {
				if (!cartItemKey) return;
				const itemRow = document.getElementById('cart-item-' + cartItemKey);
				if (itemRow) {
					itemRow.style.opacity = '0.4';
					itemRow.style.pointerEvents = 'none';
				}
				if (btn) {
					btn.innerHTML = '<i class="ri-loader-4-line animate-spin text-sm"></i>';
				}

				const formData = new FormData();
				formData.append('cart_item_key', cartItemKey);

				fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_remove_cart_item' ) ); ?>', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (data && data.fragments) {
						updateCartDrawerFragments(data.fragments);
					}
					if (data && typeof data.cart_count === 'number') {
						updateBadges(data.cart_count);
					}
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
					// If on cart page, trigger on-page cart refresh if present
					if (window.location.pathname.includes('/cart')) {
						const updateCartBtn = document.querySelector('button[name="update_cart"]');
						if (updateCartBtn) {
							updateCartBtn.disabled = false;
							updateCartBtn.click();
						}
					}
				})
				.catch(() => {
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
				});
			};

			window.updateBadges = function(count) {
				if (typeof count !== 'number') return;
				const badges = document.querySelectorAll('.raka-cart-count-badge, #raka-cart-count, #overlay-cart-count');
				badges.forEach(b => {
					b.textContent = count;
					if (count > 0) {
						b.classList.remove('scale-0');
						b.classList.add('scale-100');
					} else {
						b.classList.remove('scale-100');
						b.classList.add('scale-0');
					}
					// Pulse animation
					b.classList.remove('scale-125');
					void b.offsetWidth;
					b.classList.add('scale-125');
					setTimeout(() => b.classList.remove('scale-125'), 300);
				});

				const drawerCount = document.getElementById('raka-cart-drawer-count');
				if (drawerCount) {
					drawerCount.textContent = count;
				}
			};

			window.updateCartDrawerFragments = function(fragments) {
				if (!fragments) return;

				// Update Cart Drawer Content
				if (fragments['#raka-cart-drawer-content']) {
					const drawerBody = document.getElementById('raka-cart-drawer-body');
					if (drawerBody) {
						drawerBody.innerHTML = fragments['#raka-cart-drawer-content'];
					}
				}

				// Update Drawer Count
				if (fragments['#raka-cart-drawer-count']) {
					const temp = document.createElement('div');
					temp.innerHTML = fragments['#raka-cart-drawer-count'];
					const countEl = document.getElementById('raka-cart-drawer-count');
					if (countEl && temp.firstChild) {
						countEl.textContent = temp.firstChild.textContent.trim();
					}
				}

				// Update Badges
				if (fragments['#raka-cart-count']) {
					const temp = document.createElement('div');
					temp.innerHTML = fragments['#raka-cart-count'];
					const newSpan = temp.querySelector('span') || temp.firstChild;
					if (newSpan) {
						const cnt = parseInt(newSpan.textContent.trim(), 10);
						if (!isNaN(cnt)) window.updateBadges(cnt);
					}
				}
			};

			const updateCartDrawerFragments = window.updateCartDrawerFragments;
			const updateBadges = window.updateBadges;

			// Global Fetch Interceptor for WooCommerce Store API & AJAX requests
			const originalFetch = window.fetch;
			window.fetch = async function(...args) {
				const res = await originalFetch.apply(this, args);
				try {
					const url = (typeof args[0] === 'string') ? args[0] : (args[0] && args[0].url ? args[0].url : '');
					if (url.includes('/wc/store/') || url.includes('wc-ajax=') || url.includes('raka_')) {
						const clone = res.clone();
						clone.json().then(data => {
							if (data) {
								let cnt = null;
								if (typeof data.items_count === 'number') {
									cnt = data.items_count;
								} else if (typeof data.cart_count === 'number') {
									cnt = data.cart_count;
								} else if (data.items && Array.isArray(data.items)) {
									cnt = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
								}
								if (cnt !== null) {
									updateBadges(cnt);
								}
								if (data.fragments) {
									updateCartDrawerFragments(data.fragments);
								} else if (url.includes('/wc/store/')) {
									fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
										.then(r => r.json())
										.then(d => {
											if (d && d.fragments) updateCartDrawerFragments(d.fragments);
										})
										.catch(() => {});
								}
							}
						}).catch(() => {});
					}
				} catch (err) {}
				return res;
			};

			// Intercept Header & Overlay Cart buttons
			const cartBtns = document.querySelectorAll('#raka-cart-btn, #overlay-cart-btn, a[href*="cart"]');
			cartBtns.forEach(btn => {
				// Only intercept if not checkout link
				if (btn.id === 'raka-cart-btn' || btn.id === 'overlay-cart-btn') {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						window.openRakaCart();
					});
				}
			});

			// Escape key to close cart
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && isCartOpen) {
					window.closeRakaCart();
				}
			});

			// Live Click/Change listeners for on-page Cart quantity buttons (both Classic Cart and WooCommerce Blocks)
			function syncFromPageCart() {
				const qtyInputs = document.querySelectorAll('.wc-block-components-quantity-selector__input, .woocommerce-cart-form input.qty');
				if (qtyInputs.length > 0) {
					let total = 0;
					qtyInputs.forEach(inp => {
						const v = parseInt(inp.value, 10);
						if (!isNaN(v)) total += v;
					});
					if (total >= 0) updateBadges(total);
				}
			}

			let syncDebounce = null;
			document.addEventListener('click', function(e) {
				if (e.target && e.target.closest('.wc-block-components-quantity-selector__button, .wc-block-cart-items-table__remove-item, .wc-block-cart__submit-button, button[name="update_cart"]')) {
					clearTimeout(syncDebounce);
					syncDebounce = setTimeout(() => {
						syncFromPageCart();
						fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
							.then(r => r.json())
							.then(d => {
								if (d && d.fragments) updateCartDrawerFragments(d.fragments);
							})
							.catch(() => {});
					}, 450);
				}
			});

			document.addEventListener('change', function(e) {
				if (e.target && e.target.matches('.woocommerce-cart-form input.qty, input[name*="cart["], .wc-block-components-quantity-selector__input')) {
					clearTimeout(syncDebounce);
					syncDebounce = setTimeout(() => {
						syncFromPageCart();
						const updateBtn = document.querySelector('button[name="update_cart"]');
						if (updateBtn) {
							updateBtn.disabled = false;
							updateBtn.click();
						}
					}, 400);
				}
			});

			// MutationObserver for WooCommerce React Cart Block
			const cartBlockEl = document.querySelector('.wp-block-woocommerce-cart, .wc-block-cart');
			if (cartBlockEl && window.MutationObserver) {
				const observer = new MutationObserver(() => {
					clearTimeout(syncDebounce);
					syncDebounce = setTimeout(syncFromPageCart, 300);
				});
				observer.observe(cartBlockEl, { childList: true, subtree: true, attributes: true, characterData: true });
			}

			// WooCommerce jQuery events
			if (window.jQuery) {
				window.jQuery(document.body).on('added_to_cart', function(event, fragments) {
					if (fragments) updateCartDrawerFragments(fragments);
				});

				window.jQuery(document.body).on('removed_from_cart', function(event, fragments) {
					if (fragments) updateCartDrawerFragments(fragments);
				});

				window.jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
					// Auto sync if needed
				});

				window.jQuery(document.body).on('updated_cart_totals', function() {
					fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
						.then(r => r.json())
						.then(d => {
							if (d && d.fragments) updateCartDrawerFragments(d.fragments);
						})
						.catch(() => {});
				});
			}

			// WooCommerce Block Cart store subscription (if React Gutenberg Cart is active)
			if (window.wp && wp.data && wp.data.subscribe) {
				let prevCartCount = null;
				wp.data.subscribe(function() {
					try {
						const store = wp.data.select('wc/store/cart');
						if (store && typeof store.getCartData === 'function') {
							const cartData = store.getCartData();
							if (cartData && typeof cartData.itemsCount === 'number') {
								if (prevCartCount !== null && prevCartCount !== cartData.itemsCount) {
									updateBadges(cartData.itemsCount);
									fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
										.then(r => r.json())
										.then(d => {
											if (d && d.fragments) updateCartDrawerFragments(d.fragments);
										})
										.catch(() => {});
								}
								prevCartCount = cartData.itemsCount;
							}
						}
					} catch(e) {}
				});
			}
		})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>
  </script>

  <?php wp_footer(); ?>
</body>
</html>
