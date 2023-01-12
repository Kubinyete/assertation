<?php

namespace Kubinyete\Assertation\Tests\Unit;

use Kubinyete\Assertation\Assert;
use Kubinyete\Assertation\AssertBuilder;
use Kubinyete\Assertation\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class AssertBuilderTest extends TestCase
{
    public function testNoValidationPass()
    {
        $builder = Assert::value(1);
        $this->assertTrue($builder->valid());
        $this->assertEmpty($builder->errors());
        $this->assertEquals(1, $builder->get());
        $builder->validate();
    }

    public function testValidationCheck()
    {
        $builder = Assert::value(1)->eq(2);
        $this->assertFalse($builder->valid());
        $this->assertNotEmpty($builder->errors());
        $this->assertNull($builder->get());

        $this->expectException(ValidationException::class);
        $builder->validate();
    }

    public function testValidationContextualCheck()
    {
        $builder = Assert::value('  abc  ')->eq(2)->or()->eq(3);
        $this->assertFalse($builder->valid());
        $this->assertNotEmpty($builder->errors());
        $this->assertNull($builder->get());

        $this->expectException(ValidationException::class);
        $builder->validate();
    }

    public function testValidationContextualModificationFailedCheck()
    {
        $builder = Assert::value('  abc  ')->eq(2)->or()->eq('  abc  ')->or()->asTrim()->asLowercase()->eq('abcd');
        $this->assertTrue($builder->valid());
        $this->assertEmpty($builder->errors());
        $this->assertEquals('  abc  ', $builder->get());
        $builder->validate();
    }

    public function testValidationContextualModificationValidCheck()
    {
        $builder = Assert::value('  abc  ')->eq(2)->or()->eq('  abc  ')->or()->asTrim()->asLowercase()->eq('abc');
        $this->assertTrue($builder->valid());
        $this->assertEmpty($builder->errors());
        $this->assertEquals('abc', $builder->get());
        $builder->validate();
    }

    public function testValidationContextualModificationInheritsPreviousChecks()
    {
        Assert::setLanguage('en_US');

        $builder = Assert::value('  abc  ')->eq(2)->or()->eq('abcd')->or()->integer()->asTrim()->asLowercase()->eq('abc');
        $this->assertFalse($builder->valid());
        $this->assertNotEmpty($builder->errors());
        $this->assertNull($builder->get());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Expected to be an integer/');
        $builder->validate();
    }

    public function testRulesCanPassArgs()
    {
        $builder = Assert::value('  abc  ')->rules('asTrim;lbetween,3,3|eq,1');
        $this->assertTrue($builder->valid());
        $this->assertEmpty($builder->errors());
        $this->assertEquals('abc', $builder->get());

        $builder->validate();
    }
}
