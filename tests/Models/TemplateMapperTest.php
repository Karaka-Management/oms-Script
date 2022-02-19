<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Helper\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Helper\Models\HelperStatus;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Helper\Models\TemplateMapper;
use Modules\Media\Models\Collection;
use Modules\Media\Models\Media;
use phpOMS\DataStorage\Database\Query\OrderType;

/**
 * @testdox Modules\tests\Helper\Models\TemplateMapperTest: Template database mapper
 *
 * @internal
 */
final class TemplateMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox The model can be created and read from the database
     * @covers Modules\Helper\Models\TemplateMapper
     * @group module
     */
    public function testCR() : void
    {
        $template = new Template();

        $template->createdBy = new NullAccount(1);
        $template->name      = 'Title';
        $template->setStatus(HelperStatus::ACTIVE);
        $template->description    = 'Description';
        $template->descriptionRaw = 'DescriptionRaw';
        $template->setDatatype(TemplateDataType::OTHER);
        $template->isStandalone = false;
        $template->setExpected(['source1.csv', 'source2.csv']);

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
        self::assertGreaterThan(0, $template->getId());
        self::assertEquals($id, $template->getId());

        $templateR = TemplateMapper::get()->where('id', $template->getId())->execute();
        self::assertEquals($template->createdAt->format('Y-m-d'), $templateR->createdAt->format('Y-m-d'));
        self::assertEquals($template->createdBy->getId(), $templateR->createdBy->getId());
        self::assertEquals($template->description, $templateR->description);
        self::assertEquals($template->descriptionRaw, $templateR->descriptionRaw);
        self::assertEquals($template->name, $templateR->name);
        self::assertEquals($template->getStatus(), $templateR->getStatus());
        self::assertEquals($template->isStandalone, $templateR->isStandalone);
        self::assertEquals($template->getDatatype(), $templateR->getDatatype());
        self::assertEquals($template->getExpected(), $templateR->getExpected());
    }

    /**
     * @testdox The newest model can be read from the database
     * @covers Modules\Helper\Models\TemplateMapper
     * @group module
     */
    public function testNewest() : void
    {
        $newest = TemplateMapper::getAll()->sort('id', OrderType::DESC)->limit(1)->execute();

        self::assertCount(1, $newest);
    }

    /**
     * @covers Modules\Helper\Models\TemplateMapper
     * @group module
     */
    public function testVirtualPath() : void
    {
        $virtualPath = TemplateMapper::getByVirtualPath('/')->execute();

        self::assertGreaterThan(0, \count($virtualPath));
    }
}
