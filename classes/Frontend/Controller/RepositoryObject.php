<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\Frontend\Controller;

use ReflectionMethod;

/**
 * Class Course
 * @package ILIAS\Plugin\Proctorio\Frontend\Controller
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class RepositoryObject extends Base
{
    abstract public function getObjectGuiClass() : string;

    protected function drawHeader() : void
    {
        $class = $this->getObjectGuiClass();
        $object = new $class();

        $reflectionMethod = new ReflectionMethod($class, 'setTitleAndDescription');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($object);

        $this->dic['ilLocator']->addRepositoryItems($this->getRefId());
        $this->pageTemplate->setLocator();
    }
}
