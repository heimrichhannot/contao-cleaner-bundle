<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\CleanerBundle\Command\CleanerCommand;

$lang = &$GLOBALS['TL_LANG']['tl_cleaner'];

/*
 * Fields
 */
$lang['title'] = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['description'] = ['Beschreibung', 'Geben Sie hier bitte eine Beschreibung des Cleaners ein.'];
$lang['type'] = ['Typ', 'Wählen Sie hier einen Typ aus.'];
$lang['tstamp'] = ['Änderungsdatum', ''];
$lang['dataContainer'] = ['Entität', 'Wählen Sie hier die Datenbanktabelle der gewünschten Entität aus, die gelöscht werden soll.'];
$lang['whereCondition'] = ['Bedingung für das Löschen (!)', 'Geben Sie hier die Bedingung ein, die erfüllt sein muss, damit eine Entität gelöscht wird. Aus Sicherheitsgründen ist dieses Feld ein Pflichtfeld. Wenn Sie keine Bedingung vergeben möchten, geben Sie einfach 1=1 ein.'];
$lang['addMaxAge'] = ['Maximales Alter hinzufügen', 'Wählen Sie diese Option, wenn ptoentiell zu löschende Entitäten ein bestimmtes Maximalalter haben dürfen, das sie vor dem Löschen schützt.'];
$lang['maxAge'] = ['Maximales Alter inaktiver Datensätze', 'Wählen Sie hier aus, wie alt eine inaktive Datensatz höchstens sein darf, bevor sie gelöscht wird.'];
$lang['maxAge']['m'] = 'Minute(n)';
$lang['maxAge']['h'] = 'Stunde(n)';
$lang['maxAge']['d'] = 'Tag(e)';
$lang['maxAgeField'] = ['Feld für das Datensatzalter', 'Geben Sie hier den Namen des Feldes ein, welches für die Berechnung des maximalen Alters herangezogen werden soll.'];
$lang['period'] = ['Zeitintervall', 'Wählen Sie hier, wie oft die Säuberung ausgeführt werden soll. Dabei wird Contaos Poor Man\'s Cron (TL_CRON) verwendet.'];
$lang['period']['minutely'] = 'Jede Minute';
$lang['period']['hourly'] = 'Jede Stunde';
$lang['period']['daily'] = 'Jeden Tag';
$lang['period']['weekly'] = 'Jede Woche';
$lang['period']['monthly'] = 'Jeden Monat';
$lang['published'] = ['Aktiviert', 'Wählen Sie diese Option, um den Cleaner zu aktivieren.'];
$lang['fileDirRetrievalMode'] = ['Zu entfernende Dateien & Verzeichnisse ermitteln durch', 'Wählen Sie hier aus, wie die zu entfernenden Dateien & Verzeichnisse ermittelt werden sollen.'];
$lang['entityFields'] = ['Felder', 'Wählen Sie hier die Felder aus, in denen eine Referenz zu einer oder mehreren Dateien bzw. Verzeichnissen gespeichert ist.'];
$lang['directory'] = ['Zu leerendes Verzeichnis', 'Wählen Sie hier das relevante zu leerende Verzeichnis aus.'];
$lang['addGitKeepAfterClean'] = ['.gitkeep nach dem Leeren erzeugen', 'Wählen Sie diese Option, wenn Sie nach dem Leeren des Verzeichnisses in diesem eine .gitkeep-Datei erzeugen wollen. Sinnvoll, wenn das Verzeichnis auch "leer" in ein git-Repository eingecheckt werden soll.'];
$lang['dependentTable'] = ['abhängige Tabelle', 'Wählen Sie hier die Tabelle die die Entität beinhaltet von welcher die Löschung abhängt.'];
$lang['dependentField'] = ['Feld', 'Wählen Sie hier das Feld aus über das die zu löschende Entität mit der abhängigen Entität verbunden ist.'];
$lang['useEntityOnDeleteCallback'] = ['ondelete_callback der Entität nutzen', 'Wählen Sie diese Option, wenn der ondelete_callback der Entität beim Löschen ausgeführt werden soll.'];

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['config_legend'] = 'Konfiguration';
$lang['publish_legend'] = 'Aktivierung';

/*
 * Reference
 */
$lang['reference'] = [
    CleanerCommand::TYPE_ENTITY => 'Entität',
    CleanerCommand::TYPE_DEPENDENT_ENTITY => 'abhängige Entität',
    CleanerCommand::TYPE_FILE => 'Datei',
    CleanerCommand::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS => 'Felder von Entitäten',
    CleanerCommand::FILEDIR_RETRIEVAL_MODE_DIRECTORY => 'Verzeichnis',
];

/*
 * Buttons
 */
$lang['new'] = ['Neuer Cleaner', 'Cleaner erstellen'];
$lang['edit'] = ['Cleaner bearbeiten', 'Cleaner ID %s bearbeiten'];
$lang['copy'] = ['Cleaner duplizieren', 'Cleaner ID %s duplizieren'];
$lang['delete'] = ['Cleaner löschen', 'Cleaner ID %s löschen'];
$lang['show'] = ['Cleaner Details', 'Cleaner-Details ID %s anzeigen'];
