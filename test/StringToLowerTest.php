<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\StringToLower as StringToLowerFilter;
use Laminas\Stdlib\ErrorHandler;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 * @subpackage UnitTests
 * @group      Laminas_Filter
 */
class StringToLowerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Laminas_Filter_StringToLower object
     *
     * @var Laminas_Filter_StringToLower
     */
    protected $_filter;

    /**
     * Creates a new Laminas_Filter_StringToLower object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        $this->_filter = new StringToLowerFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = $this->_filter;
        $valuesExpected = array(
            'string' => 'string',
            'aBc1@3' => 'abc1@3',
            'A b C'  => 'a b c'
        );

        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior with
     * specified encoding
     *
     * @return void
     */
    public function testWithEncoding()
    {
        $filter = $this->_filter;
        $valuesExpected = array(
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123'
        );

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertEquals($output, $filter($input));
            }
        } catch (\Laminas\Filter\Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testFalseEncoding()
    {
        if (!function_exists('mb_strtolower')) {
            $this->markTestSkipped('mbstring required');
        }

        $this->setExpectedException('\Laminas\Filter\Exception\InvalidArgumentException', 'is not supported');
        $this->_filter->setEncoding('aaaaa');
    }

    /**
     * @Laminas-8989
     */
    public function testInitiationWithEncoding()
    {
        $valuesExpected = array(
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123'
        );

        try {
            $filter = new StringToLowerFilter(array('encoding' => 'UTF-8'));
            foreach ($valuesExpected as $input => $output) {
                $this->assertEquals($output, $filter($input));
            }
        } catch (\Laminas\Filter\Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding()
    {
        $filter = $this->_filter;
        $valuesExpected = array(
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123'
        );

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertEquals($output, $filter($input));
            }

            $this->_filter->setEncoding('utf-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertEquals($output, $filter($input));
            }

            $this->_filter->setEncoding('UtF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertEquals($output, $filter($input));
            }
        } catch (\Laminas\Filter\Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @group Laminas-9854
     */
    public function testDetectMbInternalEncoding()
    {
        if (!function_exists('mb_internal_encoding')) {
            $this->markTestSkipped("Function 'mb_internal_encoding' not available");
        }

        $this->assertEquals(mb_internal_encoding(), $this->_filter->getEncoding());
    }

    /**
     * Ensures that a warning is raised if array is used
     *
     * @return void
     */
    public function testWarningIsRaisedIfArrayUsed()
    {
        $input = array('ABC', 'DEF');

        ErrorHandler::start(E_USER_WARNING);
        $filtered = $this->_filter->filter($input);
        $err = ErrorHandler::stop();

        $this->assertEquals($input, $filtered);
        $this->assertInstanceOf('ErrorException', $err);
        $this->assertContains('cannot filter', $err->getMessage());
    }

    /**
     * @return void
     */
    public function testReturnsNullIfNullIsUsed()
    {
        $filtered = $this->_filter->filter(null);
        $this->assertNull($filtered);
    }
}
