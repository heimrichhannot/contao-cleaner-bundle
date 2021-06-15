![](https://img.shields.io/packagist/v/heimrichhannot/contao-cleaner-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-cleaner-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-cleaner-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-cleaner-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-cleaner-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-cleaner-bundle)

# Cleaner Bundle

This bundle adds cleaner functionality for periodically removing arbitrary entities and/or files fulfilling a certain
condition (using poor man's cron or your server's cron).

![alt Archive](docs/screenshot.png)

*Cleaner configuration*

## Install

1. Install via composer (`composer require heimrichhannot/contao-cleaner-bundle`) or contao manager
1. Update your database

## Events

Event                                              | Class | Description
-------------------------------------------------- | ----- | -----------
huh.cleaner.event.before_clean                     | BeforeCleanEvent | Run before the entity is cleaned. Use `setSkipped()` to skip the cleaning of the passed in entity.
huh.cleaner.event.after_clean                     | AfterCleanEvent | Run after the entity is cleaned.
