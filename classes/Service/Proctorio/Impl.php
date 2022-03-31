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

namespace ILIAS\Plugin\Proctorio\Service\Proctorio;

use ILIAS\Plugin\Proctorio\Administration\GeneralSettings\Settings;
use ilObjTest;
use ilObjUser;
use ilPropertyFormGUI;

/**
 * Class Impl
 * @package ILIAS\Plugin\Proctorio\Service/Proctorio
 * @author Michael Jansen <mjansen@databay.de>
 */
class Impl
{
    /** @var ilObjUser */
    private $actor;
    /** @var Settings */
    private $globalSettings;

    public function __construct(ilObjUser $actor, Settings $globalSettings)
    {
        $this->actor = $actor;
        $this->globalSettings = $globalSettings;
    }

    public function getActor() : ilObjUser
    {
        return $this->actor;
    }

    public function isTestSupported(ilObjTest $test) : bool
    {
        return $test->isRandomTest() || $test->isFixedTest();
    }

    public function isConfigurationChangeAllowed(ilObjTest $test) : bool
    {
        return !$test->participantDataExist();
    }

    private function getTestSettingsPrefix(ilObjTest $test) : string
    {
        return 'tst_set_' . $test->getId();
    }

    /**
     * @param ilObjTest $test
     * @return array<string, mixed>
     */
    public function getConfigurationForTest(ilObjTest $test) : array
    {
        return [
            'status' => $this->globalSettings->getSettings()->get(
                $this->getTestSettingsPrefix($test) . '_status',
                false
            ),
            'exam_settings' => array_filter(explode(
                ',',
                $this->globalSettings->getSettings()->get($this->getTestSettingsPrefix($test) . '_exam_settings', '')
            )),
        ];
    }

    /**
     * @param ilObjTest $test
     * @param ilPropertyFormGUI $form
     */
    public function saveConfigurationForTest(ilObjTest $test, ilPropertyFormGUI $form) : void
    {
        $this->globalSettings->getSettings()->set(
            $this->getTestSettingsPrefix($test) . '_status',
            (int) $form->getInput('status')
        );
        $this->globalSettings->getSettings()->set($this->getTestSettingsPrefix($test) . '_exam_settings', implode(
            ',',
            (array) $form->getInput('exam_settings')
        ));
    }
}
