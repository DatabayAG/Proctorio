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

use ilAccessHandler;
use ilCtrl;
use ilErrorHandling;
use ilGlobalTemplateInterface;
use ILIAS\DI\Container;
use ILIAS\Plugin\Proctorio\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Proctorio\Frontend\HttpContext;
use ILIAS\Plugin\Proctorio\Service\Proctorio\Impl as ProctorioService;
use ILIAS\Plugin\Proctorio\Webservice\Rest\Impl;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilLogger;
use ilObjuser;
use ilProctorioUIHookGUI;
use ilToolbarGUI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Plugin\Proctorio\AccessControl\AccessHandler;
use ReflectionClass;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class Base
{
    use HttpContext;

    /** @var ilGlobalTemplateInterface */
    public $pageTemplate;
    /** @var Factory */
    protected $uiFactory;
    /** @var ilCtrl */
    protected $ctrl;
    /** @var Renderer */
    protected $uiRenderer;
    /** @var Container */
    protected $dic;
    /** @var ilToolbarGUI */
    protected $toolbar;
    /** @var ilObjuser */
    protected $user;
    /** @var ilAccessHandler */
    protected $coreAccessHandler;
    /** @var AccessHandler */
    protected $accessHandler;
    /** @var ilErrorHandling */
    protected $errorHandler;
    /** @var ilLanguage */
    public $lng;
    /** @var ilProctorioUIHookGUI */
    public $coreController;
    /** @var Settings */
    protected $globalProctorioSettings;
    /** @var Impl */
    protected $proctorioApi;
    /** @var ProctorioService */
    protected $service;
    /** @var ServerRequestInterface */
    protected $httpRequest;
    /** @var ilLogger */
    protected $log;

    final public function __construct(ilProctorioUIHookGUI $controller, Container $dic)
    {
        $this->coreController = $controller;
        $this->dic = $dic;

        $this->httpRequest = $dic->http()->request();
        $this->objectCache = $dic['ilObjDataCache'];

        $this->ctrl = $dic->ctrl();
        $this->lng = $dic->language();
        $this->pageTemplate = $dic->ui()->mainTemplate();
        $this->user = $dic->user();
        $this->uiRenderer = $dic->ui()->renderer();
        $this->uiFactory = $dic->ui()->factory();
        $this->coreAccessHandler = $dic->access();
        $this->errorHandler = $dic['ilErr'];
        $this->toolbar = $dic->toolbar();
        $this->globalProctorioSettings = $dic['plugin.proctorio.settings'];
        $this->accessHandler = $dic['plugin.proctorio.accessHandler'];
        $this->proctorioApi = $dic['plugin.proctorio.api'];
        $this->service = $dic['plugin.proctorio.service'];
        $this->log = $dic->logger()->root();

        $this->init();
    }

    protected function init() : void
    {
        if (!$this->getCoreController()->getPluginObject()->isActive()) {
            $this->errorHandler->raiseError($this->lng->txt('permission_denied'), $this->errorHandler->MESSAGE);
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    final public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this, $this->getDefaultCommand()], []);
    }

    abstract public function getDefaultCommand() : string;

    public function getCoreController() : ilProctorioUIHookGUI
    {
        return $this->coreController;
    }

    public function getDic() : Container
    {
        return $this->dic;
    }

    final public function getControllerName() : string
    {
        return (new ReflectionClass($this))->getShortName();
    }
}
