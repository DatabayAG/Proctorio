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

namespace ILIAS\Plugin\Proctorio\Data;

use ILIAS\Data\URI;

/**
 * Class TrustedURI
 * @package ILIAS\Plugin\Proctorio\Data
 */
class TrustedURI extends URI
{
    /** @var string */
    private $uri;

    public function __construct(string $uri_string)
    {
        $this->uri = $uri_string;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
