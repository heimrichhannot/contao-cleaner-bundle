<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\CleanerBundle\Model\CleanerModel;

class CleanerRegistry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|null|static
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var CleanerModel $adapter */
        $adapter = $this->framework->getAdapter(CleanerModel::class);

        return $adapter->findBy($column, $value, $options);
    }
}
