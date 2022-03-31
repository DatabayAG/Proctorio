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

namespace ILIAS\Plugin\Proctorio\Frontend\Form;

use ilCheckboxInputGUI;
use ilFormSectionHeaderGUI;
use ILIAS\Plugin\Proctorio\Frontend\Controller\Base;
use ILIAS\Plugin\Proctorio\Service\Proctorio\Impl as ProctorioService;
use ILIAS\Plugin\Proctorio\UI\Form\Bindable;
use ilObjTest;
use ilProctorioPlugin;
use ilPropertyFormGUI;

/**
 * Class TestSettings
 * @package ILIAS\Plugin\Proctorio\Frontend\Form
 * @author Michael Jansen <mjansen@databay.de>
 */
class TestSettings extends ilPropertyFormGUI
{
    /** @var ilProctorioPlugin */
    private $plugin;
    /** @var Base */
    private $controller;
    /** @var object */
    private $cmdObject;
    /** @var bool */
    protected $isReadOnly = false;
    /** @var ilObjTest */
    protected $test;

    public function __construct(
        ilProctorioPlugin $plugin,
        Base $controller,
        $cmdObject,
        bool $isReadOnly,
        ilObjTest $test
    ) {
        $this->plugin = $plugin;
        $this->controller = $controller;
        $this->cmdObject = $cmdObject;
        $this->isReadOnly = $isReadOnly;
        $this->test = $test;
        parent::__construct();

        $this->initForm();
    }

    /**
     * @inheritDoc
     */
    public function addCommandButton($a_cmd, $a_text, $a_id = "") : void
    {
        if (!$this->isReadOnly) {
            parent::addCommandButton($a_cmd, $a_text, $a_id);
        }
    }

    protected function initForm() : void
    {
        $this->setTitle($this->plugin->txt('form_header_settings'));
        $this->setDescription($this->plugin->txt('exam_settings_info_test_started'));

        $activationStatus = new ilCheckboxInputGUI(
            $this->plugin->txt('exam_setting_label_status'),
            'status'
        );
        $activationStatus->setInfo($this->plugin->txt('exam_setting_label_status_info'));
        $activationStatus->setValue('1');
        $activationStatus->setDisabled($this->isReadOnly);
        $this->addItem($activationStatus);

        $examSettingsHeader = new ilFormSectionHeaderGUI();
        $examSettingsHeader->setTitle($this->plugin->txt('form_header_exam_settings'));
        $this->addItem($examSettingsHeader);

        $examSettings = new ExamSettingsInput(
            $this->plugin,
            '',
            'exam_settings'
        );
        $examSettings->setDisabled($this->isReadOnly);
        $this->addItem($examSettings);

        $this->controller->lng->toJSMap($examSettings->getClientLanguageMapping());
        $this->controller->pageTemplate->addOnLoadCode($examSettings->getOnloadCode());
    }
}
