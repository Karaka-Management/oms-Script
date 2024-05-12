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

use Modules\Script\Controller\BackendController;
use Modules\Script\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/script/template/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\BackendController:viewTemplateCreate',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^/script/report/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\BackendController:viewReportCreate',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
    '^/script/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\BackendController:viewTemplateList',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
    '^/script/report/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Script\Controller\BackendController:viewHelperReport',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
    ],
];
