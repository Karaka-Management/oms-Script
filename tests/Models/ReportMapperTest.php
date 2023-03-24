<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Helper\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Helper\Models\HelperStatus;
use Modules\Helper\Models\Report;
use Modules\Helper\Models\ReportMapper;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Media\Models\Collection;
use Modules\Media\Models\Media;

/**
 * @testdox Modules\tests\Helper\Models\ReportMapperTest: Report database mapper
 *
 * @internal
 */
final class ReportMapperTest extends \PHPUnit\Framework\TestCase
{
    private function createTemplate()
    {
        $template = new Template();

        $template->createdBy = new NullAccount(1);
        $template->name      = 'Report Template';
        $template->setStatus(HelperStatus::ACTIVE);
        $template->description = 'Description';
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

        return $template;
    }

    /**
     * @testdox The model can be created and read from the database
     * @covers Modules\Helper\Models\ReportMapper
     * @group module
     */
    public function testCR() : void
    {
        $report = new Report();

        $report->createdBy = new NullAccount(1);
        $report->title     = 'Title';
        $report->setStatus(HelperStatus::ACTIVE);
        $report->description = 'Description';
        $report->template    = $this->createTemplate();

        $collection            = new Collection();
        $collection->createdBy = new NullAccount(1);

        $reportFiles = [
            [
                'extension' => 'csv',
                'filename'  => 'accounts.csv',
                'name'      => 'accounts',
                'path'      => 'Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'csv',
                'filename'  => 'costcenters.csv',
                'name'      => 'costcenters',
                'path'      => 'Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'csv',
                'filename'  => 'costobjects.csv',
                'name'      => 'costobjects',
                'path'      => 'Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'csv',
                'filename'  => 'crm.csv',
                'name'      => 'crm',
                'path'      => 'Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
            [
                'extension' => 'csv',
                'filename'  => 'entries.csv',
                'name'      => 'entries',
                'path'      => 'Demo/Modules/Helper/EventCourse',
                'size'      => 1,
            ],
        ];

        foreach ($reportFiles as $file) {
            $media            = new Media();
            $media->createdBy = new NullAccount(1);
            $media->extension = $file['extension'];
            $media->setPath(\trim($file['path'], '/') . '/' . $file['filename']);
            $media->name = $file['name'];
            $media->size = $file['size'];

            $collection->addSource($media);
        }

        $report->source = $collection;

        $id = ReportMapper::create()->execute($report);
        self::assertGreaterThan(0, $report->getId());
        self::assertEquals($id, $report->getId());

        $reportR = ReportMapper::get()->with('template')->where('id', $report->getId())->execute();
        self::assertEquals($report->createdAt->format('Y-m-d'), $reportR->createdAt->format('Y-m-d'));
        self::assertEquals($report->createdBy->getId(), $reportR->createdBy->getId());
        self::assertEquals($report->description, $reportR->description);
        self::assertEquals($report->title, $reportR->title);
        self::assertEquals($report->getStatus(), $reportR->getStatus());
        self::assertEquals($report->template->name, $reportR->template->name);
    }
}
