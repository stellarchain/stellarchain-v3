<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.5',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'chart.js' => [
        'version' => '4.4.1',
    ],
    '@swup/fade-theme' => [
        'version' => '1.0.5',
    ],
    '@swup/slide-theme' => [
        'version' => '1.0.5',
    ],
    '@swup/forms-plugin' => [
        'version' => '2.0.1',
    ],
    '@swup/plugin' => [
        'version' => '2.0.3',
    ],
    'swup' => [
        'version' => '3.1.1',
    ],
    'delegate-it' => [
        'version' => '6.0.1',
    ],
    '@swup/debug-plugin' => [
        'version' => '3.0.0',
    ],
    'typed.js' => [
        'version' => '2.1.0',
    ],
    'axios' => [
        'version' => '1.7.4',
    ],
    'swiper/bundle' => [
        'version' => '11.1.9',
    ],
    'swiper/css/bundle' => [
        'version' => '11.1.9',
    ],
    'swiper/modules' => [
        'version' => '11.1.9',
    ],
    'add-to-calendar-button' => [
        'version' => '2.6.18',
    ],
    'timezones-ical-library' => [
        'version' => '1.8.3',
    ],
    'chartjs-plugin-zoom' => [
        'version' => '2.0.1',
    ],
    'hammerjs' => [
        'version' => '2.0.8',
    ],
    'chart.js/helpers' => [
        'version' => '4.4.3',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    'chartjs-plugin-crosshair' => [
        'version' => '2.0.0',
    ],
    '@swup/theme' => [
        'version' => '2.1.0',
    ],
    'path-to-regexp' => [
        'version' => '6.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'chartjs-plugin-gradient' => [
        'version' => '0.6.1',
    ],
    'date-fns' => [
        'version' => '4.1.0',
    ],
    'chartjs-adapter-date-fns' => [
        'version' => '3.0.0',
    ],
    '@stellar/stellar-sdk' => [
        'version' => '12.3.0',
    ],
    'lightweight-charts' => [
        'version' => '4.2.1',
    ],
    'fancy-canvas' => [
        'version' => '2.1.0',
    ],
];
