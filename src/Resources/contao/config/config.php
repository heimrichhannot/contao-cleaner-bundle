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
$GLOBALS['TL_CRON']['minutely']['runMinutelyCleaner'] = ['HeimrichHannot\CleanerBundle\Cron\Cleaner', 'runMinutely'];
$GLOBALS['TL_CRON']['hourly']['runHourlyCleaner']     = ['HeimrichHannot\CleanerBundle\Cron\Cleaner', 'runHourly'];
$GLOBALS['TL_CRON']['daily']['runDailyCleaner']       = ['HeimrichHannot\CleanerBundle\Cron\Cleaner', 'runDaily'];
$GLOBALS['TL_CRON']['weekly']['runWeeklyCleaner']     = ['HeimrichHannot\CleanerBundle\Cron\Cleaner', 'runWeekly'];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cleaner'] = 'HeimrichHannot\CleanerBundle\Model\CleanerModel';