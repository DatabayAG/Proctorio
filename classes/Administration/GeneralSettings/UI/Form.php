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

namespace ILIAS\Plugin\Proctorio\Administration\GeneralSettings\UI;

use ilException;
use ilFormPropertyGUI;
use ilFormSectionHeaderGUI;
use ILIAS\Plugin\Proctorio\UI\Form\Bindable;
use ILIAS\Plugin\Proctorio\AccessControl\Acl;
use ilMultiSelectInputGUI;
use ilObjectDataCache;
use ilProctorioPlugin;
use ilPropertyFormGUI;
use ilRbacReview;
use ilTextInputGUI;
use ilUtil;

/**
 * Class Form
 * @package ILIAS\Plugin\Proctorio\Administration\GeneralSettings\UI
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Form extends ilPropertyFormGUI
{
    /** @var ilProctorioPlugin */
    private $plugin;
    /** @var object */
    private $cmdObject;
    /** @var Bindable */
    private $generalSettings;
    /** @var ilObjectDataCache */
    private $objectCache;
    /** @var ilRbacReview */
    protected $rbacReview;
    /** @var Acl */
    private $acl;

    public function __construct(
        ilProctorioPlugin $plugin,
        $cmdObject,
        Bindable $generalSettings,
        ilObjectDataCache $objectCache,
        ilRbacReview $rbacReview,
        Acl $acl
    ) {
        $this->plugin = $plugin;
        $this->cmdObject = $cmdObject;
        $this->generalSettings = $generalSettings;
        $this->objectCache = $objectCache;
        $this->rbacReview = $rbacReview;
        $this->acl = $acl;
        parent::__construct();

        $this->initForm();
    }

    protected function initForm() : void
    {
        $this->addCommandButton('saveSettings', $this->lng->txt('save'));
        $this->setFormAction($this->ctrl->getFormAction($this->cmdObject, 'saveSettings'));
        $this->setTitle($this->lng->txt('settings'));

        $apiKey = new ilTextInputGUI(
            $this->plugin->txt('api_key'),
            'api_key'
        );
        $apiKey->setInfo($this->plugin->txt('api_key_info'));
        $apiKey->setRequired(true);
        $this->addItem($apiKey);

        $apiSecret = new ilTextInputGUI(
            $this->plugin->txt('api_secret'),
            'api_secret'
        );
        $apiSecret->setInfo($this->plugin->txt('api_secret_info'));
        $apiSecret->setRequired(true);
        $this->addItem($apiSecret);

        $apiAccountRegion = new ilTextInputGUI(
            $this->plugin->txt('api_region'),
            'api_region'
        );
        $apiAccountRegion->setInfo($this->plugin->txt('api_region_info'));
        $apiAccountRegion->setRequired(true);
        $this->addItem($apiAccountRegion);

        $apiBaseUrl = new ilTextInputGUI(
            $this->plugin->txt('api_base_url'),
            'api_base_url'
        );
        $apiBaseUrl->setInfo($this->plugin->txt('api_base_url_info'));
        $apiBaseUrl->setRequired(true);
        $this->addItem($apiBaseUrl);

        $apiLaunchReviewEndpoint = new ilTextInputGUI(
            $this->plugin->txt('api_launch_review_endpoint'),
            'api_launch_review_endpoint'
        );
        $apiLaunchReviewEndpoint->setInfo($this->plugin->txt('api_launch_review_endpoint_info'));
        $apiLaunchReviewEndpoint->setRequired(true);
        $apiLaunchReviewEndpoint->setValidationRegexp('/^(\/([\.A-Za-z0-9_-]+|\[[A-Za-z0-9_-]+\]))+$/');
        $this->addItem($apiLaunchReviewEndpoint);

        $accessControlSection = new ilFormSectionHeaderGUI();
        $accessControlSection->setTitle($this->plugin->txt('header_access_control'));
        $this->addItem($accessControlSection);

        $roles = [];
        foreach ($this->rbacReview->getGlobalRoles() as $roleId) {
            $roleId = (int) $roleId;
            if ($roleId !== (int) ANONYMOUS_ROLE_ID) {
                $roles[$roleId] = $this->objectCache->lookupTitle($roleId);
            }
        }
        asort($roles);

        foreach ($this->acl->getRoles() as $role) {
            $roleMapping = new ilMultiSelectInputGUI(
                $this->plugin->txt('acl_role_' . $role->getRoleId()),
                'role_mapping_' . $role->getRoleId()
            );
            $roleMapping->setInfo($this->plugin->txt('acl_role_info_' . $role->getRoleId()));
            $roleMapping->setOptions($roles);
            $this->addItem($roleMapping);
        }

        $this->setValuesByArray($this->generalSettings->toArray());
    }

    /**
     * @inheritDoc
     */
    public function checkInput() : bool
    {
        $bool = parent::checkInput();
        if (!$bool) {
            return $bool;
        }

        $requirePlaceholdersDefinition = [
            'api_base_url' => [
                'ACCOUNT_REGION',
            ],
        ];

        $valid = true;
        foreach ($requirePlaceholdersDefinition as $formFieldId => $placeholders) {
            $errors = [];

            foreach ($placeholders as $placeholder) {
                $position = stripos($this->getInput($formFieldId), '[' . strtoupper($placeholder) . ']');
                if (false === $position) {
                    $valid = false;
                    $errors[] = $this->lng->txt('api_err_' . strtolower($placeholder) . '_missing');
                }
            }

            $formElement = $this->getItemByPostVar($formFieldId);
            if (count($errors) > 0 && $formElement instanceof ilFormPropertyGUI) {
                $formElement->setAlert(implode(' ', $errors));
            }
        }

        if (!$valid) {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        }

        return $valid;
    }

    public function saveObject() : bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        try {
            $this->generalSettings->bindForm($this);
            $this->generalSettings->onFormSaved();
            return true;
        } catch (ilException $e) {
            ilUtil::sendFailure($this->plugin->txt($e->getMessage()));
            $this->setValuesByPost();
            return false;
        }
    }

    protected function fillObject() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $success = true;

        try {
            $this->setValuesByArray(
                $this->generalSettings->toArray()
            );
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage());
            $success = false;
        }

        return $success;
    }
}
