<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Event;

use Contao\Model;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeCleanEvent extends Event
{
    const NAME = 'huh.cleaner.event.before_clean';
    /**
     * @var array
     */
    protected $entity;
    /**
     * @var Model
     */
    protected $cleaner;
    /**
     * @var bool
     */
    protected $skipped;

    public function __construct(array $entity, Model $cleaner, bool $skipped = false)
    {
        $this->entity = $entity;
        $this->cleaner = $cleaner;
        $this->skipped = $skipped;
    }

    public function getEntity(): array
    {
        return $this->entity;
    }

    public function getCleaner(): Model
    {
        return $this->cleaner;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): void
    {
        $this->skipped = $skipped;
    }
}
