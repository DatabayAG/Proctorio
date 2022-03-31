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
