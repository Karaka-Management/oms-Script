<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Script\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Script\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Helper status.
 *
 * @package Modules\Script\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class TemplateDataType extends Enum
{
    public const OTHER = 1;

    public const GLOBAL_DB = 2;

    public const GLOBAL_FILE = 3;
}
