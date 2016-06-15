# Metrol\DBTable
## Make working with Database tables easy

Was working with tables all that hard that it needed a library all of it's own?  Oh yeah.  Table structures can get real messy once you get past just simple integers and var chars.  Even those have attributes that your software should know before trying to assign values, like the alloweable ranges or character count.  Once you start working with Composite and Array types, things can get interesting.

What most libraries do is put the effort of manually coding the information about a table into entity classes.  Maybe you have something that generates those classes for you.  Either way, there's a perfectly good resource to tap into at run time to determine the basics about what you can assign to fields in a table... **The Database!**

## Features
- Can specify both the table name and the schema it belongs to.
- A cached *Bank* of tables can be used so you only look up information once.
- You can manually specify field information and assign it to a table without a database connection.
- Will be supporting multiple databases.  Please see status below.
- No external dependencies.

## Useage
You would generally start with creating a DBTable object like so...

```php
$table = new \Metrol\DBTable\PostgreSQL('ImportantData');

// At this point the table does not know about any fields.  You need to pass
// in an active PDO connection to have them looked up.

$table->runFieldLookup($pdo);

// Need to know the primary keys for the table?
$primaryKeys = $table->getPrimaryKeys();

// How about the structure of a specific field
$firstNameField = $table->getField('firstName');

// Is it okay to store a null in that field?
$nullOK = $firstNameField->isNullOk(); // Returns boolean true or false

// If null is not allowed, is there a default value that should be used?
$defaultValue = $firstNameField->getDefaultValue();

// This is a varchar, so knowing how many characters are allowed would be nice
$maxChar = $firstNameField->getMaxCharacters();

// For an integer field, you may want to know what the maximum allowed value is
$maxValue = $indexField=>getMax(); 
```

These are just a few of the early examples.  Each major field type is given it's own class to provide all the information you would need to know what is and is not allowed to go into it at run time.

## What this does not do (and never will)
There's no attempt to try to work through triggers or any database initiated validations.  Just looking at the basics here.  The complexity and load wouldn't be worth the efforts.

This library does not work directly with any of the values that will be stored in a table.  Instead, this is meant to be a tool used to assist other classes with handling values.

## Status
Things are still pretty raw at the moment.  Not ready for production as of 15Jun2016.

Primary focus is on getting field types for PostgreSQL properly recognized and parsed.  I don't know if I'll be able to achieve 100% type coverage, as some types shouldn't be messed with in userland anyway, but the more likely ones will be.
  
Once I feel things are stable with PostgreSQL, MySQL will be the next server on my agenda.  After that, not sure where I'll be taking this library too.

