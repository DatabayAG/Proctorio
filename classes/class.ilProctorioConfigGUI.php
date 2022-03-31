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

use ILIAS\Plugin\Proctorio\Administration\Controller;
use ILIAS\Plugin\Proctorio\Administration\GeneralSettings\UI\Form;
use ILIAS\Plugin\Proctorio\AccessControl\Acl;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilProctorioConfigGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilProctorioConfigGUI extends Controller\Base
{
    /** @var Acl */
    private $acl;

    /**
     * @inheritDoc
     */
    public function performCommand($cmd) : void
    {
        $this->acl = $GLOBALS['DIC']['plugin.proctorio.acl'];
        parent::performCommand($cmd);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultCommand() : string
    {
        return 'showSettings';
    }

    public function showSettings() : void
    {
        $form = new Form(
            $this->plugin_object,
            $this,
            $this->settings,
            $this->objectCache,
            $this->rbacReview,
            $this->acl
        );
        $this->pageTemplate->setContent($form->getHTML());
    }

    public function saveSettings() : void
    {
        $form = new Form(
            $this->plugin_object,
            $this,
            $this->settings,
            $this->objectCache,
            $this->rbacReview,
            $this->acl
        );
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this);
        }

        $this->pageTemplate->setContent($form->getHTML());
    }
}
