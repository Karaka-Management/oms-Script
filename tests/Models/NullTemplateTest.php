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

use Modules\Helper\Models\NullTemplate;

/**
 * @internal
 */
final class NullTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Helper\Models\NullTemplate
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Helper\Models\Template', new NullTemplate());
    }

    /**
     * @covers Modules\Helper\Models\NullTemplate
     * @group module
     */
    public function testId() : void
    {
        $null = new NullTemplate(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Helper\Models\NullTemplate
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTemplate(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
