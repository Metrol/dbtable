<?php
/**
 * @author        Michael Collette <mcollette@meetingevolution.net>
 * @version       1.0
 * @package       DBTable
 * @copyright (c) 2016, DBTable
 */

namespace Metrol;

use PDO;
use Metrol\DBTable;

/**
 * Test the PostgreSQL table and field objects
 *
 */
class PostgreSQLTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * File where I put the DB credentials
     *
     * @const
     */
    const DB_CREDENTIALS = 'etc/db.ini';

    /**
     * The database to perform tests on
     *
     * @var PDO
     */
    private $db;

    /**
     * Connect to the database so as to make the $db property available for
     * testing.
     *
     */
    public function setUp()
    {
        $ini = parse_ini_file(self::DB_CREDENTIALS);

        $dsn = 'pgsql:';
        $dsn .= implode(';', [
            'host=' .  $ini['DBHOST'],
            'port='.   $ini['DBPORT'],
            'dbname='. $ini['DBNAME']
        ]);

        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $this->db = new PDO($dsn, $ini['DBUSER'], $ini['DBPASS'], $opts);
    }

    /**
     * Disconnect from the database
     *
     */
    public function tearDown()
    {
        $this->db = null;
    }

    /**
     * Verify the parts going into the DBTable object are coming out as expected
     *
     */
    public function testTableBasics()
    {
        $table = new DBTable\PostgreSQL('pgtable1');

        $this->assertEquals('pgtable1', $table->getName());
        $this->assertEquals('public', $table->getSchema());
        $this->assertEquals('public.pgtable1', $table->getFQN());
        $this->assertEquals('public.pgtable1 alia', $table->getFQN('alia'));
        $this->assertEquals('"public"."pgtable1"', $table->getFQNQuoted());
        $this->assertEquals('"public"."pgtable1" "alia"', $table->getFQNQuoted('alia'));
    }

    /**
     * Investigates a table with a variety of field types and must figure it all
     * out by looking it up in the DB.
     *
     */
    public function testTableFieldLookup()
    {
        $table = new DBTable\PostgreSQL('pgtable1');
        $table->runFieldLookup($this->db);

        $keys = $table->getPrimaryKeys();
        $this->assertCount(1, $keys);
        $this->assertEquals('primaryKeyID', $keys[0]);

        // Test some of the fields that they came out correctly
        $field = $table->getField('stringone');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertTrue($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals(50, $field->getMaxCharacters());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('string', $field->getPhpType());

        $field = $table->getField('stringtwo');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertFalse($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals("'ABCDE'::bpchar", $field->getDefaultValue());
        $this->assertEquals(5, $field->getMaxCharacters());
        $this->assertEquals('string', $field->getPhpType());

        $field = $table->getField('stringthree');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertTrue($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals("'blah blah'::text", $field->getDefaultValue());
        $this->assertNull($field->getMaxCharacters());
        $this->assertEquals('string', $field->getPhpType());

        $field = $table->getField('numberone');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('numberone', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-2147483648, $field->getMin());
        $this->assertEquals(2147483647, $field->getMax());
        $this->assertEquals('integer', $field->getPhpType());

        $field = $table->getField('numbertwo');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric', $field);
        $this->assertEquals('numbertwo', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9999.9999, $field->getMin());
        $this->assertEquals(9999.9999, $field->getMax());
        $this->assertEquals('float', $field->getPhpType());

        $field = $table->getField('numberthree');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('numberthree', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9223372036854775808, $field->getMin());
        $this->assertEquals(9223372036854775807, $field->getMax());
        $this->assertEquals('integer', $field->getPhpType());

        $field = $table->getField('numberfour');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('numberfour', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-32768, $field->getMin());
        $this->assertEquals(32767, $field->getMax());
        $this->assertEquals('integer', $field->getPhpType());

        $field = $table->getField('numberfive'); // Double precision field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric', $field);
        $this->assertEquals('numberfive', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('float', $field->getPhpType());

        $field = $table->getField('numbersix'); // Money field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric', $field);
        $this->assertEquals('numbersix', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('float', $field->getPhpType());

        $field = $table->getField('numberseven'); // real field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric', $field);
        $this->assertEquals('numberseven', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('float', $field->getPhpType());

        $field = $table->getField('dateone'); // date field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('date', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('\DateTime', $field->getPhpType());

        $field = $table->getField('datetwo'); // Timestamp without a timezone
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('timestamp', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('\DateTime', $field->getPhpType());

        $field = $table->getField('datethree'); // Timestamp with timezone field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Date', $field);
        $this->assertEquals('timestamptz', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('\DateTime', $field->getPhpType());

        $field = $table->getField('timeone'); // Time without TZ field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Time', $field);
        $this->assertEquals('time', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('\DateTime', $field->getPhpType());

        $field = $table->getField('timetwo'); // Time with TZ field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Time', $field);
        $this->assertEquals('timetz', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('\DateTime', $field->getPhpType());

        $field = $table->getField('jsonone'); // JSON field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\JSON', $field);
        $this->assertEquals('json', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('array', $field->getPhpType());

        $field = $table->getField('xmarkuplang'); // XML field
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\XML', $field);
        $this->assertEquals('xml', $field->getUdtName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('string', $field->getPhpType());

        $field = $table->getField('yeahnay'); // An enum column
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Enumerated', $field);
        $enumValues = $field->getValues();
        $this->assertCount(2, $enumValues);
        $this->assertEquals('Yes', $enumValues[0]);
        $this->assertEquals('No', $enumValues[1]);
        $this->assertEquals('string', $field->getPhpType());

        $field = $table->getField('trueorfalse'); // Boolean
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Boolean', $field);
        $this->assertEquals('trueorfalse', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals('bool', $field->getPhpType());

        // The primary key field and some of it's properties
        $field = $table->getField( $table->getPrimaryKeys()[0] );
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('primaryKeyID', $field->getName());
        $this->assertTrue($field->isPrimaryKey());
        $this->assertEquals('integer', $field->getPhpType());
    }
}
