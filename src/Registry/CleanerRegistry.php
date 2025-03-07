<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Collection;
use HeimrichHannot\CleanerBundle\Model\CleanerModel;

class CleanerRegistry
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return Collection|static|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var CleanerModel $adapter */
        $adapter = $this->framework->getAdapter(CleanerModel::class);

        return $adapter->findBy($column, $value, $options);
    }
}
