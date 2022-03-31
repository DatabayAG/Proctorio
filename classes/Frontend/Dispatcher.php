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

namespace ILIAS\Plugin\Proctorio\Frontend;

use ILIAS\DI\Container;
use ILIAS\Plugin\Proctorio\Frontend\Controller\Base;
use ilProctorioUIHookGUI;

/**
 * Class Dispatcher
 * @package ILIAS\Plugin\Proctorio\Frontend
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Dispatcher
{
    /** @var self */
    private static $instance = null;
    /** @var ilProctorioUIHookGUI */
    private $coreController;
    /** @var string */
    private $defaultController = '';
    /** @var Container */
    private $dic;

    private function __clone()
    {
    }

    private function __construct(ilProctorioUIHookGUI $baseController, string $defaultController = '')
    {
        $this->coreController = $baseController;
        $this->defaultController = $defaultController;
    }

    public function setDic(Container $dic) : void
    {
        $this->dic = $dic;
    }

    public static function getInstance(ilProctorioUIHookGUI $baseController) : self
    {
        if (self::$instance === null) {
            self::$instance = new self($baseController);
        }

        return self::$instance;
    }

    public function dispatch(string $cmd) : string
    {
        $controller = $this->getController($cmd);
        $command = $this->getCommand($cmd);
        $controller = $this->instantiateController($controller);

        return $controller->$command();
    }

    protected function getController(string $cmd) : string
    {
        $parts = explode('.', $cmd);

        if (count($parts) >= 1) {
            return $parts[0];
        }

        return $this->defaultController ?: 'Error';
    }

    protected function getCommand(string $cmd) : string
    {
        $parts = explode('.', $cmd);

        if (count($parts) === 2) {
            $cmd = $parts[1];

            return $cmd . 'Cmd';
        }

        return '';
    }

    /**
     * @param string $controller
     * @return Base
     */
    protected function instantiateController(string $controller) : Base
    {
        $class = "ILIAS\\Plugin\\Proctorio\\Frontend\\Controller\\$controller";

        return new $class($this->getCoreController(), $this->dic);
    }

    protected function getControllerPath() : string
    {
        $path = $this->getCoreController()->getPluginObject()->getDirectory() .
            DIRECTORY_SEPARATOR .
            'classes' .
            DIRECTORY_SEPARATOR .
            'Frontend' .
            DIRECTORY_SEPARATOR .
            'Controller' .
            DIRECTORY_SEPARATOR;

        return $path;
    }

    public function getCoreController() : ilProctorioUIHookGUI
    {
        return $this->coreController;
    }

    public function setCoreController(ilProctorioUIHookGUI $coreController) : void
    {
        $this->coreController = $coreController;
    }
}
