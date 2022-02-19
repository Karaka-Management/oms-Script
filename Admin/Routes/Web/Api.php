<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Helper\Controller\ApiController;
use Modules\Helper\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/helper/report/export.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\ApiController:apiHelperExport',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::REPORT,
            ],
        ],
    ],
    '^.*/helper/report/template.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\ApiController:apiTemplateCreate',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TEMPLATE,
            ],
        ],
    ],
    '^.*/helper/report/report.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\ApiController:apiReportCreate',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::REPORT,
            ],
        ],
    ],
];
