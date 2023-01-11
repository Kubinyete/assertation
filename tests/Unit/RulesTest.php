<?php

namespace Kubinyete\Assertation\Tests\Unit;

use Kubinyete\Assertation\Assert;
use Kubinyete\Assertation\AssertBuilder;
use Kubinyete\Assertation\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class RulesTest extends TestCase
{
    public function testTrim()
    {
        $this->assertTrue(Assert::value('   abc    ')->asTrim()->eq('abc')->valid());
    }

    public function testUppercase()
    {
        $this->assertTrue(Assert::value('a')->asUppercase()->eq('A')->valid());
    }

    public function testLowercase()
    {
        $this->assertTrue(Assert::value('B')->asLowercase()->eq('b')->valid());
    }

    public function testExtract()
    {
        $this->assertTrue(Assert::value('aa123bb')->asExtract('ab')->eq('aabb')->valid());
    }

    public function testDigits()
    {
        $this->assertTrue(Assert::value('  a123b  ')->asDigits()->eq('123')->valid());
    }

    public function testTruncate()
    {
        $this->assertTrue(Assert::value('abcdef')->asTruncate(4)->eq('a...')->valid());
        $this->assertTrue(Assert::value('abcdef')->asTruncate(6)->eq('abcdef')->valid());
    }

    public function testLimit()
    {
        $this->assertTrue(Assert::value('abcdef')->asLimit(4)->eq('abcd')->valid());
        $this->assertTrue(Assert::value('abcdef')->asLimit(6)->eq('abcdef')->valid());
    }

    public function testDecimal()
    {
        $this->assertFalse(Assert::value('')->asDecimal()->valid());
        $this->assertFalse(Assert::value('.')->asDecimal()->valid());
        $this->assertFalse(Assert::value('awod')->asDecimal()->valid());
        $this->assertFalse(Assert::value('123.')->asDecimal()->valid());

        $this->assertTrue(Assert::value('123')->asDecimal()->valid());
        $this->assertTrue(Assert::value('123.1')->asDecimal()->valid());
        $this->assertTrue(Assert::value('123.12')->asDecimal()->valid());
        $this->assertTrue(Assert::value('.12')->asDecimal()->valid());
    }
}
