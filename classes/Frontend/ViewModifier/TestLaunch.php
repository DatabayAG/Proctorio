<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\Frontend\ViewModifier;

use DOMDocument;
use DOMElement;
use DomXPath;
use ilInfoScreenGUI;
use ilLinkButton;
use ilObjCourseGUI;
use ilObjectFactory;
use ilObjTest;
use ilObjTestGUI;
use ilOrgUnitOperation;
use ilToolbarGUI;
use ilUIHookPluginGUI;
use ilUIPluginRouterGUI;

/**
 * Class TestLaunch
 * @package ILIAS\Plugin\Proctorio\Frontend\Controller
 * @author Michael Jansen <mjansen@databay.de>
 */
class TestLaunch extends Base
{
    private const CMD_START_TEST = 'startPlayer';
    private const CMD_RESUME_TEST = 'resumePlayer';
    
    /** @var ilObjTest */
    private $test;
    /** @var bool */
    private $reviewButtonRendered = false;

    private function isPreviewContext() : bool
    {
        return (
            $this->isCommandClass(ilObjCourseGUI::class) &&
            strtolower($this->ctrl->getCmd()) === strtolower('showItemIntro')
        );
    }

    private function isInfoScreenContext() : bool
    {
        $validInfoScreenCommands = array_map('strtolower', [
            'showNotes',
            'infoScreen',
            'showSummary',
            'activateComments',
            'deactivateComments',
            'addNote',
            'updateNote',
            'deleteNote',
            'editNoteForm',
            'confirmDelete',
            'cancelDelete',
            'listSortAsc',
            'listSortDesc',
            'saveTags',
        ]);
        
        $isBaseClassInfoScreenRequest = (
            $this->isBaseClass(ilObjTestGUI::class) &&
            in_array(strtolower($this->ctrl->getCmd()), $validInfoScreenCommands, true)
        );

        $isCmdClassInfoScreenRequest = (
            $this->isCommandClass(ilInfoScreenGUI::class) &&
            in_array(strtolower($this->ctrl->getCmd()), $validInfoScreenCommands, true)
        ) || (
            $this->isCommandClass(ilObjTestGUI::class) &&
            in_array(strtolower($this->ctrl->getCmd()), $validInfoScreenCommands, true)
        );

        $isGotoRequest = (
            preg_match('/^tst_\d+$/', (string) ($this->httpRequest->getQueryParams()['target'] ?? ''))
        );

        return $isBaseClassInfoScreenRequest || $isCmdClassInfoScreenRequest || $isGotoRequest;
    }

    private function getTestRefId() : int
    {
        $refId = $this->getPreviewRefId();
        if ($refId <= 0) {
            $refId = $this->getRefId();
        }

        if ($refId <= 0) {
            $refId = $this->getTargetRefId();
        }
        
        return $refId;
    }
    
    /**
     * @inheritDoc
     */
    public function shouldModifyHtml(string $component, string $part, array $parameters) : bool
    {
        if (!$this->isInfoScreenContext() && !$this->isPreviewContext()) {
            return false;
        }

        if ('template_get' !== $part) {
            return false;
        }
        
        if ($this->isPreviewContext()) {
            if ('Modules/Course/Intro/tpl.intro_layout.html' !== $parameters['tpl_id']) {
                return false;
            }

            if (!$this->isPreviewObjectOfType('tst')) {
                return false;
            }
        } else {
            if (
                'Services/UIComponent/Toolbar/tpl.toolbar.html' !== $parameters['tpl_id'] &&
                'Services/InfoScreen/tpl.infoscreen.html' !== $parameters['tpl_id']
            ) {
                return false;
            }

            if (!$this->isObjectOfType('tst') && !$this->isTargetObjectOfType('tst')) {
                return false;
            }
        }

        if (!$this->coreAccessHandler->checkAccess('read', '', $this->getTestRefId())) {
            return false;
        }

        $this->test = ilObjectFactory::getInstanceByRefId($this->getTestRefId());
        if (!$this->service->isTestSupported($this->test)) {
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
        $html = $parameters['html'];

        $unmodified = ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];

        if ('Services/InfoScreen/tpl.infoscreen.html' === $parameters['tpl_id']) {
            $this->addReviewButtonToToolbar();

            return $unmodified;
        }

        $doc = new DOMDocument("1.0", "utf-8");
        if (!@$doc->loadHTML('<?xml encoding="utf-8" ?><html><body>' . $html . '</body></html>')) {
            return $unmodified;
        }
        $doc->encoding = 'UTF-8';

        $this->manipulateLaunchButton($doc);
        $this->addReviewButton($doc);

        $processedHtml = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
        if ($processedHtml === '') {
            return $unmodified;
        }

        return ['mode' => ilUIHookPluginGUI::REPLACE, 'html' => $this->cleanHtmlString($processedHtml)];
    }

