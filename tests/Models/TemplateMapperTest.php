<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Script\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Script\Models\ScriptStatus;
use Modules\Script\Models\Template;
use Modules\Script\Models\TemplateDataType;
use Modules\Script\Models\TemplateMapper;
use Modules\Media\Models\Collection;
use Modules\Media\Models\Media;
use phpOMS\DataStorage\Database\Query\OrderType;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Script\Models\TemplateMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\tests\Helper\Models\TemplateMapperTest: Template database mapper')]
final class TemplateMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model can be created and read from the database')]
    public function testCR() : void
    {
        $template = new Template();

        $template->createdBy      = new NullAccount(1);
        $template->name           = 'Title';
        $template->status         = ScriptStatus::ACTIVE;
        $template->description    = 'Description';
        $template->descriptionRaw = 'DescriptionRaw';
        $template->datatype       = TemplateDataType::OTHER;
        $template->isStandalone   = false;
        $template->expected       = ['source1.csv', 'source2.csv'];

        $collection            = new Collection();
        $collection->createdBy = new NullAccount(1);

        $templateFiles = [
            [
                'extension' => 'php',
                'filename'  => 'EventCourse.lang.php',
                'name'      => 'EventCourse',
                'path'      => '/Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'php',
                'filename'  => 'EventCourse.pdf.php',
                'name'      => 'EventCourse',
                'path'      => '/Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'php',
                'filename'  => 'EventCourse.tpl.php',
                'name'      => 'EventCourse',
                'path'      => '/Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'php',
                'filename'  => 'EventCourse.xlsx.php',
                'name'      => 'EventCourse',
                'path'      => '/Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'php',
                'filename'  => 'Worker.php',
                'name'      => 'Worker',
                'path'      => '/Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
        ];

        foreach ($templateFiles as $file) {
            $media            = new Media();
            $media->createdBy = new NullAccount(1);
            $media->extension = $file['extension'];
            $media->setPath(\trim($file['path'], '/') . '/' . $file['filename']);
            $media->name = $file['name'];
            $media->size = $file['size'];

            $collection->addSource($media);
        }

        $template->source = $collection;

        $id = TemplateMapper::create()->execute($template);
        self::assertGreaterThan(0, $template->id);
        self::assertEquals($id, $template->id);

        $templateR = TemplateMapper::get()->where('id', $template->id)->execute();
        self::assertEquals($template->createdAt->format('Y-m-d'), $templateR->createdAt->format('Y-m-d'));
        self::assertEquals($template->createdBy->id, $templateR->createdBy->id);
        self::assertEquals($template->description, $templateR->description);
        self::assertEquals($template->descriptionRaw, $templateR->descriptionRaw);
        self::assertEquals($template->name, $templateR->name);
        self::assertEquals($template->status, $templateR->status);
        self::assertEquals($template->isStandalone, $templateR->isStandalone);
        self::assertEquals($template->getDatatype(), $templateR->getDatatype());
        self::assertEquals($template->getExpected(), $templateR->getExpected());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The newest model can be read from the database')]
    public function testNewest() : void
    {
        $newest = TemplateMapper::getAll()->sort('id', OrderType::DESC)->limit(1)->executeGetArray();

        self::assertCount(1, $newest);
    }
}
