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
$GLOBALS['TL_CRON']['minutely']['runMinutelyCleaner'] = ['huh.cleaner.controller.poormanscron', 'runMinutely'];
$GLOBALS['TL_CRON']['hourly']['runHourlyCleaner']     = ['huh.cleaner.controller.poormanscron', 'runHourly'];
$GLOBALS['TL_CRON']['daily']['runDailyCleaner']       = ['huh.cleaner.controller.poormanscron', 'runDaily'];
$GLOBALS['TL_CRON']['weekly']['runWeeklyCleaner']     = ['huh.cleaner.controller.poormanscron', 'runWeekly'];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cleaner'] = 'HeimrichHannot\CleanerBundle\Model\CleanerModel';