    private function addReviewButtonToToolbar() : void
    {
        $this->reviewButtonRendered = true;

        if (!$this->hasReviewRbacAccess() || !$this->accessHandler->mayReadTestReviews($this->test)) {
            return;
        }

        $this->ctrl->setParameterByClass(
            get_class($this->getCoreController()),
            'ref_id',
            $this->getTestRefId()
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
        $btn->setCaption(
            $this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_review'),
            false
        );
        $this->toolbar->addButtonInstance($btn);
    }

    private function hasReviewRbacAccess() : bool
    {
        return (
            $this->coreAccessHandler->checkAccess('write', '', $this->getTestRefId()) ||
            $this->coreAccessHandler->checkAccess('tst_results', '', $this->getTestRefId()) ||
            $this->coreAccessHandler->checkPositionAccess(ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS, $this->getTestRefId()) ||
            $this->coreAccessHandler->checkPositionAccess(ilOrgUnitOperation::OP_ACCESS_RESULTS, $this->getTestRefId())
        );
    }

    private function addReviewButton(DOMDocument $doc) : void
    {
        if ($this->reviewButtonRendered) {
            return;
        }
        
        if (!$this->hasReviewRbacAccess() || !$this->accessHandler->mayReadTestReviews($this->test)) {
            return;
        }

        $xpath = new DomXPath($doc);
        $toolbarButtons = $xpath->query("(//form[@id='ilToolbar'][1]//input | //form[@id='ilToolbar'][1]//a)[last()]");

        $this->ctrl->setParameterByClass(
            get_class($this->getCoreController()),
            'ref_id',
            $this->getTestRefId()
        );
        $url = $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'TestLaunchAndReview.review',
            '',
            false,
            false
        );
        
        if ($toolbarButtons->length > 0) {
            $referenceButton = $toolbarButtons->item(0);

            $btn = $doc->createElement('a');
            $btn->setAttribute('class', 'btn btn-default btn-primary');
            $btn->setAttribute('style', 'margin-left: 5px;');
            $btn->setAttribute('href', $url);

            $btnText = $doc->createTextNode($this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_review'));
            $btn->appendChild($btnText);

            $referenceButton->parentNode->insertBefore($btn, $referenceButton->nextSibling);
        } else {
            $toolbar = new ilToolbarGUI();
            $btn = ilLinkButton::getInstance();
            $btn->setUrl($url);
            $btn->setCaption($this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_review'), false);
            $toolbar->addButtonInstance($btn);
            $toolbarHtml = $toolbar->getHTML();

            if ($this->isPreviewContext()) {
                $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' item-intro-add-container ')]");
                if (1 === $nodes->length) {
                    $toolbarDoc = new DOMDocument("1.0", "utf-8");
                    if (!@$toolbarDoc->loadHTML('<?xml encoding="utf-8" ?><html><body>' . $toolbarHtml . '</body></html>')) {
                        return;
                    }
                    $toolbarDoc->encoding = 'UTF-8';
                    
                    foreach ($toolbarDoc->getElementsByTagName('body')->item(0)->childNodes as $child) {
                        $importedToolbarNode = $doc->importNode($child, true);
                        $nodes->item(0)->appendChild($importedToolbarNode);
                    }
                }
            }
        }
    }

    private function manipulateLaunchButton(DOMDocument $doc) : void
    {
        $xpath = new DomXPath($doc);
        $startPlayerCommandButton = $xpath->query("//input[contains(@name, '" . self::CMD_START_TEST . "')]");
        $resumePlayerCommandButton = $xpath->query("//input[contains(@name, '" . self::CMD_RESUME_TEST . "')]");

        if (1 === $startPlayerCommandButton->length xor 1 === $resumePlayerCommandButton->length) {
            if (1 === $startPlayerCommandButton->length) {
                $this->manipulateLaunchElement($doc, $startPlayerCommandButton->item(0));
            } elseif (1 === $resumePlayerCommandButton->length) {
                $this->manipulateLaunchElement($doc, $resumePlayerCommandButton->item(0));
            }
        }
    }

    private function manipulateLaunchElement(DOMDocument $doc, DOMElement $elm) : void
    {
        if (!$this->accessHandler->mayTakeTests($this->test)) {
            $elm->parentNode->removeChild($elm);
            return;
        }

        $this->ctrl->setParameterByClass(
            get_class($this->getCoreController()),
            'ref_id',
            $this->getTestRefId()
        );
        $url = $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'TestLaunchAndReview.launch',
            '',
            false,
            false
        );

        $btn = $doc->createElement('a');
        $btn->setAttribute('class', 'btn btn-default btn-primary');
        $btn->setAttribute('href', $url);

        $btlLabel = $this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_launch');
        if ('cmd[' . self::CMD_RESUME_TEST . ']' === $elm->getAttribute('name')) {
            $btlLabel = $this->getCoreController()->getPluginObject()->txt('btn_label_proctorio_resume');
        }
        $btnText = $doc->createTextNode($btlLabel);
        $btn->appendChild($btnText);

        $elm->parentNode->insertBefore($btn, $elm->nextSibling);
        $elm->parentNode->removeChild($elm);
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
