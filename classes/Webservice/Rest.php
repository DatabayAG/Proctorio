<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\Webservice;

use ILIAS\Data\URI;
use ilObjTest;

/**
 * Interface Rest
 * @package ILIAS\Plugin\Proctorio\Webservice
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Rest
{
    public function getLaunchUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI;

    public function getReviewUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI;
}
