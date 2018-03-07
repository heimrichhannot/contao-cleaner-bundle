<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Cron;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;

class CommandCleaner extends Controller
{
    const TYPE_ENTITY = 'entity';
    const TYPE_FILE = 'file';

    const TYPES = [
        self::TYPE_ENTITY,
        self::TYPE_FILE,
    ];

    const FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS = 'entityFields';
    const FILEDIR_RETRIEVAL_MODE_DIRECTORY = 'directory';

    const FILEDIR_RETRIEVAL_MODES = [
        self::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS,
        self::FILEDIR_RETRIEVAL_MODE_DIRECTORY,
    ];

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $interval;

    public function __construct(ContaoFrameworkInterface $framework, string $interval)
    {
        parent::__construct();

        $this->framework = $framework;
        $this->interval = $interval;
    }

    public function run()
    {
        $arrOrder = StringUtil::deserialize(Config::get('cleanerOrder'), true);
        $arrOptions = [];

        if (count($arrOrder) > 0) {
            $arrOptions = [
                'order' => 'FIELD(id,'.implode(',', $arrOrder).')',
            ];
        }

        if (null !== ($objCleaners = System::getContainer()->get('huh.cleaner.registry.cleaner')->findBy(['published=?', 'period=?'], [true, $this->interval], $arrOptions))) {
            while ($objCleaners->next()) {
                switch ($objCleaners->type) {
                    case static::TYPE_ENTITY:
                        if (!$objCleaners->whereCondition) {
                            continue 2;
                        }

                        $strQuery = "DELETE FROM $objCleaners->dataContainer WHERE ($objCleaners->whereCondition)";

                        if ($objCleaners->addMaxAge) {
                            $strQuery .= $this->getMaxAgeCondition($objCleaners);
                        }

                        \Database::getInstance()->execute(html_entity_decode($strQuery));

                        break;
                    case static::TYPE_FILE:
                        switch ($objCleaners->fileDirRetrievalMode) {
                            case static::FILEDIR_RETRIEVAL_MODE_DIRECTORY:
                                $strPath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($objCleaners->directory);

                                $objFolder = new Folder($strPath);

                                $objFolder->purge();

                                if ($objCleaners->addGitKeepAfterClean) {
                                    touch(TL_ROOT.'/'.$strPath.'/.gitkeep');
                                }

                                break;
                            case static::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS:
                                if (!$objCleaners->whereCondition) {
                                    continue 2;
                                }

                                $arrFields = StringUtil::deserialize($objCleaners->entityFields, true);

                                if (empty($arrFields)) {
                                    continue 2;
                                }

                                $strQuery = "SELECT * FROM $objCleaners->dataContainer WHERE ($objCleaners->whereCondition)";

                                if ($objCleaners->addMaxAge) {
                                    $strQuery .= $this->getMaxAgeCondition($objCleaners);
                                }

                                $objInstances = \Database::getInstance()->execute(html_entity_decode($strQuery));

                                if ($objInstances->numRows > 0) {
                                    while ($objInstances->next()) {
                                        foreach ($arrFields as $strField) {
                                            if (!$objInstances->{$strField}) {
                                                continue;
                                            }

                                            // deserialize if necessary
                                            $varValue = StringUtil::deserialize($objInstances->{$strField});

                                            if (!is_array($varValue)) {
                                                $varValue = [$varValue];
                                            }

                                            foreach ($varValue as $strFile) {
                                                if (null === ($objFile = System::getContainer()->get('huh.utils.file')->getFileFromUuid($strFile, true))) {
                                                    continue;
                                                }

                                                $objFile->delete();
                                            }
                                        }
                                    }
                                }
                                break;
                        }

                        break;
                }
            }
        }
    }

    public function getMaxAgeCondition($objCleaner)
    {
        $arrMaxAge = StringUtil::deserialize($objCleaner->maxAge, true);

        $intFactor = 1;
        switch ($arrMaxAge['unit']) {
            case 'm':
                $intFactor = 60;
                break;
            case 'h':
                $intFactor = 60 * 60;
                break;
            case 'd':
                $intFactor = 24 * 60 * 60;
                break;
        }

        $intMaxInterval = $arrMaxAge['value'] * $intFactor;

        return " AND (UNIX_TIMESTAMP() > $objCleaner->dataContainer.$objCleaner->maxAgeField + $intMaxInterval)";
    }
}
