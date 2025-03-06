<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Command;

use HeimrichHannot\Privacy\Manager\ProtocolManager;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\File;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\CleanerBundle\Event\AfterCleanEvent;
use HeimrichHannot\CleanerBundle\Event\BeforeCleanEvent;
use HeimrichHannot\CleanerBundle\Exception\InvalidIntervalException;
use HeimrichHannot\CleanerBundle\Model\CleanerModel;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CleanerCommand extends Command
{
    const TYPE_ENTITY = 'entity';
    const TYPE_DEPENDENT_ENTITY = 'dependent_entity';
    const TYPE_FILE = 'file';
    const TYPES = [
        self::TYPE_ENTITY,
        self::TYPE_DEPENDENT_ENTITY,
        self::TYPE_FILE,
    ];

    const FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS = 'entityFields';
    const FILEDIR_RETRIEVAL_MODE_DIRECTORY = 'directory';
    const FILEDIR_RETRIEVAL_MODES = [
        self::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS,
        self::FILEDIR_RETRIEVAL_MODE_DIRECTORY,
    ];

    const INTERVAL_MINUTELY = 'minutely';
    const INTERVAL_HOURLY = 'hourly';
    const INTERVAL_DAILY = 'daily';
    const INTERVAL_WEEKLY = 'weekly';
    const INTERVAL_MONTHLY = 'monthly';
    const INTERVALS = [
        self::INTERVAL_MINUTELY,
        self::INTERVAL_HOURLY,
        self::INTERVAL_DAILY,
        self::INTERVAL_MONTHLY,
        self::INTERVAL_WEEKLY
    ];

    protected ContaoFramework $framework;
    protected EventDispatcherInterface $eventDispatcher;
    protected Utils $utils;
    protected string $projectDir;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;
    protected string $interval;

    public function getInterval(): string
    {
        return $this->interval;
    }

    public function setInterval(?string $interval): void
    {
        if (!\in_array($interval, static::INTERVALS, true)) {
            throw new InvalidIntervalException(
                $interval === null
                    ? "No interval provided."
                    : \sprintf(
                        'Invalid interval "%s" provided. Should be one of: %s.',
                        $interval,
                        implode(', ', static::INTERVALS)
                )
            );
        }
        $this->interval = $interval;
    }

    public function __construct(
        ContaoFramework          $framework,
        EventDispatcherInterface $eventDispatcher,
        Utils                    $utils,
        string                   $projectDir,
        ?string                  $name = null
    ) {
        $this->framework = $framework;
        $this->eventDispatcher = $eventDispatcher;
        $this->utils = $utils;
        $this->projectDir = $projectDir;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cleaner:execute')
            ->setDescription('Trigger the cleaner, and remove no longer required files and database entries.')
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_REQUIRED,
                \sprintf('Provide an interval: %s.', implode(', ', static::INTERVALS))
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;

        try
        {
            $interval = $input->getOption('interval');
            $this->setInterval($interval);

            $this->clean();
        }
        catch (InvalidIntervalException $e)
        {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }
        catch (\Throwable $e)
        {
            $this->io->error($e->getMessage());
            $this->io->getErrorStyle()->block($e->getTraceAsString());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function clean(): void
    {
        $order = StringUtil::deserialize(Config::get('cleanerOrder'), true);
        $options = [];

        if (\count($order) > 0) {
            $options = ['order' => \sprintf('FIELD(id,%s)', \implode(',', $order))];
        }

        $cleaners = CleanerModel::findBy(['published=?', 'period=?'], [true, $this->interval], $options);

        if ($cleaners === null) {
            return;
        }

        while ($cleaners->next()) {
            $this->executeCleaner($cleaners->current());
        }
    }

    /**
     * @throws \Exception
     */
    protected function executeCleaner(CleanerModel $cleaner)
    {
        switch ($cleaner->type)
        {
            case static::TYPE_ENTITY:
                $this->handleEntityType($cleaner);
                break;

            case static::TYPE_DEPENDENT_ENTITY:
                $this->handleDependentEntityType($cleaner);
                break;

            case static::TYPE_FILE:
                $this->handleFileType($cleaner);
                break;
        }
    }

    /**
     * @throws \Exception
     */
    protected function handleFileType(CleanerModel $cleaner)
    {
        switch ($cleaner->fileDirRetrievalMode)
        {
            case static::FILEDIR_RETRIEVAL_MODE_DIRECTORY:
                $this->handleFileTypeRetrieveDirectory($cleaner);
                return;

            case static::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS:
                $this->handleFileTypeRetrieveEntityFields($cleaner);
                return;
        }
    }

    /**
     * @throws \Exception
     */
    protected function handleFileTypeRetrieveDirectory(CleanerModel $cleaner): void
    {
        $strPath = $this->utils->file()->getPathFromUuid($cleaner->directory);

        $objFolder = new Folder($strPath);

        $objFolder->purge();

        if ($cleaner->addGitKeepAfterClean) {
            \touch(System::getContainer()->getParameter('kernel.project_dir').'/'.$strPath.'/.gitkeep');
        }

        $this->output->writeln("<fg=green>Cleanup folder '".$strPath.' ['.$cleaner->title.'].</>');
    }

    protected function handleFileTypeRetrieveEntityFields(CleanerModel $cleaner): void
    {
        if (!$cleaner->whereCondition) {
            return;
        }

        $arrFields = StringUtil::deserialize($cleaner->entityFields, true);

        if (empty($arrFields)) {
            return;
        }

        $strQuery = "SELECT * FROM $cleaner->dataContainer WHERE ($cleaner->whereCondition)";

        if ($cleaner->addMaxAge)
        {
            $strQuery .= $this->getMaxAgeCondition(
                $cleaner->dataContainer,
                $cleaner->maxAgeField,
                $cleaner->maxAge
            );
        }

        $db = Database::getInstance();

        $result = $db->execute(html_entity_decode($strQuery));

        if ($result->numRows < 1) {
            return;
        }

        while ($result->next())
        {
            foreach ($arrFields as $strField)
            {
                if (!$result->{$strField}) {
                    continue;
                }

                // deserialize if necessary
                $value = StringUtil::deserialize($result->{$strField});

                if (!\is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $fileUuid)
                {
                    $filePath = $this->utils->file()->getPathFromUuid($fileUuid);
                    if (null === $filePath) {
                        continue;
                    }

                    try {
                        $file = new File($filePath);
                    } catch (\Exception $e) {
                        continue;
                    }

                    if ($file->delete() !== true) {
                        continue;
                    }

                    $this->output->writeln(\sprintf(
                        "<fg=green>Cleanup files, removed file '%s' [%s].</>",
                        $file->path,
                        $cleaner->title
                    ));
                }
            }
        }
    }

    protected function handleDependentEntityType(CleanerModel $cleaner)
    {
        if (!$cleaner->whereCondition) {
            return;
        }

        $query = "SELECT * FROM $cleaner->dependentTable WHERE $cleaner->whereCondition";

        if ($cleaner->addMaxAge) {
            $query .= static::getMaxAgeCondition($cleaner->dependentTable,
                $cleaner->maxAgeField, $cleaner->maxAge);
        }

        $db = Database::getInstance();

        $dependenceEntities = $db->execute(html_entity_decode($query));

        if (0 == $dependenceEntities->numRows) {
            return;
        }

        $dependenceEntities = $dependenceEntities->fetchEach('id');
        $inEntities = implode(',', $dependenceEntities);
        $query = "SELECT * FROM $cleaner->dataContainer WHERE $cleaner->dataContainer.$cleaner->dependentField IN ($inEntities)";

        $cleanEntities = $db->execute(html_entity_decode($query));

        if (0 == $cleanEntities->numRows) {
            return;
        }

        while ($cleanEntities->next()) {
            static::cleanEntity($cleanEntities, $cleaner);
        }
    }

    protected function handleEntityType(CleanerModel $cleaner)
    {
        if (!$cleaner->whereCondition) {
            return;
        }

        $strQuery = "SELECT id FROM $cleaner->dataContainer WHERE ($cleaner->whereCondition)";

        if ($cleaner->addMaxAge) {
            $strQuery .= $this->getMaxAgeCondition($cleaner->dataContainer,
                $cleaner->maxAgeField, $cleaner->maxAge);
        }

        $db = Database::getInstance();

        $result = $db->execute(html_entity_decode($strQuery));
        $removedCount = 0;

        if (0 == $result->numRows) {
            return;
        }

        foreach ($result->fetchEach('id') as $id)
        {
            $singleResult = $db->prepare("SELECT * FROM $cleaner->dataContainer WHERE $cleaner->dataContainer.id=?")
                ->limit(1)
                ->execute($id);

            if (0 == $singleResult->numRows) {
                continue;
            }

            if (!$this->cleanEntity($singleResult, $cleaner)) {
                continue;
            }

            ++$removedCount;
        }

        $this->output->writeln(\sprintf(
            "<fg=green>Cleanup table '%s', removed %s entries [%s].</>",
            $cleaner->dataContainer,
            $removedCount,
            $cleaner->title
        ));
    }

    /**
     * @param string $table
     * @param string $maxAgeField
     * @param string $maxAge
     * @return string
     */
    public function getMaxAgeCondition(string $table, string $maxAgeField, string $maxAge): string
    {
        $arrMaxAge = StringUtil::deserialize($maxAge, true);

        $intFactor = 1;

        switch ($arrMaxAge['unit'])
        {
            case 'm':
                $intFactor = 60;
                break;

            case 'h':
                $intFactor = 60 * 60;
                break;

            case 'd':
                $intFactor = 24 * 60 * 60;
                break;

            case 'w':
                $intFactor = 7 * 24 * 60 * 60;
                break;

            case 'M':
                $intFactor = 30 * 24 * 60 * 60;
                break;

            case 'Y':
                $intFactor = 365 * 24 * 60 * 60;
                break;
        }

        $intMaxInterval = $arrMaxAge['value'] * $intFactor;

        return " AND (UNIX_TIMESTAMP() > $table.$maxAgeField + $intMaxInterval)";
    }

    /**
     * delete the entity.
     *
     * @param $entity
     * @param $cleaner
     *
     * @return bool
     */
    protected function cleanEntity($entity, $cleaner): bool
    {
        $data = $entity->row();
        $data['table'] = $cleaner->dataContainer;

        /** @var BeforeCleanEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new BeforeCleanEvent($data, $cleaner->current(), false),
            BeforeCleanEvent::NAME
        );

        if ($event->isSkipped()) {
            return false;
        }

        if ($cleaner->useEntityOnDeleteCallback) {
            $this->applyOnDeleteCallback($entity, $cleaner);
        }

        $deleteResult = Database::getInstance()
            ->prepare("DELETE FROM $cleaner->dataContainer WHERE $cleaner->dataContainer.id=?")
            ->execute($entity->id);

        if ($deleteResult->affectedRows < 1) {
            return false;
        }

        if ($cleaner->addPrivacyProtocolEntry
            && \class_exists(ProtocolManager::class))
        {
            $protocolManager = new ProtocolManager();

            if ($cleaner->privacyProtocolEntryDescription) {
                $data['description'] = $cleaner->privacyProtocolEntryDescription;
            }

            $protocolManager->addEntry(
                $cleaner->privacyProtocolEntryType,
                $cleaner->privacyProtocolEntryArchive,
                $data,
                'heimrichhannot/contao-cleaner-bundle'
            );
        }

        /* @var AfterCleanEvent $event */
        $this->eventDispatcher->dispatch(new AfterCleanEvent($data, $cleaner->current()), AfterCleanEvent::NAME);

        return true;
    }

    /**
     * @param $entity
     * @param $cleaner
     */
    protected function applyOnDeleteCallback($entity, $cleaner): void
    {
        Controller::loadDataContainer($cleaner->dataContainer);

        if (!\is_array($GLOBALS['TL_DCA'][$cleaner->dataContainer]['config']['ondelete_callback'] ?? null)) {
            return;
        }

        $dc = new DC_Table_Utils($cleaner->dataContainer);
        $dc->activeRecord = $entity->row();
        $dc->id = $entity->id;

        foreach ($GLOBALS['TL_DCA'][$cleaner->dataContainer]['config']['ondelete_callback'] as $callback)
        {
            if (\is_array($callback))
            {
                System::importStatic($callback[0])->{$callback[1]}($dc, 0);
            }
            elseif (\is_callable($callback))
            {
                $callback($dc, 0);
            }
        }
    }
}