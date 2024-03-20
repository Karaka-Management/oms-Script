<?php
/**
 * Jingga
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
use Modules\Helper\Models\NullReport;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Media\Models\NullCollection;
use Modules\Organization\Models\NullUnit;
use phpOMS\Utils\TestUtils;

/**
 * @testdox Modules\tests\Helper\Models\TemplateTest: Template model
 *
 * @internal
 */
final class TemplateTest extends \PHPUnit\Framework\TestCase
{
    protected Template $template;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->template = new Template();
    }

    /**
     * @testdox The model has the expected default values after initialization
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->template->id);
        self::assertEquals(0, $this->template->unit->id);
        self::assertEquals(0, $this->template->createdBy->id);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->template->createdAt->format('Y-m-d'));
        self::assertEquals('', $this->template->name);
        self::assertEquals(HelperStatus::INACTIVE, $this->template->status);
        self::assertEquals('', $this->template->description);
        self::assertEquals('', $this->template->descriptionRaw);
        self::assertEquals([], $this->template->getExpected());
        self::assertEquals(0, $this->template->source->id);
        self::assertFalse($this->template->isStandalone);
        self::assertEquals(TemplateDataType::OTHER, $this->template->getDatatype());
        self::assertInstanceOf(NullReport::class, $this->template->getNewestReport());
    }

    /**
     * @testdox The unit can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testUnitInputOutput() : void
    {
        $this->template->unit = new NullUnit(1);
        self::assertEquals(1, $this->template->unit->id);
    }

    /**
     * @testdox The creator can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testCreatedByInputOutput() : void
    {
        $this->template->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->template->createdBy->id);
    }

    /**
     * @testdox The title can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testNameInputOutput() : void
    {
        $this->template->name = 'Title';
        self::assertEquals('Title', $this->template->name);
    }

    /**
     * @testdox The template can be set as standalone and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testStandalonInputOutput() : void
    {
        $this->template->isStandalone = true;
        self::assertTrue($this->template->isStandalone);
    }

    /**
     * @testdox The description can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testDescriptionInputOutput() : void
    {
        $this->template->description = 'Description';
        self::assertEquals('Description', $this->template->description);
    }

    /**
     * @testdox The raw description can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testDescriptionRawInputOutput() : void
    {
        $this->template->descriptionRaw = 'DescriptionRaw';
        self::assertEquals('DescriptionRaw', $this->template->descriptionRaw);
    }

    /**
     * @testdox The expected report files can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testExpectedInputOutput() : void
    {
        $this->template->setExpected(['source1.csv', 'source2.csv']);
        $this->template->addExpected('source3.csv');
        self::assertEquals(['source1.csv', 'source2.csv', 'source3.csv'], $this->template->getExpected());
    }

    /**
     * @testdox The source can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testSourceInputOutput() : void
    {
        $this->template->source = new NullCollection(4);
        self::assertEquals(4, $this->template->source->id);
    }

    /**
     * @testdox The data storage type can be set and returned correctly
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testDatatypeInputOutput() : void
    {
        $this->template->setDatatype(TemplateDataType::GLOBAL_DB);
        self::assertEquals(TemplateDataType::GLOBAL_DB, $this->template->getDatatype());
    }

    /**
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testNewestReportOutput() : void
    {
        TestUtils::setMember($this->template, 'reports', [
            $a = new NullReport(1),
            $b = new NullReport(2),
        ]);

        self::assertEquals($b, $this->template->getNewestReport());
    }

    /**
     * @testdox Template data can be turned into an array
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testToArray() : void
    {
        $this->template->name           = 'testName';
        $this->template->description    = 'testDescription';
        $this->template->descriptionRaw = 'testDescriptionRaw';
        $this->template->isStandalone   = true;

        $array    = $this->template->toArray();
        $expected = [
            'id'             => 0,
            'name'           => 'testName',
            'description'    => 'testDescription',
            'descriptionRaw' => 'testDescriptionRaw',
            'status'         => HelperStatus::INACTIVE,
            'datatype'       => TemplateDataType::OTHER,
            'standalone'     => true,
        ];

        foreach ($expected as $key => $e) {
            if (!isset($array[$key]) || $array[$key] !== $e) {
                self::assertTrue(false);
            }
        }

        self::assertTrue(true);
    }

    /**
     * @testdox Template data can be json serialized
     * @covers \Modules\Helper\Models\Template
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $this->template->name           = 'testName';
        $this->template->description    = 'testDescription';
        $this->template->descriptionRaw = 'testDescriptionRaw';
        $this->template->isStandalone   = true;

        $array    = $this->template->jsonSerialize();
        $expected = [
            'id'             => 0,
            'name'           => 'testName',
            'description'    => 'testDescription',
            'descriptionRaw' => 'testDescriptionRaw',
            'status'         => HelperStatus::INACTIVE,
            'datatype'       => TemplateDataType::OTHER,
            'standalone'     => true,
        ];

        foreach ($expected as $key => $e) {
            if (!isset($array[$key]) || $array[$key] !== $e) {
                self::assertTrue(false);
            }
        }

        self::assertTrue(true);
    }
}
