<?php

namespace Kubinyete\Assertation\Tests\Unit;

use Kubinyete\Assertation\Assert;
use Kubinyete\Assertation\AssertBuilder;
use Kubinyete\Assertation\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class AssertTest extends TestCase
{
    public function testCreateAssertValue()
    {
        $value = Assert::value(1);
        $this->assertInstanceOf(AssertBuilder::class, $value);
    }

    public function testCanChangeLanguage()
    {
        $this->expectExceptionMessageMatches('/^Verificação de validação falhou/');
        Assert::setLanguage('pt_BR');
        Assert::value(1)->eq(2)->validate()->get();
    }

    public function testThrowOnInvalidLanguage()
    {
        $this->expectException(UnexpectedValueException::class);
        Assert::setLanguage('does not exist');
    }
}
