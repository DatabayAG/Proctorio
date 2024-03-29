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

use ilLinkButton;
use ilObjectFactory;
use ilObjTest;
use ilParticipantsTestResultsGUI;
use ilUIHookPluginGUI;
use ilUIPluginRouterGUI;

/**
 * Class TestResults
 * @package ILIAS\Plugin\Proctorio\Frontend\Controller
 * @author Michael Jansen <mjansen@databay.de>
 */
class TestResults extends Base
{
    /** @var ilObjTest */
    private $test;

    private function isResultContext() : bool
    {
        return $this->isCommandClass(ilParticipantsTestResultsGUI::class);
    }

    /**
     * @inheritDoc
     */
    public function shouldModifyHtml(string $component, string $part, array $parameters) : bool
    {
        if ('template_get' !== $part) {
            return false;
        }

        if ('Services/Table/tpl.table2.html' !== $parameters['tpl_id']) {
            return false;
        }

        if (!$this->isObjectOfType('tst')) {
            return false;
        }

        if (!$this->isResultContext()) {
            return false;
        }

        $this->test = ilObjectFactory::getInstanceByRefId($this->getRefId());
        if (!$this->service->isTestSupported($this->test)) {
            return false;
        }

        // We do not check any RBAC permissions here, since this is already done by the ILIAS core when rendering this view
        if (!$this->accessHandler->mayReadTestReviews($this->test)) {
            return false;
        }

        if (!$this->service->getConfigurationForTest($this->test)['status']) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        $unmodified = ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];

        $this->ctrl->setParameterByClass(
            get_class($this->getCoreController()),
            'ref_id',
            $this->getRefId()
        );
        $url = $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'TestLaunchAndReview.review',
            '',
            false,
            false
        );
        $btn = ilLinkButton::getInstance();
        $btn->setUrl($url);
        $btn->setCaption($this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_review'), false);
        $this->toolbar->addButtonInstance($btn);

        return $unmodified;
    }

    /**
     * @inheritDoc
     */
    public function shouldModifyGUI(string $component, string $part, array $parameters) : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function modifyGUI(string $component, string $part, array $parameters) : void
    {
    }
}
