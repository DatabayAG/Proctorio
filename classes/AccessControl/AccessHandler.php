<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\AccessControl;

use ilObjTest;
use ilObjUser;

/**
 * Interface AccessHandler
 * @package ILIAS\Plugin\Proctorio\AccessControl
 * @author  Michael Jansen <mjansen@databay.de>
 */
interface AccessHandler
{
    public function withActor(ilObjUser $actor) : self;

    public function mayTakeTests(ilObjTest $test) : bool;

    public function mayReadTestReviews(ilObjTest $test) : bool;

    public function mayReadTestSettings(ilObjTest $test) : bool;

    public function mayWriteTestSettings(ilObjTest $test) : bool;
}
