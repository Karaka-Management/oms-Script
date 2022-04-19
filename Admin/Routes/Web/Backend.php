<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Helper\Controller\BackendController;
use Modules\Helper\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/helper/template/create.*$' => [
        [
            'dest'       => '\Modules\Media\Controller\BackendController::setUpFileUploaderTrait',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
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
            'dest'       => '\Modules\Media\Controller\BackendController::setUpFileUploaderTrait',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::REPORT,
            ],
        ],
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
