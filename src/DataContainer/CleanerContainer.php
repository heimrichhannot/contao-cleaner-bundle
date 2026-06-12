<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Log\LogLevel;

class CleanerContainer
{
    public function __construct(
        private readonly Utils $utils,
    ) {
    }

    #[AsCallback(table: 'tl_cleaner', target: 'fields.dataContainer.options')]
    #[AsCallback(table: 'tl_cleaner', target: 'fields.dependentTable.options')]
    public function getTables(?DataContainer $dc = null): array
    {
        return Database::getInstance()->listTables();
    }

    #[AsCallback(table: 'tl_cleaner', target: 'fields.entityFields.options')]
    #[AsCallback(table: 'tl_cleaner', target: 'fields.dependentField.options')]
    public function getFieldsAsOptions(DataContainer $dc): array
    {
        if (!$dc->activeRecord->dataContainer) {
            return [];
        }

        return $this->utils->dca()->getDcaFields($dc->activeRecord->dataContainer);
    }

    #[AsCallback(table: 'tl_cleaner', target: 'list.operations.toggle.button')]
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes): string
    {
        $objUser = BackendUser::getInstance();

        if (strlen((string) Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), '1' === Input::get('state'));
            Controller::redirect(Controller::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return \sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    public function toggleVisibility($id, $blnVisible): void
    {
        $objUser = BackendUser::getInstance();
        $db = Database::getInstance();
        $logger = System::getContainer()->get('monolog.logger.contao');

        // Check permissions to publish
        if (!$objUser->isAdmin && !$objUser->hasAccess('tl_cleaner::published', 'alexf')) {
            $logger->log(LogLevel::ERROR, 'Not enough permissions to publish/unpublish item ID "' . $id . '"', [
                'contao' => new ContaoContext('tl_cleaner toggleVisibility', LogLevel::ERROR),
            ]);

            Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_cleaner', $id);
        $objVersions->initialize();

        // Trigger the save_callback
        if (isset($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'])
            && is_array($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_cleaner']['fields']['published']['save_callback'] as $callback) {
                System::importStatic($callback[0]);
                $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        $db->prepare(\sprintf(
            'UPDATE `tl_cleaner` SET tstamp=%s, published=%s WHERE id=?',
            time(),
            $blnVisible ? 1 : "''"
        ))->execute($id);

        $objVersions->create();

        $logger->log(LogLevel::INFO, \sprintf('A new version of record "tl_cleaner.id=%s" has been created', $id), [
            'contao' => new ContaoContext(__METHOD__, LogLevel::INFO),
        ]);
    }
}
