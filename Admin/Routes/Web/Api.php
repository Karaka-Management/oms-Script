<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Script\Controller\ApiController;
use Modules\Script\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/script/report/export(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\ApiController:apiHelperExport',
            'verb'       => RouteVerb::GET,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
    '^.*/script/report/template(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\ApiController:apiTemplateCreate',
            'verb'       => RouteVerb::SET,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/script/report/report(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\ApiController:apiReportCreate',
            'verb'       => RouteVerb::SET,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
];
