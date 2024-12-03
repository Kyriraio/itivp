<?php
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    // Простой тест
    public function testAddition()
    {
        $this->assertEquals(4, 2 + 2);
    }

    // Тест на строковое значение
    public function testString()
    {
        $this->assertStringContainsString('hello', 'hello world');
    }
}
