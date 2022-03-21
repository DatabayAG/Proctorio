<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Proctorio\Refinery\Transformation;

use ILIAS\Plugin\Proctorio\Data\TrustedURI;
use ILIAS\Refinery\URI\StringTransformation;

/**
 * Class UriToString
 * @package ILIAS\Plugin\Proctorio\Refinery\Transformation
 * @author Michael Jansen <mjansen@databay.de>
 */
class UriToString extends StringTransformation
{
    /**
     * @inheritDoc
     */
    public function transform($from)
    {
        if ($from instanceof TrustedURI) {
            return $from->getUri();
        }

        return parent::transform($from);
    }
}
