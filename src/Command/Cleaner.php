<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Command;

use Contao\Config;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Cleaner extends AbstractLockedCommand
{
    use FrameworkAwareTrait;

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
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $interval;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     */
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

                        $strQuery = "SELECT id FROM $objCleaners->dataContainer WHERE ($objCleaners->whereCondition)";

                        if ($objCleaners->addMaxAge) {
                            $strQuery .= $this->getMaxAgeCondition($objCleaners);
                        }

                        $result = \Database::getInstance()->execute(html_entity_decode($strQuery));
                        $removedCount = 0;

                        if ($result->numRows > 0) {
                            $ids = $result->fetchEach('id');

                            if (null !== ($models = System::getContainer()->get('huh.utils.model')->findModelInstancesBy($objCleaners->dataContainer, [
                                    'column' => ["$objCleaners->dataContainer.id IN(".implode(',', array_map('intval', $ids)).')'],
                                    'value' => null,
                                    'return' => 'Collection',
                                ], []))) {
                                while ($models->next()) {
                                    $data = $models->row();

                                    $affectedRows = $models->delete();

                                    if ($affectedRows > 0 && $objCleaners->addPrivacyProtocolEntry) {
                                        ++$removedCount;

                                        $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();

                                        if ($objCleaners->privacyProtocolEntryDescription) {
                                            $data['description'] = $objCleaners->privacyProtocolEntryDescription;
                                        }

                                        $protocolManager->addEntry(
                                            $objCleaners->privacyProtocolEntryType,
                                            $objCleaners->privacyProtocolEntryArchive,
                                            $data,
                                            'heimrichhannot/contao-cleaner-bundle'
                                        );
                                    }
                                }
                            }
                        }

                        $this->output->writeln("<fg=green>Cleanup table '".$objCleaners->dataContainer."', removed ".$removedCount.' entries ['.$objCleaners->title.'].</>');

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

                                                if (true === $objFile->delete()) {
                                                    $this->output->writeln("<fg=green>Cleanup files, removed file '".$objFile->path.' ['.$objCleaners->title.'].</>');
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

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Provide the interval.', 'daily');

        $this->setName('cleaner:execute')->setDescription(
            'Migration of tl_module type:newsreader modules to huhreader and creates reader configurations from old tl_module settings.'
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
}
