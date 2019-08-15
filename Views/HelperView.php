<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package    Modules\Helper\Views
 * @copyright  Dennis Eichhorn
 * @license    OMS License 1.0
 * @version    1.0.0
 * @link       https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Helper\Views;

use phpOMS\Views\View;

/**
 * Helper view.
 *
 * @package    Modules\Helper\Views
 * @license    OMS License 1.0
 * @link       https://orange-management.org
 * @since      1.0.0
 */
class HelperView extends View
{
    protected $dataSets = [];
    protected $dataSet  = null;
}
