<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Helper\Controller\BackendController;
use Modules\Helper\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/helper/template/create.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\BackendController:viewTemplateCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/helper/report/create.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\BackendController:viewReportCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
    '^.*/helper/list.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\BackendController:viewTemplateList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
    '^.*/helper/report/view.*$' => [
        [
            'dest'       => '\Modules\Helper\Controller\BackendController:viewHelperReport',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
];
