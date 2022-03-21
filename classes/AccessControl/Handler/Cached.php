<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\AccessControl\Handler;

use ILIAS\Plugin\Proctorio\AccessControl;
use ilObjTest;
use ilObjUser;

/**
 * Class Cached
 * @package ILIAS\Plugin\Proctorio\AccessControl\Handler
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Cached implements AccessControl\AccessHandler
{
    /** @var AccessControl\AccessHandler */
    private $origin;
    /** @var array */
    private $cache = [];

    public function __construct(AccessControl\AccessHandler $origin)
    {
        $this->origin = $origin;
    }

    public function withActor(ilObjUser $actor) : AccessControl\AccessHandler
    {
        $clone = clone $this;
        $clone->origin = $clone->origin->withActor($actor);
        $clone->cache = [];

        return $clone;
    }

    public function mayTakeTests(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayTakeTests($test));
    }

    public function mayReadTestReviews(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayReadTestReviews($test));
    }

    public function mayReadTestSettings(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayReadTestSettings($test));
    }

    public function mayWriteTestSettings(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayWriteTestSettings($test));
    }
}
