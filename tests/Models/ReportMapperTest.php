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
use Modules\Script\Models\Report;
use Modules\Script\Models\ReportMapper;
use Modules\Script\Models\Template;
use Modules\Script\Models\TemplateDataType;
use Modules\Media\Models\Collection;
use Modules\Media\Models\Media;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Script\Models\ReportMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\tests\Helper\Models\ReportMapperTest: Report database mapper')]
final class ReportMapperTest extends \PHPUnit\Framework\TestCase
{
    private function createTemplate()
    {
        $template = new Template();

        $template->createdBy   = new NullAccount(1);
        $template->name        = 'Report Template';
        $template->status      = ScriptStatus::ACTIVE;
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model can be created and read from the database')]
    public function testCR() : void
    {
        $report = new Report();

        $report->createdBy   = new NullAccount(1);
        $report->title       = 'Title';
        $report->status      = ScriptStatus::ACTIVE;
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
        self::assertGreaterThan(0, $report->id);
        self::assertEquals($id, $report->id);

        $reportR = ReportMapper::get()->with('template')->where('id', $report->id)->execute();
        self::assertEquals($report->createdAt->format('Y-m-d'), $reportR->createdAt->format('Y-m-d'));
        self::assertEquals($report->createdBy->id, $reportR->createdBy->id);
        self::assertEquals($report->description, $reportR->description);
        self::assertEquals($report->title, $reportR->title);
        self::assertEquals($report->status, $reportR->status);
        self::assertEquals($report->template->name, $reportR->template->name);
    }
}
