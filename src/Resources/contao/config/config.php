<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['cleaner'] = [
    'tables' => ['tl_cleaner'],
    'icon'   => 'system/modules/cleaner/assets/img/icon.png',
];

/**
 * Crons
 */
$GLOBALS['TL_CRON']['minutely']['runMinutelyCleaner'] = ['huh.cleaner.cron.command.minutely', 'run'];
$GLOBALS['TL_CRON']['hourly']['runHourlyCleaner']     = ['huh.cleaner.cron.command.hourly', 'run'];
$GLOBALS['TL_CRON']['daily']['runDailyCleaner']       = ['huh.cleaner.cron.command.daily', 'run'];
$GLOBALS['TL_CRON']['weekly']['runWeeklyCleaner']     = ['huh.cleaner.cron.command.weekly', 'run'];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cleaner'] = 'HeimrichHannot\CleanerBundle\Model\CleanerModel';