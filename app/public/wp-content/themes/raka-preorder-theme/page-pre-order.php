<?php
/**
 * Template Name: Pre-Order Standalone Hero
 * Standalone full-viewport Hero layout based on Mindora specification.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$video_url = get_template_directory_uri() . '/assets/images/a9-horizontal.mp4';
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
      --pad: clamp(18px, 2.2vw, 34px);
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
      font-size: clamp(19px, 1.55vw, 26px);
      font-weight: 800;
      letter-spacing: -.03em;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: clamp(18px, 2.6vw, 44px);
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 4px;
      margin: -8px -4px;
      position: relative;
      z-index: 130;
    }

    .menu__bars {
      display: flex;
      flex-direction: column;
      gap: 5px;
      width: 34px;
    }

    .menu__bars span {
      display: block;
      height: 2px;
      background: var(--ink);
      border-radius: 2px;
      transform-origin: center;
      transition: transform .5s cubic-bezier(.16, 1, .3, 1), width .4s cubic-bezier(.16, 1, .3, 1);
    }

    .menu:hover .menu__bars span:nth-child(2) {
      width: 70%;
    }

    .menu.is-active .menu__bars span {
      width: 100%;
    }

    .menu.is-active .menu__bars span:nth-child(1) {
      transform: translateY(3.5px) rotate(45deg);
    }

    .menu.is-active .menu__bars span:nth-child(2) {
      transform: translateY(-3.5px) rotate(-45deg);
    }

    .menu__label {
      font-size: clamp(14px, 1.05vw, 17px);
      font-weight: 500;
    }

    .connect {
      display: flex;
      align-items: center;
      gap: clamp(8px, .7vw, 12px);
      border: 1px solid var(--line);
      border-radius: 999px;
      padding: 6px 6px 6px clamp(14px, 1.3vw, 22px);
    }

    .connect__label {
      font-size: clamp(10px, .78vw, 12.5px);
      font-weight: 600;
      letter-spacing: .09em;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .socials {
      display: flex;
      gap: 6px;
    }

    .social {
      width: clamp(28px, 2.15vw, 36px);
      height: clamp(28px, 2.15vw, 36px);
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

    /* ═════════ 7. STAGE (Pre-Order Form Area) ═════════ */
    .stage {
      flex: 1 1 auto;
      min-height: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      padding: clamp(12px, 1.8vh, 26px) 0;
      width: 100%;
    }

    .avatar {
      width: clamp(60px, 5.5vw, 92px);
      height: clamp(60px, 5.5vw, 92px);
      border-radius: 50%;
      overflow: hidden;
      background: linear-gradient(150deg, #dfe7e5, #c3d2cf);
      flex: 0 0 auto;
      box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.15);
    }

    .avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .headline {
      margin: clamp(10px, 1.6vh, 20px) 0 0;
      font-size: clamp(26px, 3.2vw, 48px);
      line-height: 1.06;
      letter-spacing: -.045em;
      font-weight: 400;
    }

    .headline b {
      font-weight: 800;
    }

    .squiggle {
      width: clamp(160px, 18vw, 280px);
      margin: clamp(6px, 1vh, 12px) auto clamp(12px, 1.8vh, 20px);
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
      gap: clamp(10px, .95vw, 18px);
      margin-top: clamp(10px, 1.4vh, 18px);
    }

    .card {
      background: var(--box);
      border-radius: var(--radius-box);
      padding: clamp(12px, 1.1vw, 20px);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: clamp(10px, 1.4vh, 18px);
      min-height: clamp(130px, 16vh, 180px);
      border: 1px solid var(--line);
    }

    .card__top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
    }

    .card__title {
      font-size: clamp(12.5px, 0.95vw, 15px);
      font-weight: 600;
      line-height: 1.3;
      letter-spacing: -.015em;
      margin: 0;
      flex: 1 1 auto;
      min-width: 0;
      text-wrap: pretty;
    }

    .info {
      width: clamp(20px, 1.5vw, 24px);
      height: clamp(20px, 1.5vw, 24px);
      border-radius: 50%;
      background: var(--ink);
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 11px;
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
      width: clamp(30px, 2.5vw, 40px);
      height: clamp(30px, 2.5vw, 40px);
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
      font-size: clamp(11px, .8vw, 13px);
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
      font-size: clamp(24px, 2.6vw, 40px);
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
      font-size: clamp(10px, .75vw, 12px);
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
      font-size: clamp(12px, .9vw, 14.5px);
      line-height: 1.3;
      color: var(--ink);
      margin: 0;
    }

    .teaser__thumb {
      position: relative;
      width: clamp(64px, 5.5vw, 90px);
      aspect-ratio: 1/1.06;
      border-radius: 12px;
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
      width: 28px;
      height: 28px;
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
      width: 10px;
      height: 10px;
      fill: currentColor;
      margin-left: 2px;
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
      width: clamp(42px, 3.4vw, 56px);
      height: clamp(42px, 3.4vw, 56px);
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
      gap: clamp(8px, .7vw, 14px);
      background: rgba(255, 255, 255, .92);
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
      border-radius: 999px;
      padding: 5px 5px 5px clamp(12px, 1vw, 18px);
      box-shadow: 0 10px 26px -14px rgba(10, 28, 24, .45);
      transition: transform .2s ease, background-color .2s ease;
      cursor: pointer;
    }

    .account:hover {
      background: #ffffff;
      transform: translateY(-2px);
    }

    .account__plus {
      width: 16px;
      height: 16px;
      flex: 0 0 auto;
    }

    .account__label {
      font-size: clamp(13px, 1vw, 16px);
      color: #7d8785;
      font-weight: 500;
    }

    .account__avatar {
      width: clamp(34px, 2.7vw, 44px);
      height: clamp(34px, 2.7vw, 44px);
      border-radius: 50%;
      overflow: hidden;
      background: linear-gradient(150deg, #dfe7e5, #c3d2cf);
      flex: 0 0 auto;
    }

    .account__avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .arrow-fab {
      position: absolute;
      right: clamp(10px, 2.2vw, 44px);
      bottom: clamp(10px, 2.2vw, 44px);
      width: clamp(64px, 5.6vw, 96px);
      height: clamp(64px, 5.6vw, 96px);
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

    /* ═════════ 10. PAGE-LOAD ENTRY ANIMATION ═════════ */
    @keyframes in-up {
      from {
        opacity: 0;
        transform: translate3d(0, 18px, 0);
      }
      to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
      }
    }

    @keyframes in-fade {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes in-bg {
      from {
        opacity: 0;
        transform: scale(1.06);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    @keyframes in-draw {
      from { stroke-dashoffset: 420; }
      to { stroke-dashoffset: 0; }
    }

    .topbar .brand,
    .topbar .menu,
    .topbar .connect,
    .stage .avatar,
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
    .stage .avatar       { animation-delay: .18s; }
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

    /* ═════════ 11. NAV OVERLAY ═════════ */
    .navmenu {
      position: fixed;
      inset: 0;
      z-index: 120;
      visibility: hidden;
      transition: visibility 0s linear .5s;
    }

    .navmenu.is-open {
      visibility: visible;
      transition-delay: 0s;
    }

    .navmenu__scrim {
      position: absolute;
      inset: 0;
      background: rgba(10, 22, 19, .34);
      -webkit-backdrop-filter: blur(9px) saturate(1.1);
      backdrop-filter: blur(9px) saturate(1.1);
      opacity: 0;
      transition: opacity .5s cubic-bezier(.22, 1, .36, 1);
    }

    .navmenu.is-open .navmenu__scrim {
      opacity: 1;
    }

    .navmenu__sheet {
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      width: min(430px, 88vw);
      background: var(--panel);
      border-radius: 0 clamp(20px, 2.4vw, 34px) clamp(20px, 2.4vw, 34px) 0;
      padding: clamp(20px, 3vw, 38px) clamp(20px, 3vw, 38px) max(clamp(18px, 2.4vw, 32px), env(safe-area-inset-bottom));
      display: flex;
      flex-direction: column;
      transform: translate3d(-102%, 0, 0);
      transition: transform .62s cubic-bezier(.16, 1, .3, 1);
      box-shadow: 44px 0 90px -44px rgba(8, 24, 20, .5);
      overflow-y: auto;
      overscroll-behavior: contain;
      will-change: transform;
    }

    .navmenu.is-open .navmenu__sheet {
      transform: translate3d(0, 0, 0);
    }

    .navmenu:not(.is-open) .navmenu__sheet {
      transition-duration: .44s;
    }

    .navmenu__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex: 0 0 auto;
    }

    .navmenu__brand {
      font-size: clamp(19px, 1.55vw, 26px);
      font-weight: 700;
      letter-spacing: -.03em;
    }

    .navmenu__close {
      width: 44px;
      height: 44px;
      border: 1px solid var(--line);
      border-radius: 50%;
      display: grid;
      place-items: center;
      flex: 0 0 auto;
      transition: transform .4s cubic-bezier(.16, 1, .3, 1), background .3s ease;
    }

    .navmenu__close:hover {
      background: var(--box);
      transform: rotate(90deg);
    }

    .navmenu__close svg {
      width: 15px;
      height: 15px;
    }

    .navmenu__list {
      list-style: none;
      margin: clamp(22px, 4vh, 44px) 0 0;
      padding: 0;
      flex: 1 1 auto;
    }

    .navmenu__list li {
      border-bottom: 1px solid var(--line);
      opacity: 0;
      transform: translateY(22px);
      transition: opacity .45s ease, transform .6s cubic-bezier(.16, 1, .3, 1);
    }

    .navmenu.is-open .navmenu__list li {
      opacity: 1;
      transform: none;
    }

    .navmenu.is-open .navmenu__list li:nth-child(1) { transition-delay: .13s; }
    .navmenu.is-open .navmenu__list li:nth-child(2) { transition-delay: .19s; }
    .navmenu.is-open .navmenu__list li:nth-child(3) { transition-delay: .25s; }
    .navmenu.is-open .navmenu__list li:nth-child(4) { transition-delay: .31s; }
    .navmenu.is-open .navmenu__list li:nth-child(5) { transition-delay: .37s; }
    .navmenu.is-open .navmenu__list li:nth-child(6) { transition-delay: .43s; }

    .navmenu__link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: clamp(12px, 1.7vh, 19px) 2px;
      font-size: clamp(21px, 4.4vw, 30px);
      font-weight: 500;
      letter-spacing: -.035em;
      transition: padding-left .4s cubic-bezier(.16, 1, .3, 1);
    }

    .navmenu__link:hover {
      padding-left: 12px;
    }

    .navmenu__link svg {
      width: 17px;
      height: 17px;
      flex: 0 0 auto;
      opacity: 0;
      transform: translateX(-8px);
      transition: opacity .35s ease, transform .35s cubic-bezier(.16, 1, .3, 1);
    }

    .navmenu__link:hover svg {
      opacity: 1;
      transform: none;
    }

    .navmenu__foot {
      flex: 0 0 auto;
      margin-top: clamp(20px, 3vh, 34px);
      display: flex;
      flex-direction: column;
      gap: clamp(13px, 2vh, 19px);
      opacity: 0;
      transform: translateY(18px);
      transition: opacity .45s ease, transform .6s cubic-bezier(.16, 1, .3, 1);
    }

    .navmenu.is-open .navmenu__foot {
      opacity: 1;
      transform: none;
      transition-delay: .5s;
    }

    .navmenu__foot-label {
      font-size: 11.5px;
      font-weight: 600;
      letter-spacing: .09em;
      text-transform: uppercase;
      color: var(--ink-soft);
    }

    .cta {
      display: inline-flex;
      align-items: center;
      gap: clamp(12px, 1.1vw, 20px);
      background: var(--ink);
      color: #fff;
      border-radius: 999px;
      padding: 8px 8px 8px clamp(22px, 1.9vw, 34px);
      font-size: clamp(15px, 1.15vw, 19px);
      font-weight: 500;
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px -14px rgba(0, 0, 0, .6);
    }

    .cta__icon {
      width: clamp(32px, 2.5vw, 42px);
      height: clamp(32px, 2.5vw, 42px);
      border-radius: 50%;
      background: #fff;
      color: var(--ink);
      display: grid;
      place-items: center;
      flex: 0 0 auto;
    }

    .cta__icon svg {
      width: 48%;
      height: 48%;
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
        padding: 6px;
      }
      .panel {
        padding-top: clamp(64px, 9vw, 90px);
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
        padding-top: 70px;
        border-radius: 26px;
      }
      .menu__label {
        display: none;
      }
      .menu__bars {
        width: 26px;
      }
      .stage {
        padding: 24px 0 20px;
      }
      .headline {
        font-size: clamp(26px, 7.5vw, 38px);
      }
      .squiggle {
        width: min(72%, 260px);
      }
      .cards {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      }
      .card {
        min-height: 0;
      }
      .side__top {
        top: 22px;
        right: 22px;
      }
      .navmenu__sheet {
        width: 100%;
        border-radius: 0;
        padding-top: max(clamp(20px, 3vw, 38px), env(safe-area-inset-top));
      }
    }

    @media (max-width: 420px) {
      .brand {
        font-size: 18px;
      }
      .headline {
        font-size: clamp(24px, 7.5vw, 32px);
      }
      .account__label {
        display: none;
      }
      .stats {
        gap: 12px;
      }
      .stat__num {
        font-size: clamp(24px, 7.5vw, 34px);
      }
      .teaser__thumb {
        width: 80px;
      }
    }

    @media (max-width: 360px) {
      .hero__inner {
        padding: 8px;
      }
      .panel {
        padding: 14px;
        padding-top: 66px;
      }
      .brand {
        font-size: 16.5px;
      }
      .social {
        width: 30px;
        height: 30px;
      }
      .socials {
        gap: 5px;
      }
      .card {
        padding: 12px;
      }
      .navmenu__link {
        font-size: 20px;
      }
    }

    @media (hover: none) {
      .cta:hover, .arrow-fab:hover, .navmenu__close:hover {
        transform: none;
      }
      .navmenu__link:hover {
        padding-left: 2px;
      }
      .navmenu__link svg {
        opacity: 1;
        transform: none;
      }
      .menu:hover .menu__bars span:nth-child(2) {
        width: 100%;
      }
      .social {
        min-width: 34px;
        min-height: 34px;
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
              <span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block animate-pulse"></span>
              <span>RAKA</span>
            </a>
            <button class="menu" id="menu-btn" aria-label="Open menu" aria-expanded="false">
              <div class="menu__bars">
                <span></span>
                <span></span>
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
          <div class="avatar">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&amp;h=400&amp;fit=crop&amp;crop=faces&amp;q=80" alt="Raka Pre-Order">
          </div>
          <h1 class="headline"><b>Track</b> your gear<br />with <b>Raka Pre-Order</b></h1>
          <svg class="squiggle" viewBox="0 0 320 26" aria-hidden="true">
            <path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
          </svg>
          
          <!-- Pre-Order Form Mount -->
          <div class="stage__form-mount">
            <?php echo do_shortcode( '[preorder_raka]' ); ?>
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
          <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="pill-btn" aria-label="Explore Shop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
            </svg>
          </a>
          <a href="<?php echo esc_url( home_url( '/account' ) ); ?>" class="account">
            <svg class="account__plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="account__label">Account</span>
            <div class="account__avatar">
              <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=160&amp;h=160&amp;fit=crop&amp;crop=faces&amp;q=80" alt="">
            </div>
          </a>
        </div>
        <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="arrow-fab" aria-label="Explore more">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 17L17 7M8 7h9v9"/>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- NAV OVERLAY (outside .hero) -->
  <div class="navmenu" id="navmenu" role="dialog" aria-modal="true" aria-label="Site menu">
    <div class="navmenu__scrim" id="navmenu-scrim"></div>
    <div class="navmenu__sheet">
      <div class="navmenu__head">
        <span class="navmenu__brand">RAKA GADGET</span>
        <button class="navmenu__close" id="navmenu-close" aria-label="Close menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <path d="M5 5l14 14M19 5L5 19"/>
          </svg>
        </button>
      </div>
      <ul class="navmenu__list">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navmenu__link"><span>Home</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></a></li>
        <li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="navmenu__link"><span>Catalog & Products</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></a></li>
        <li><a href="<?php echo esc_url( home_url( '/pre-order' ) ); ?>" class="navmenu__link"><span>Pre-Order Hub</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></a></li>
        <li><a href="<?php echo esc_url( home_url( '/account' ) ); ?>" class="navmenu__link"><span>My Account</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></a></li>
      </ul>
      <div class="navmenu__foot">
        <span class="navmenu__foot-label">Follow along</span>
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
        <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="cta">
          Explore Products
          <span class="cta__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
            </svg>
          </span>
        </a>
      </div>
    </div>
  </div>

  <script>
    /* ═════════ IIFE #1: Entry Animation Cleanup ═════════ */
    (function initAnimationCleanup() {
      const animSelectors = [
        '.topbar .brand', '.topbar .menu', '.topbar .connect',
        '.stage .avatar', '.stage .headline', '.stage .squiggle',
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

    /* ═════════ IIFE #2: Nav Controller ═════════ */
    (function initNavController() {
      const menuBtn = document.getElementById('menu-btn');
      const navmenu = document.getElementById('navmenu');
      const closeBtn = document.getElementById('navmenu-close');
      const scrim = document.getElementById('navmenu-scrim');
      const navLinks = navmenu.querySelectorAll('.navmenu__link');
      let isOpen = false;

      function setOpen(open) {
        if (isOpen === open) return;
        isOpen = open;

        if (isOpen) {
          navmenu.classList.add('is-open');
          menuBtn.classList.add('is-active');
          menuBtn.setAttribute('aria-expanded', 'true');
          menuBtn.setAttribute('aria-label', 'Close menu');
          document.documentElement.classList.add('nav-lock');
          document.body.classList.add('nav-lock');

          setTimeout(() => {
            const firstLink = navmenu.querySelector('.navmenu__link');
            if (firstLink) {
              firstLink.focus();
            } else if (closeBtn) {
              closeBtn.focus();
            }
          }, 360);
        } else {
          navmenu.classList.remove('is-open');
          menuBtn.classList.remove('is-active');
          menuBtn.setAttribute('aria-expanded', 'false');
          menuBtn.setAttribute('aria-label', 'Open menu');
          document.documentElement.classList.remove('nav-lock');
          document.body.classList.remove('nav-lock');
          menuBtn.focus();
        }
      }

      if (menuBtn) menuBtn.addEventListener('click', () => setOpen(!isOpen));
      if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
      if (scrim) scrim.addEventListener('click', () => setOpen(false));
      navLinks.forEach(link => {
        link.addEventListener('click', () => setOpen(false));
      });

      document.addEventListener('keydown', e => {
        if (!isOpen) return;

        if (e.key === 'Escape') {
          setOpen(false);
          return;
        }

        if (e.key === 'Tab') {
          const focusable = navmenu.querySelectorAll('a[href], button');
          if (!focusable.length) return;
          const first = focusable[0];
          const last = focusable[focusable.length - 1];

          if (e.shiftKey) {
            if (document.activeElement === first) {
              e.preventDefault();
              last.focus();
            }
          } else {
            if (document.activeElement === last) {
              e.preventDefault();
              first.focus();
            }
          }
        }
      });
    })();
  </script>

  <?php wp_footer(); ?>
</body>
</html>
