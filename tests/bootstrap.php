<?php

/**
 * Load composer autoload.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Set the default timezone
 */
date_default_timezone_set('UTC');

/*
 * Set the default locale
 */
setlocale(LC_ALL, 'C.UTF-8');
