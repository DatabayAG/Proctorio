<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\UI\Form;

use ilPropertyFormGUI;

/**
 * Interface Bindable
 * @package ILIAS\Plugin\Proctorio\UI\Form
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Bindable
{
    public function bindForm(ilPropertyFormGUI $form) : void;

    public function onFormSaved() : void;

    /**
     * A key value map of form values mapped to the respective element name
     * @return array<string, mixed>
     */
    public function toArray() : array;
}
