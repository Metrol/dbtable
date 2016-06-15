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
        $field = $table->getField('onestring');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertTrue($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals(50, $field->getMaxCharacters());
        $this->assertNull($field->getDefaultValue());

        $field = $table->getField('twostring');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertFalse($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals("'ABCDE'::bpchar", $field->getDefaultValue());
        $this->assertEquals(5, $field->getMaxCharacters());

        $field = $table->getField('threestring');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Character', $field);
        $this->assertTrue($field->isNullOk());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertEquals("'blah blah'::text", $field->getDefaultValue());
        $this->assertNull($field->getMaxCharacters());

        $field = $table->getField('onenumber');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('onenumber', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-2147483648, $field->getMin());
        $this->assertEquals(2147483647, $field->getMax());

        $field = $table->getField('twonumber');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Numeric', $field);
        $this->assertEquals('twonumber', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9999.9999, $field->getMin());
        $this->assertEquals(9999.9999, $field->getMax());

        $field = $table->getField('threenumber');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('threenumber', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-9223372036854775808, $field->getMin());
        $this->assertEquals(9223372036854775807, $field->getMax());

        $field = $table->getField('fournumber');
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('fournumber', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());
        $this->assertEquals(-32768, $field->getMin());
        $this->assertEquals(32767, $field->getMax());

        $field = $table->getField('yeahnay'); // An enum column
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Enumerated', $field);
        $enumValues = $field->getValues();
        $this->assertCount(2, $enumValues);
        $this->assertEquals('Yes', $enumValues[0]);
        $this->assertEquals('No', $enumValues[1]);

        $field = $table->getField('trueorfalse'); // Boolean
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Boolean', $field);
        $this->assertEquals('trueorfalse', $field->getName());
        $this->assertFalse($field->isPrimaryKey());
        $this->assertTrue($field->isNullOk());
        $this->assertNull($field->getDefaultValue());

        // The primary key field and some of it's properties
        $field = $table->getField( $table->getPrimaryKeys()[0] );
        $this->assertInstanceOf('Metrol\DBTable\Field\PostgreSQL\Integer', $field);
        $this->assertEquals('primaryKeyID', $field->getName());
        $this->assertTrue($field->isPrimaryKey());
    }
}
