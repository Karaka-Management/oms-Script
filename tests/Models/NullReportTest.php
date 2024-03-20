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

use Modules\Helper\Models\NullReport;

/**
 * @internal
 */
final class NullReportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Modules\Helper\Models\NullReport
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Helper\Models\Report', new NullReport());
    }

    /**
     * @covers \Modules\Helper\Models\NullReport
     * @group module
     */
    public function testId() : void
    {
        $null = new NullReport(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers \Modules\Helper\Models\NullReport
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullReport(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
