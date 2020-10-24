<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Helper\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Helper\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Media\Models\CollectionMapper;
use Modules\Organization\Models\UnitMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Report mapper class.
 *
 * @package Modules\Helper\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class TemplateMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'helper_template_id'         => ['name' => 'helper_template_id',          'type' => 'int',      'internal' => 'id'],
        'helper_template_status'     => ['name' => 'helper_template_status',      'type' => 'int',      'internal' => 'status'],
        'helper_template_title'      => ['name' => 'helper_template_title',       'type' => 'string',   'internal' => 'name'],
        'helper_template_data'       => ['name' => 'helper_template_data',        'type' => 'int',      'internal' => 'datatype'],
        'helper_template_standalone' => ['name' => 'helper_template_standalone',  'type' => 'bool',     'internal' => 'isStandalone'],
        'helper_template_expected'   => ['name' => 'helper_template_expected',    'type' => 'Json',     'internal' => 'expected'],
        'helper_template_desc'       => ['name' => 'helper_template_desc',        'type' => 'string',   'internal' => 'description'],
        'helper_template_desc_raw'   => ['name' => 'helper_template_desc_raw',    'type' => 'string',   'internal' => 'descriptionRaw'],
        'helper_template_media'      => ['name' => 'helper_template_media',       'type' => 'int',      'internal' => 'source'],
        'helper_template_creator'    => ['name' => 'helper_template_creator',     'type' => 'int',      'internal' => 'createdBy'],
        'helper_template_unit'       => ['name' => 'helper_template_unit',        'type' => 'int',      'internal' => 'unit'],
        'helper_template_created'    => ['name' => 'helper_template_created',     'type' => 'DateTimeImmutable', 'internal' => 'createdAt'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    protected static array $ownsOne = [
        'source' => [
            'mapper'     => CollectionMapper::class,
            'external'   => 'helper_template_media',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string}>
     * @since 1.0.0
     */
    protected static array $belongsTo = [
        'createdBy' => [
            'mapper'     => AccountMapper::class,
            'external'   => 'helper_template_creator',
        ],
        'unit' => [
            'mapper'     => UnitMapper::class,
            'external'   => 'helper_template_unit',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    protected static array $hasMany = [
        'reports' => [
            'mapper'       => ReportMapper::class,
            'table'        => 'helper_report',
            'self'         => 'helper_report_template',
            'external'     => null,
        ],
        'tags' => [
            'mapper'   => TagMapper::class,
            'table'    => 'helper_template_tag',
            'self'     => 'helper_template_tag_dst',
            'external' => 'helper_template_tag_src',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'helper_template';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $createdAt = 'helper_template_created';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'helper_template_id';
}
