<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$arrLang = &$GLOBALS['TL_LANG']['tl_cleaner'];

/*
 * Fields
 */
$arrLang['title'] = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$arrLang['description'] = ['Beschreibung', 'Geben Sie hier bitte eine Beschreibung des Cleaners ein.'];
$arrLang['type'] = ['Typ', 'Wählen Sie hier einen Typ aus.'];
$arrLang['tstamp'] = ['Änderungsdatum', ''];
$arrLang['dataContainer'] = ['Entität', 'Wählen Sie hier die Datenbanktabelle der gewünschten Entität aus, die gelöscht werden soll.'];
$arrLang['whereCondition'] = ['Bedingung für das Löschen (!)', 'Geben Sie hier die Bedingung ein, die erfüllt sein muss, damit eine Entität gelöscht wird. Aus Sicherheitsgründen ist dieses Feld ein Pflichtfeld. Wenn Sie keine Bedingung vergeben möchten, geben Sie einfach 1=1 ein.'];
$arrLang['addMaxAge'] = ['Maximales Alter hinzufügen', 'Wählen Sie diese Option, wenn ptoentiell zu löschende Entitäten ein bestimmtes Maximalalter haben dürfen, das sie vor dem Löschen schützt.'];
$arrLang['maxAge'] = ['Maximales Alter inaktiver Datensätze', 'Wählen Sie hier aus, wie alt eine inaktive Datensatz höchstens sein darf, bevor sie gelöscht wird.'];
$arrLang['maxAge']['m'] = 'Minute(n)';
$arrLang['maxAge']['h'] = 'Stunde(n)';
$arrLang['maxAge']['d'] = 'Tag(e)';
$arrLang['maxAgeField'] = ['Feld für das Datensatzalter', 'Geben Sie hier den Namen des Feldes ein, welches für die Berechnung des maximalen Alters herangezogen werden soll.'];
$arrLang['period'] = ['Zeitintervall', 'Wählen Sie hier, wie oft die Säuberung ausgeführt werden soll. Dabei wird Contaos Poor Man\'s Cron (TL_CRON) verwendet.'];
$arrLang['period']['minutely'] = 'Jede Minute';
$arrLang['period']['hourly'] = 'Jede Stunde';
$arrLang['period']['daily'] = 'Jeden Tag';
$arrLang['period']['weekly'] = 'Jede Woche';
$arrLang['period']['monthly'] = 'Jeden Monat';
$arrLang['published'] = ['Aktiviert', 'Wählen Sie diese Option, um den Cleaner zu aktivieren.'];
$arrLang['fileDirRetrievalMode'] = ['Zu entfernende Dateien & Verzeichnisse ermitteln durch', 'Wählen Sie hier aus, wie die zu entfernenden Dateien & Verzeichnisse ermittelt werden sollen.'];
$arrLang['entityFields'] = ['Felder', 'Wählen Sie hier die Felder aus, in denen eine Referenz zu einer oder mehreren Dateien bzw. Verzeichnissen gespeichert ist.'];
$arrLang['directory'] = ['Zu leerendes Verzeichnis', 'Wählen Sie hier das relevante zu leerende Verzeichnis aus.'];
$arrLang['addGitKeepAfterClean'] = ['.gitkeep nach dem Leeren erzeugen', 'Wählen Sie diese Option, wenn Sie nach dem Leeren des Verzeichnisses in diesem eine .gitkeep-Datei erzeugen wollen. Sinnvoll, wenn das Verzeichnis auch "leer" in ein git-Repository eingecheckt werden soll.'];
$arrLang['dependentTable'] = ['abhängige Tabelle', 'Wählen Sie hier die Tabelle die die Entität beinhaltet von welcher die Löschung abhängt.'];
$arrLang['dependentField'] = ['Feld', 'Wählen Sie hier das Feld aus über das die zu löschende Entität mit der abhängigen Entität verbunden ist.'];
$arrLang['useEntityOnDeleteCallback'] = ['ondelete_callback der Entität nutzen', 'Wählen Sie diese Option, wenn der ondelete_callback der Entität beim Löschen ausgeführt werden soll.'];

/*
 * Legends
 */
$arrLang['general_legend'] = 'Allgemeine Einstellungen';
$arrLang['config_legend'] = 'Konfiguration';
$arrLang['publish_legend'] = 'Aktivierung';

/*
 * Reference
 */
$arrLang['reference'] = [
    \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_ENTITY => 'Entität',
    \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_DEPENDENT_ENTITY => 'abhängige Entität',
    \HeimrichHannot\CleanerBundle\Command\CleanerCommand::TYPE_FILE => 'Datei',
    \HeimrichHannot\CleanerBundle\Command\CleanerCommand::FILEDIR_RETRIEVAL_MODE_ENTITY_FIELDS => 'Felder von Entitäten',
    \HeimrichHannot\CleanerBundle\Command\CleanerCommand::FILEDIR_RETRIEVAL_MODE_DIRECTORY => 'Verzeichnis',
];

/*
 * Buttons
 */
$arrLang['new'] = ['Neuer Cleaner', 'Cleaner erstellen'];
$arrLang['edit'] = ['Cleaner bearbeiten', 'Cleaner ID %s bearbeiten'];
$arrLang['copy'] = ['Cleaner duplizieren', 'Cleaner ID %s duplizieren'];
$arrLang['delete'] = ['Cleaner löschen', 'Cleaner ID %s löschen'];
$arrLang['show'] = ['Cleaner Details', 'Cleaner-Details ID %s anzeigen'];
