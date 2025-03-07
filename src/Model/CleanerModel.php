<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property string $tstamp
 * @property string $dateAdded
 * @property string $type
 * @property string $title
 * @property string $description
 * @property bool   $published
 * @property string $dataContainer
 * @property bool   $addMaxAge
 * @property string $maxAge
 * @property string $maxAgeField
 * @property string $period
 * @property string $whereCondition
 * @property string $fileDirRetrievalMode
 * @property mixed  $entityFields
 * @property string $directory
 * @property bool   $addGitKeepAfterClean
 * @property string $dependentTable
 * @property string $dependentField
 * @property bool   $useEntityOnDeleteCallback
 */
class CleanerModel extends Model
{
    protected static $strTable = 'tl_cleaner';
}
