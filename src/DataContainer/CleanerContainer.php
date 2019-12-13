<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\CleanerBundle\DataContainer;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CleanerContainer
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * CleanerContainer constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * options_callback for tl_cleaner.fields.dataContainer.
     *
     * @return array
     */
    public function onDataContainerOptionsCallback()
    {
        return $this->container->get('huh.utils.dca')->getDataContainers(['onlyTableType' => true]);
    }
}
