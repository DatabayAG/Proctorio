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

namespace ILIAS\Plugin\Proctorio\Frontend\ViewModifier;

use ilMarkSchemaGUI;
use ilObjectFactory;
use ilObjTestGUI;
use ilObjTestSettingsGeneralGUI;
use ilObjTestSettingsScoringResultsGUI;
use ilRepositoryGUI;
use ilTabsGUI;
use ilUIPluginRouterGUI;

/**
 * Class TestSettings
 * @package ILIAS\Plugin\Proctorio\Frontend\Controller
 * @author Michael Jansen <mjansen@databay.de>
 */
class TestSettings extends Base
{
    /**
     * @inheritDoc
     */
    public function shouldModifyHtml(string $component, string $part, array $parameters) : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function shouldModifyGUI(string $component, string $part, array $parameters) : bool
    {
        if ('sub_tabs' !== $part) {
            return false;
        }

        if (
            !$this->isCommandClass(ilObjTestSettingsGeneralGUI::class) &&
            !$this->isCommandClass(ilMarkSchemaGUI::class) &&
            !$this->isCommandClass(ilObjTestSettingsScoringResultsGUI::class) &&
            !(
                $this->isCommandClass(ilObjTestGUI::class) &&
                in_array($this->ctrl->getCmd(), ['defaults', 'addDefaults', 'deleteDefaults', 'applyDefaults'])
            ) &&
            !(
                $this->isCommandClass(get_class($this->getCoreController())) &&
                strpos($this->ctrl->getCmd(), $this->getClassName()) !== false
            )
        ) {
            return false;
        }

        if (!$this->isObjectOfType('tst')) {
            return false;
        }

        if (!$this->coreAccessHandler->checkAccess('write', '', $this->getRefId())) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function modifyGUI(string $component, string $part, array $parameters) : void
    {
        /** @var ilTabsGUI $tabs */
        $tabs = $parameters['tabs'];

        if (
            $this->isCommandClass(get_class($this->getCoreController())) &&
            strpos($this->ctrl->getCmd(), $this->getClassName()) !== false
        ) {
            $this->ctrl->setParameterByClass(ilObjTestSettingsGeneralGUI::class, 'ref_id', $this->getRefId());
            $tstSettingsUrl = $this->ctrl->getLinkTargetByClass(
                [ilRepositoryGUI::class, ilObjTestGUI::class, ilObjTestSettingsGeneralGUI::class],
                '',
                '',
                false,
                false
            );
            $tabs->setBackTarget($this->lng->txt('back'), $tstSettingsUrl);
        } else {
            $test = ilObjectFactory::getInstanceByRefId($this->getRefId());
            if ($this->service->isTestSupported($test) && $this->accessHandler->mayReadTestSettings($test)) {
                $this->ctrl->setParameterByClass(get_class($this->getCoreController()), 'ref_id', $this->getRefId());
                $tabs->addSubTabTarget(
                    $this->getCoreController()->getPluginObject()->getPrefix() . '_exam_tab_proctorio',
                    $this->ctrl->getLinkTargetByClass(
                        [ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                        'TestSettings.showSettingsCmd'
                    )
                );
            }
        }
    }
}
