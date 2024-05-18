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
use Modules\Media\Models\NullCollection;
use Modules\Organization\Models\NullUnit;
use Modules\Script\Models\NullReport;
use Modules\Script\Models\ScriptStatus;
use Modules\Script\Models\Template;
use Modules\Script\Models\TemplateDataType;
use phpOMS\Utils\TestUtils;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Script\Models\Template::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\tests\Script\Models\TemplateTest: Template model')]
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The model has the expected default values after initialization')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->template->id);
        self::assertEquals(0, $this->template->unit->id);
        self::assertEquals(0, $this->template->createdBy->id);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->template->createdAt->format('Y-m-d'));
        self::assertEquals('', $this->template->name);
        self::assertEquals(ScriptStatus::INACTIVE, $this->template->status);
        self::assertEquals('', $this->template->description);
        self::assertEquals('', $this->template->descriptionRaw);
        self::assertEquals([], $this->template->getExpected());
        self::assertEquals(0, $this->template->source->id);
        self::assertFalse($this->template->isStandalone);
        self::assertEquals(TemplateDataType::OTHER, $this->template->getDatatype());
        self::assertInstanceOf(NullReport::class, $this->template->getNewestReport());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The unit can be set and returned correctly')]
    public function testUnitInputOutput() : void
    {
        $this->template->unit = new NullUnit(1);
        self::assertEquals(1, $this->template->unit->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The creator can be set and returned correctly')]
    public function testCreatedByInputOutput() : void
    {
        $this->template->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->template->createdBy->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The title can be set and returned correctly')]
    public function testNameInputOutput() : void
    {
        $this->template->name = 'Title';
        self::assertEquals('Title', $this->template->name);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The template can be set as standalone and returned correctly')]
    public function testStandalonInputOutput() : void
    {
        $this->template->isStandalone = true;
        self::assertTrue($this->template->isStandalone);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The description can be set and returned correctly')]
    public function testDescriptionInputOutput() : void
    {
        $this->template->description = 'Description';
        self::assertEquals('Description', $this->template->description);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The raw description can be set and returned correctly')]
    public function testDescriptionRawInputOutput() : void
    {
        $this->template->descriptionRaw = 'DescriptionRaw';
        self::assertEquals('DescriptionRaw', $this->template->descriptionRaw);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The expected report files can be set and returned correctly')]
    public function testExpectedInputOutput() : void
    {
        $this->template->setExpected(['source1.csv', 'source2.csv']);
        $this->template->addExpected('source3.csv');
        self::assertEquals(['source1.csv', 'source2.csv', 'source3.csv'], $this->template->getExpected());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The source can be set and returned correctly')]
    public function testSourceInputOutput() : void
    {
        $this->template->source = new NullCollection(4);
        self::assertEquals(4, $this->template->source->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('The data storage type can be set and returned correctly')]
    public function testDatatypeInputOutput() : void
    {
        $this->template->setDatatype(TemplateDataType::GLOBAL_DB);
        self::assertEquals(TemplateDataType::GLOBAL_DB, $this->template->getDatatype());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testNewestReportOutput() : void
    {
        TestUtils::setMember($this->template, 'reports', [
            $a = new NullReport(1),
            $b = new NullReport(2),
        ]);

        self::assertEquals($b, $this->template->getNewestReport());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('Template data can be turned into an array')]
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
            'status'         => ScriptStatus::INACTIVE,
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    #[\PHPUnit\Framework\Attributes\TestDox('Template data can be json serialized')]
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
            'status'         => ScriptStatus::INACTIVE,
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
