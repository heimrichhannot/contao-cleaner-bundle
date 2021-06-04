<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Command;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanerCommand extends AbstractLockedCommand
{
    use FrameworkAwareTrait;

    const TYPE_ENTITY = 'entity';
    const TYPE_DEPENDENT_ENTITY = 'dependent_entity';
    const TYPE_FILE = 'file';

    const TYPES
        = [
            self::TYPE_ENTITY,
            self::TYPE_DEPENDENT_ENTITY,
            self::TYPE_FILE,
        ];

    const FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS = 'entityFields';
    const FILEDIR_RETRIEVAL_MODE_DIRECTORY = 'directory';

    const FILEDIR_RETRIEVAL_MODES
        = [
            self::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS,
            self::FILEDIR_RETRIEVAL_MODE_DIRECTORY,
        ];

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    private $interval;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    public function getInterval(): string
    {
        return $this->interval;
    }

    public function setInterval(string $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @throws \Exception
     *
     * @return int|void
     */
    public function executeCleaner()
    {
        $arrOrder = StringUtil::deserialize(Config::get('cleanerOrder'), true);
        $arrOptions = [];
        $db = Database::getInstance();

        if (\count($arrOrder) > 0) {
            $arrOptions = [
                'order' => 'FIELD(id,'.implode(',', $arrOrder).')',
            ];
        }

        if (null !== ($objCleaners = System::getContainer()->get('huh.cleaner.registry.cleaner')->findBy([
                'published=?',
                'period=?',
            ], [true, $this->interval], $arrOptions))) {
            while ($objCleaners->next()) {
                switch ($objCleaners->type) {
                    case static::TYPE_ENTITY:
                        if (!$objCleaners->whereCondition) {
                            continue 2;
                        }

                        $strQuery = "SELECT id FROM $objCleaners->dataContainer WHERE ($objCleaners->whereCondition)";

                        if ($objCleaners->addMaxAge) {
                            $strQuery .= $this->getMaxAgeCondition($objCleaners->dataContainer,
                                $objCleaners->maxAgeField, $objCleaners->maxAge);
                        }

                        $result = $db->execute(html_entity_decode($strQuery));
                        $removedCount = 0;

                        if (0 == $result->numRows) {
                            continue 2;
                        }

                        foreach ($result->fetchEach('id') as $id) {
                            $singleResult = $db->prepare("SELECT * FROM $objCleaners->dataContainer WHERE $objCleaners->dataContainer.id=?")
                                ->limit(1)
                                ->execute($id);

                            if (0 == $singleResult->numRows) {
                                continue;
                            }

                            if (!$this->cleanEntity($singleResult, $objCleaners)) {
                                continue;
                            }

                            ++$removedCount;
                        }

                        $this->output->writeln("<fg=green>Cleanup table '".$objCleaners->dataContainer."', removed ".$removedCount
                            .' entries ['.$objCleaners->title.'].</>');

                        break;
                    case static::TYPE_DEPENDENT_ENTITY:
                        if (!$objCleaners->whereCondition) {
                            continue 2;
                        }

                        $query = "SELECT * FROM $objCleaners->dependentTable WHERE $objCleaners->whereCondition";

                        if ($objCleaners->addMaxAge) {
                            $query .= static::getMaxAgeCondition($objCleaners->dependentTable,
                                $objCleaners->maxAgeField, $objCleaners->maxAge);
                        }

                        $dependenceEntities = $db->execute(html_entity_decode($query));

                        if (0 == $dependenceEntities->numRows) {
                            continue 2;
                        }

                        $dependenceEntities = $dependenceEntities->fetchEach('id');
                        $query = "SELECT * FROM $objCleaners->dataContainer WHERE $objCleaners->dataContainer.$objCleaners->dependentField IN (".implode(',',
                                $dependenceEntities).')';

                        $cleanEntities = $db->execute(html_entity_decode($query));

                        if (0 == $cleanEntities->numRows) {
                            continue 2;
                        }

                        while ($cleanEntities->next()) {
                            static::cleanEntity($cleanEntities, $objCleaners);
                        }

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

                                $this->output->writeln("<fg=green>Cleanup folder '".$strPath.' ['.$objCleaners->title.'].</>');

                                break;
                            case static::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS:
                                if (!$objCleaners->whereCondition) {
                                    continue 3;
                                }

                                $arrFields = StringUtil::deserialize($objCleaners->entityFields, true);

                                if (empty($arrFields)) {
                                    continue 3;
                                }

                                $strQuery = "SELECT * FROM $objCleaners->dataContainer WHERE ($objCleaners->whereCondition)";

                                if ($objCleaners->addMaxAge) {
                                    $strQuery .= $this->getMaxAgeCondition($objCleaners);
                                }

                                $objInstances = $db->execute(html_entity_decode($strQuery));

                                if ($objInstances->numRows > 0) {
                                    while ($objInstances->next()) {
                                        foreach ($arrFields as $strField) {
                                            if (!$objInstances->{$strField}) {
                                                continue;
                                            }

                                            // deserialize if necessary
                                            $varValue = StringUtil::deserialize($objInstances->{$strField});

                                            if (!\is_array($varValue)) {
                                                $varValue = [$varValue];
                                            }

                                            foreach ($varValue as $strFile) {
                                                if (null === ($objFile = System::getContainer()->get('huh.utils.file')->getFileFromUuid($strFile))) {
                                                    continue;
                                                }

                                                if (true === $objFile->delete()) {
                                                    $this->output->writeln("<fg=green>Cleanup files, removed file '".$objFile->path.' ['
                                                        .$objCleaners->title.'].</>');
                                                }
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

    /**
     * @return string
     */
    public function getMaxAgeCondition(string $table, string $maxAgeField, string $maxAge)
    {
        $arrMaxAge = StringUtil::deserialize($maxAge, true);

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

        return " AND (UNIX_TIMESTAMP() > $table.$maxAgeField + $intMaxInterval)";
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Provide the interval.', 'daily');

        $this->setName('cleaner:execute')->setDescription(
            'Trigger the cleaner, and remove no longer required files and database entries.'
        );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;

        $this->setInterval($input->getOption('interval'));

        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');

        try {
            $this->executeCleaner();
        } catch (\Exception $e) {
            $this->output->writeln('<fg=red>'.$e->getMessage().'</>');
        }

        return 0;
    }

    /**
     * delete the entity.
     *
     * @param $entity
     * @param $cleaner
     *
     * @return bool
     */
    protected function cleanEntity($entity, $cleaner)
    {
        $data = $entity->row();
        $data['table'] = $cleaner->dataContainer;

        if ($cleaner->useEntityOnDeleteCallback) {
            $this->applyOnDeleteCallback($entity, $cleaner);
        }

        $deleteResult = Database::getInstance()->prepare("DELETE FROM $cleaner->dataContainer WHERE $cleaner->dataContainer.id=?")->execute($entity->id);

        if ($deleteResult->affectedRows > 0 && $cleaner->addPrivacyProtocolEntry) {
            $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();

            if ($cleaner->privacyProtocolEntryDescription) {
                $data['description'] = $cleaner->privacyProtocolEntryDescription;
            }

            $protocolManager->addEntry(
                $cleaner->privacyProtocolEntryType,
                $cleaner->privacyProtocolEntryArchive,
                $data,
                'heimrichhannot/contao-cleaner-bundle'
            );

            return true;
        }

        return false;
    }

    /**
     * @param $entity
     * @param $cleaner
     */
    protected function applyOnDeleteCallback($entity, $cleaner): void
    {
        Controller::loadDataContainer($cleaner->dataContainer);

        if (\is_array($GLOBALS['TL_DCA'][$cleaner->dataContainer]['config']['ondelete_callback'])) {
            $dc = new DC_Table_Utils($cleaner->dataContainer);
            $dc->activeRecord = $entity->row();
            $dc->id = $entity->id;

            foreach ($GLOBALS['TL_DCA'][$cleaner->dataContainer]['config']['ondelete_callback'] as $callback) {
                if (\is_array($callback)) {
                    Controller::importStatic($callback[0])->{$callback[1]}($dc, 0);
                } elseif (\is_callable($callback)) {
                    $callback($dc, 0);
                }
            }
        }
    }
}
