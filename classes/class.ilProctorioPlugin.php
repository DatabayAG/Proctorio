<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Plugin\Proctorio\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Proctorio\Webservice\Rest;
use ILIAS\Plugin\Proctorio\Webservice\Rest\Impl;
use ILIAS\Plugin\Proctorio\AccessControl\Acl\Impl as Acl;
use ILIAS\Plugin\Proctorio\AccessControl\Acl\Resource\GenericResource;
use ILIAS\Plugin\Proctorio\AccessControl\Acl\Role\GenericRole;
use ILIAS\Plugin\Proctorio\AccessControl\Acl\Role\Registry;
use ILIAS\Plugin\Proctorio\AccessControl\Handler\Cached;
use ILIAS\Plugin\Proctorio\AccessControl\Handler\RoleBased;

/**
 * Class ilProctorioPlugin
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilProctorioPlugin extends ilUserInterfaceHookPlugin
{
    private const CTYPE = 'Services';
    private const CNAME = 'UIComponent';
    private  const SLOT_ID = 'uihk';
    private  const PNAME = 'Proctorio';

    /** @var self */
    private static $instance = null;
    /** @var bool */
    protected static $initialized = false;
    /** @var array<string, array<string, array<string, bool>>> */
    protected static $activePluginsCheckCache = [];
    /** @var array<string, array<string, array<string, ilPlugin>>> */
    protected static $activePluginsCache = [];

    /**
     * @inheritDoc
     */
    public function getPluginName() : string
    {
        return self::PNAME;
    }

    /**
     * @inheritDoc
     */
    protected function init() : void
    {
        parent::init();
        $this->registerAutoloader();

        if (!self::$initialized) {
            self::$initialized = true;

            $GLOBALS['DIC']['plugin.proctorio.settings'] = function (Container $c) : Settings {
                return new Settings(
                    new ilSetting($this->getId()),
                    $c['plugin.proctorio.acl']
                );
            };

            $GLOBALS['DIC']['plugin.proctorio.accessHandler'] = static function (Container $c) : Cached {
                return new Cached(
                    new RoleBased(
                        $c->user(),
                        $c['plugin.proctorio.settings'],
                        $c->rbac()->review(),
                        $c['plugin.proctorio.acl']
                    )
                );
            };

            $GLOBALS['DIC']['plugin.proctorio.acl'] = static function (Container $c) : \ILIAS\Plugin\Proctorio\AccessControl\Acl {
                $acl = new Acl(new Registry());

                $acl
                    ->addRole(new GenericRole('manager'))
                    ->addRole(new GenericRole('reviewer'))
                    ->addResource(new GenericResource('exam_review'))
                    ->addResource(new GenericResource('exam_settings'))
                    ->allow('reviewer', 'exam_review', 'read')
                    ->allow('manager', 'exam_review', 'read')
                    ->allow('manager', 'exam_settings', 'read')
                    ->allow('manager', 'exam_settings', 'write');

                return $acl;
            };

            $GLOBALS['DIC']['plugin.proctorio.api'] = static function (Container $c) : Rest {
                return new Impl(
                    $c['plugin.proctorio.service'],
                    $c['plugin.proctorio.settings'],
                    $c->logger()->root()
                );
            };

            $GLOBALS['DIC']['plugin.proctorio.service'] = static function (Container $c) : \ILIAS\Plugin\Proctorio\Service\Proctorio\Impl {
                return new \ILIAS\Plugin\Proctorio\Service\Proctorio\Impl(
                    $c->user(),
                    $c['plugin.proctorio.settings']
                );
            };
        }
    }

    /**
     * @inheritDoc
     */
    protected function afterUninstall() : void
    {
        parent::afterUninstall();

        $settings = new ilSetting($this->getId());
        $settings->deleteAll();
    }

    public function registerAutoloader() : void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        return self::$instance ?? (self::$instance = ilPluginAdmin::getPluginObject(
            self::CTYPE,
            self::CNAME,
            self::SLOT_ID,
            self::PNAME
        ));
    }

    /**
     * @param string $component
     * @param string $slot
     * @param string $pluginClass
     * @return bool
     */
    public function isPluginInstalled(string $component, string $slot, string $pluginClass) : bool
    {
        if (isset(self::$activePluginsCheckCache[$component][$slot][$pluginClass])) {
            return self::$activePluginsCheckCache[$component][$slot][$pluginClass];
        }

        foreach (
            $GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(IL_COMP_SERVICE, $component, $slot) as $plugin_name
        ) {
            $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, $component, $slot, $plugin_name);
            if (class_exists($pluginClass) && $plugin instanceof $pluginClass) {
                return (self::$activePluginsCheckCache[$component][$slot][$pluginClass] = true);
            }
        }

        return (self::$activePluginsCheckCache[$component][$slot][$pluginClass] = false);
    }

    /**
     * @param string $component
     * @param string $slot
     * @param string $pluginClass
     * @return ilPlugin
     * @throws ilException
     */
    public function getPlugin(string $component, string $slot, string $pluginClass) : ilPlugin
    {
        if (isset(self::$activePluginsCache[$component][$slot][$pluginClass])) {
            return self::$activePluginsCache[$component][$slot][$pluginClass];
        }

        foreach (
            $GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(IL_COMP_SERVICE, $component, $slot) as $plugin_name
        ) {
            $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, $component, $slot, $plugin_name);
            if (class_exists($pluginClass) && $plugin instanceof $pluginClass) {
                return (self::$activePluginsCache[$component][$slot][$pluginClass] = $plugin);
            }
        }

        throw new ilException($pluginClass . ' plugin not installed!');
    }
}
