<?php

use HeimrichHannot\CleanerBundle\Model\CleanerModel;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['cleaner'] = [
    'tables' => ['tl_cleaner'],
    'icon'   => 'bundles/heimrichhannotcontaocleaner/img/icon.png',
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cleaner'] = CleanerModel::class;
