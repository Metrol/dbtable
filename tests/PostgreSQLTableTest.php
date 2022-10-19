<?php
/**
 * @author        Michael Collette <mcollette@meetingevolution.net>
 * @version       1.0
 * @package       DBTable
 * @copyright (c) 2016, DBTable
 */

namespace Metrol\Tests;

use PHPUnit\Framework\TestCase;

use Metrol\DBTable;
// use Metrol\DBConnect;
use PDO;
use RangeException;
use PDOException;

/**
 * Test the PostgreSQL table and field objects
 *
 */
class PostgreSQLTableTest extends TestCase
{
    /**
     * File where I put the DB credentials
     *
     */
    const DB_CREDENTIALS = 'etc/postgresql_test.ini';

    /**
     * The table used for testing
     *
     */
    const TABLE_NAME = 'pgtable1';

    /**
     * The database to perform tests on
     *
     */
    private PDO $db;

    /**
     * The table being worked with for testing
     *
     */
    private DBTable\PostgreSQL $table;

    /**
     * Connect to the database to make the $db property available for testing.
     *
     */
    public function setUp(): void
    {
        if ( isset($this->db) )
        {
            return;
        }

        // (new DBConnect\Load\INI(self::DB_CREDENTIALS))->run();
        //
        // $this->db = DBConnect\Connect\Bank::get();

        $ini = parse_ini_file(self::DB_CREDENTIALS);

        $dsn = 'pgsql:';
        $dsn .= implode(';', [
            'host=' . $ini['host'],
            'port=' . $ini['port'],
            'dbname=' . $ini['dbname']
         ]);

        $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        try
        {
            $this->db = new PDO($dsn, $ini['user'], $ini['password'], $opts);
        }
        catch ( PDOException )
        {
            echo 'Connection to database failed.';
            exit;
        }

        $this->table = new DBTable\PostgreSQL(self::TABLE_NAME);
        $this->table->runFieldLookup($this->db);
    }

    /**
     * Disconnect from the database
     *
     */
    public function tearDown(): void
    {
        unset($this->db);
    }

    /**
     * Verify the parts going into the DBTable object are coming out as expected
     *
     */
    public function testTableBasics()
    {
        $this->assertEquals('pgtable1', $this->table->getName());
        $this->assertEquals('public', $this->table->getSchema());
        $this->assertEquals('public.pgtable1', $this->table->getFQN());
        $this->assertEquals('public.pgtable1 alia', $this->table->getFQN('alia'));
        $this->assertEquals('"public"."pgtable1"', $this->table->getFQNQuoted());
        $this->assertEquals('"public"."pgtable1" "alia"',
                            $this->table->getFQNQuoted('alia'));
    }

