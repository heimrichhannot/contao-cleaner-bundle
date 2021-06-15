<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class AfterCleanEvent extends Event
{
    const NAME = 'huh.cleaner.event.after_clean';
    /**
     * @var array
     */
    protected $entity;
    /**
     * @var Model
     */
    protected $cleaner;

    public function __construct(array $entity, Model $cleaner)
    {
        $this->entity = $entity;
        $this->cleaner = $cleaner;
    }

    public function getEntity(): array
    {
        return $this->entity;
    }

    public function getCleaner(): Model
    {
        return $this->cleaner;
    }
}
