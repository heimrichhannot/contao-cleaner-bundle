# Changelog

All notable changes to this project will be documented in this file.

## [2.6.4] - 2022-02-14

- Fixed: array index issues in php 8+

## [2.6.3] - 2021-08-27

- Added: php8 support

## [2.6.2] - 2021-06-22

- fixed argument error on PoorManCronController

## [2.6.1] - 2021-06-17

- fixed dep issue
PoorManCronController
## [2.6.0] - 2021-06-15

- added 2 new events:
  - `huh.cleaner.event.before_clean`
  - `huh.cleaner.event.after_clean`
- fixed counting issue
- updated code style

## [2.5.2] - 2021-06-04

- adjustments for privacy-bundle

## [2.5.1] - 2020-07-03

- added new tag for packagist

## [2.5.0] - 2020-06-29

- added optional regarding ondelete_callback of entity that will be cleaned

## [2.4.0] - 2020-02-28

- fixes for Contao 4.9

## [2.3.1] - 2019-12-17

- fixes module leftovers in composer.json
- added licence file

## [2.3.0] - 2019-12-13

- updated cleaner entity selection to show all and only database datacontainers
- updated dependencies
- some code enhancements

## [2.2.3] - 2019-03-06

### Fixed

- continue 2 issue in command for PHP 7.3

## [2.2.2] - 2019-01-22

### Fixed

- continue 2 issue in command

## [2.2.1] - 2018-10-26

### Changed

- sql for `dependentField`

## [2.2.0] - 2018-10-25

### Added

- new cleaner type for dependent entities

## [2.1.4] - 2018-08-06

### Fixed

- settings palette

## [2.1.3] - 2018-06-26

### Fixed

- switched deletion of entities to database -> there's not always a model for a db table

## [2.1.2] - 2018-06-26

### Fixed

- `PoorMansCron` implementation fixed

## [2.1.1] - 2018-06-25

### Fixed

- command name

### Added

- support for heimrichhannot/contao-privacy

## [2.1.0] - 2018-06-22

### Fixed

- restored cleaning functionality, 2.0.0 was not working
- refactored command

## [2.0.0] - 2018-03-12

### Changed

- `heimrichhanoot/contao-utils-bundle`: "^2.0"

## [1.0.1] - 2018-03-08

### Fixed

- travis.yml

### Changed

- refactored extension class
