<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

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
