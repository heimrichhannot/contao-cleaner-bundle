<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\CleanerBundle\Model\CleanerModel;

class CleanerRegistry
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFramework $framework
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|static|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var CleanerModel $adapter */
        $adapter = $this->framework->getAdapter(CleanerModel::class);

        return $adapter->findBy($column, $value, $options);
    }
}