    /**
     * Test that the primary key field can be found
     *
     */
    public function testPrimaryKeyField()
    {
        $field = $this->table->getField($this->table->getPrimaryKeys()[0]);
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer',
                                $field);
        $this->assertEquals('primaryKeyID', $field->getName());
    }

    /**
     * Test fields that accept some type of markup language, like XML or JSON
     *
     */
    public function testMarkupFields()
    {
        $field = $this->table->getField('jsonone'); // JSON field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\JSON', $field);
        $this->assertEquals('json', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        // $field = $this->table->getField('xmarkuplang'); // XML field
        // $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\XML', $field);
        // $this->assertEquals('xml', $field->getDefinedType());
        // $this->assertTrue($field->isNullOk());
        // $this->assertNull($field->getDefaultValue());
    }

    /**
     * Test an ENUM field, and that it's values can be fetched
     *
     */
    public function testEnumField()
    {
        $field = $this->table->getField('yeahnay'); // An enum column
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Enumerated',
                                $field);
        $enumValues = $field->getValues();
        $this->assertCount(2, $enumValues);
        $this->assertEquals('Yes', $enumValues[0]);
        $this->assertEquals('No', $enumValues[1]);
    }

    /**
     * Test a boolean field
     *
     */
    public function testBooleanField()
    {
        $field = $this->table->getField('trueorfalse'); // Boolean
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Boolean',
                                $field);
        $this->assertEquals('trueorfalse', $field->getName());

        $this->assertTrue($field->getPHPValue(1));
        $this->assertFalse($field->getPHPValue(0));
        $this->assertNull($field->getPHPValue(null));

        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('falsedef'); // Boolean, No Nulls, default False
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Boolean', $field);
        $this->assertEquals('falsedef', $field->getName());

        $this->assertFalse($field->getPHPValue(null));

        // Check for an exception when in strict
        $field->setStrictValues();
        $x = false;

        try
        {
            $field->getPHPValue(null);
        }
        catch ( RangeException )
        {
            $x = true;
        }

        $this->assertTrue($x);

        $field = $this->table->getField('truedef'); // Boolean, No Nulls, default true
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Boolean', $field);
        $this->assertEquals('truedef', $field->getName());

        $this->assertTrue($field->getPHPValue(null));
        $boundVal = $field->getSqlBoundValue(null);
        $valMarker = $boundVal->getValueMarker();

        $this->assertEquals('true', $boundVal->getBoundValues()[$valMarker]);
    }

    /**
     * Tests for various string and character types
     *
     */
    public function testStringFields()
    {
        $field = $this->table->getField('stringone');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character',
                                $field);
        $this->assertTrue($field->isNullOk());
        $this->assertEquals(50, $field->getMaxCharacters());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('stringtwo');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character',
                                $field);
        $this->assertFalse($field->isNullOk());
        $this->assertEquals("'ABCDE'::bpchar", $field->getDefaultValue());
        $this->assertEquals(5, $field->getMaxCharacters());

        $field = $this->table->getField('stringthree');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character',
                                $field);
        $this->assertTrue($field->isNullOk());
        $this->assertEquals("'blah blah'::text", $field->getDefaultValue());
        $this->assertNull($field->getMaxCharacters());
    }

    /**
     * Tests for the various number types
     *
     */
    public function testNumberFields()
    {
        $field = $this->table->getField('numberone');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer',
                                $field);
        $this->assertEquals('numberone', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-2147483648, $field->getMin());
        $this->assertEquals(2147483647, $field->getMax());

        $field = $this->table->getField('numbertwo');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric',
                                $field);
        $this->assertEquals('numbertwo', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9999.9999, $field->getMin());
        $this->assertEquals(9999.9999, $field->getMax());

        $field = $this->table->getField('numberthree');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer',
                                $field);
        $this->assertEquals('numberthree', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9223372036854775808, $field->getMin());
        $this->assertEquals(9223372036854775807, $field->getMax());

        $field = $this->table->getField('numberfour');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer',
                                $field);
        $this->assertEquals('numberfour', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-32768, $field->getMin());
        $this->assertEquals(32767, $field->getMax());

        $field = $this->table->getField('numberfive'); // Double precision field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric',
                                $field);
        $this->assertEquals('numberfive', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('numbersix'); // Money field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric',
                                $field);
        $this->assertEquals('numbersix', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('numberseven'); // real field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric',
                                $field);
        $this->assertEquals('numberseven', $field->getName());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
    }

    /**
     * Tests for date and time related fields
     *
     */
    public function testDateTimeFields()
    {
        $field = $this->table->getField('dateone'); // date field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('date', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('datetwo'); // Timestamp without a timezone
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('timestamp', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('datethree'); // Timestamp with timezone field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('timestamptz', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('timeone'); // Time without TZ field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Time', $field);
        $this->assertEquals('time', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $field = $this->table->getField('timetwo'); // Time with TZ field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Time', $field);
        $this->assertEquals('timetz', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
    }

    /**
     * Tests for geometric field types
     *
     */
    public function testGeometricFields()
    {
        $field = $this->table->getField('xypoint'); // date field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Point', $field);
        $this->assertEquals('point', $field->getDefinedType());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        $phpVal = $field->getPHPValue('(3.14, 4.53)');

        $this->assertCount(2, $phpVal);
        $this->assertEquals(3.14, $phpVal[0]);
        $this->assertEquals(4.53, $phpVal[1]);

        $fieldVal = $field->getSqlBoundValue([3, 4.53]);

        $this->assertCount(2, $fieldVal->getBoundValues());
        $this->assertEquals(2, $fieldVal->getBindCount());

        $bindArr = $fieldVal->getBoundValues();
        $keys = array_keys($bindArr);
        $vals = array_values($bindArr);

        $testSql = 'point(' . implode(', ', $keys) . ')';
        $this->assertEquals($testSql, $fieldVal->getValueMarker());

        $this->assertEquals(3, $vals[0]);
        $this->assertEquals(4.53, $vals[1]);
    }

    /**
     * Tests the field validation for characters routines
     *
     */
    public function testCharacterFieldValidation()
    {
        // Field should only allow 50 characters
        $field = $this->table->getField('stringone');

        $testStr = str_repeat('x', 56);
        $this->assertEquals(56, strlen($testStr));
        $cleanStr = $field->getPHPValue($testStr);
        $this->assertEquals(50, strlen($cleanStr));


        $testStr = str_repeat('z', 50);

        $fieldVal = $field->getSqlBoundValue($testStr);
        $key = $fieldVal->getValueMarker();

        $this->assertEquals($key, $fieldVal->getValueMarker());
        $this->assertEquals($testStr, $fieldVal->getBoundValues()[$key]);
    }

    /**
     * Tests the field validation for dates
     *
     */
    public function testDateFieldValidation()
    {
        $field = $this->table->getField('dateone');

        $testDate = 'July 4, 2016';

        $fldObj = $field->getPHPValue($testDate);

        $this->assertInstanceOf('DateTime', $fldObj);
        $this->assertEquals('2016-07-04', $fldObj->format('Y-m-d'));

        $fieldVal = $field->getSqlBoundValue($testDate);
        $key = $fieldVal->getValueMarker();

        $this->assertEquals(1, $fieldVal->getBindCount());
        $this->assertEquals('2016-07-04', $fieldVal->getBoundValues()[$key]);
    }

    /**
     * Tests the field validation for integers
     *
     */
    public function testIntegerFieldValidation()
    {
        $field = $this->table->getField('numberfour');

        $testVal  = 1;
        $expected = 1;
        $this->assertEquals($expected, $field->getPHPValue($testVal));

        $testVal  = 1.123;
        $this->assertEquals($expected, $field->getPHPValue($testVal));

        $testVal  = 0;
        $expected = 0;
        $this->assertEquals($expected, $field->getPHPValue($testVal));
    }
}
