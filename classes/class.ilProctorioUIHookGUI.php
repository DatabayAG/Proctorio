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

use ILIAS\DI\Container;
use ILIAS\Plugin\Proctorio\Frontend;
use ILIAS\Plugin\Proctorio\Frontend\ViewModifier;
use ILIAS\Plugin\Proctorio\Frontend\ViewModifier\TestLaunch;
use ILIAS\Plugin\Proctorio\Frontend\ViewModifier\TestResults;
use ILIAS\Plugin\Proctorio\Frontend\ViewModifier\TestSettings;

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilProctorioUIHookGUI: ilUIPluginRouterGUI
 */
class ilProctorioUIHookGUI extends ilUIHookPluginGUI
{
    /** @var Container */
    protected $dic;
    /** @var ViewModifier[]|null */
    protected static $modifiers = null;

    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * The main entry point for own plugin controllers
     */
    public function executeCommand() : void
    {
        $this->setPluginObject(ilProctorioPlugin::getInstance());

        $nextClass = $this->dic->ctrl()->getNextClass();
        switch (strtolower($nextClass)) {
            default:
                $dispatcher = Frontend\Dispatcher::getInstance($this);
                $dispatcher->setDic($this->dic);

                $response = $dispatcher->dispatch($this->dic->ctrl()->getCmd());
                break;
        }

        $this->dic->ui()->mainTemplate()->setContent($response);
        $this->dic->ui()->mainTemplate()->printToStdout();
    }

    private function initModifiers() : void
    {
        if (!isset($this->dic['tpl'], $this->dic['ilToolbar'], $this->dic['refinery'])) {
            return;
        }

        if (null !== self::$modifiers) {
            return;
        }

        $phpSelf = (string) ($_SERVER['PHP_SELF'] ?? '');
        $urlParts = parse_url($phpSelf);
        $script = basename($phpSelf);

        $isLiveVotingRequest = (
            $phpSelf !== '' &&
            is_array($urlParts) &&
            isset($urlParts['path']) &&
            strpos($urlParts['path'], '/LiveVoting/') !== false
        );
        if ($isLiveVotingRequest) {
            return;
        }

        $isBootstrappedRequest = in_array($script, ['login.php', 'goto.php', 'ilias.php']);
        if (!$isBootstrappedRequest) {
            return;
        }

        self::$modifiers = [
            new TestSettings($this, $this->dic),
            new TestLaunch($this, $this->dic),
            new TestResults($this, $this->dic),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getHTML($a_comp, $a_part, $a_par = []) : array
    {
        $unmodified = ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];

        $this->initModifiers();

        if (is_array(self::$modifiers)) {
            foreach (self::$modifiers as $modifier) {
                if ($modifier->shouldModifyHtml($a_comp, $a_part, $a_par)) {
                    return $modifier->modifyHtml($a_comp, $a_part, $a_par);
                }
            }
        }

        return $unmodified;
    }

    /**
     * @inheritDoc
     */
    public function modifyGUI($a_comp, $a_part, $a_par = []) : void
    {
        parent::modifyGUI($a_comp, $a_part, $a_par);

        $this->initModifiers();

        if (is_array(self::$modifiers)) {
            foreach (self::$modifiers as $modifier) {
                if ($modifier->shouldModifyGUI($a_comp, $a_part, $a_par)) {
                    $modifier->modifyGUI($a_comp, $a_part, $a_par);
                }
            }
        }
    }
}
