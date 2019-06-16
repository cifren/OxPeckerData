Here a simple example of `report table` creation. 

In this Example, we will display an ingredient list (this example is really basic but can be replicate on more complicated case.

You need tables structure :
  
  Table `ingredient`:

    id  |ing_name   |fk_recipe_id
    ----|-----------|------------
    1   |asparag    |1
    2   |salt       |1
    3   |olive      |2
    
  Table `recipe`:

    id  |recipe_name
    ----|-----------
    1   |aspa plate    
    2   |olive plate    
    
  Table `rpt_recipe_ing`:
  
    id      |rpt_recipe_name |rpt_ingredient_name
    --------|----------------|-------------------
    null    |null            |null


The folder structure for the example :

```
-- src/
---- Project/
------ Entity
-------- RptRecipeIng
------ DataTierBundle/
-------- OxPeckerConfiguration/
---------- FirstReportDataConfiguration.php
```

First create your entity `RptRecipeIng` based on the structure of the table `rpt_recipe_ing`

Config file
========
Create your `FirstReportDataConfiguration file` :

```php

namespace Project\DataTierBundle\OxPeckerConfiguration;

use Cifren\OxPeckerData\Definition\DataConfiguration;
use Cifren\OxPeckerData\Definition\Context;

/**
 *  Setup config
 
 *  Project\DataTierBundle\OxPeckerConfiguration\FirstReportDataConfiguration
 */
class FirstReportDataConfiguration extends DataConfiguration
{

    public function preProcess(Context $context)
    {
        //what you want
    }
    
    /**
     * authorized arguments
     *
     * @return array
     */
    public function getETLProcesses(Context $context)
    {
        $etlProcesses = array(
            //choose your ETLProcess
            new SqlETLProcess(
                'SELECT ing_name, recipe_name '. //Transformation
                'FROM ingredient i JOIN recipe r ON i.fk_recipe_id = r.id', //FROM: Extraction
                'RptRecipeIng', //TO : Load, where you want to send the data
                array('rptRecipeName', 'rptIngredientName') //MAPPING: From the SELECT statement to the entity, matching fields from SQL statement to the entity
            ),
        );

        return $etlProcesses;
    }
    
    /**
     * authorized arguments
     *
     * @return array
     */
    public function postProcess(Context $context)
    {
        //what you want
    }
}
```

You have 2 options for ETLProcess:
- IterationETLProcess: Work from any type of file to any type of file, slower but more possibility. See more [here](iterationETLProcess.md)
- SqlETLProcess: work only on database, form a table to another, fast and easy to use via SQL solutions. See more [here](sqlETLProcess.md)

####TIPS
    All of this Processes depends on your needs, if you work only on database, SqlETLProcess will be your choice, fast and easy to use.
    If you extract data from CSV to Database, you choice will be IterationETLProcess. CsvExtractor + ObjectTransformer + ORMLoader/DBALLoader
    For complexe structure, a mix between IterationETLProcess at first, after SqlETLProcess for heavy transformation, and finally IterationETLProcess for other details

And that's it your config is done.

In the constructor of `SqlETLProcess` you can use `Raw sql` statement, `Doctrine\ORM\Query` or `Doctrine\DBAL\Query\QueryBuilder`.

As you can see, **ETL** is still used :
- **Extraction** : By the FROM
- **Transformation** : By the SELECT
- **Load** : By The entity

##### *WARNING*:

    When the system will call `SqlETLProcess` the system will drop automatically the entity table and recreate this table. If you don't want this default behaviour, just add the option `dropOnInit`
    
```php 
    new SqlETLProcess(
            'SELECT ing_name, recipe_name 
             FROM ingredient i JOIN recipe r ON i.fk_recipe_id = r.id', 
            'RptRecipeIng', 
            array('rptRecipeName', 'rptIngredientName'), 
            array('dropOnInit' => false)
        ),
```


Create your service:
```yaml
parameters:
    datatier.recipe.class:      Project\DataTierBundle\OxPeckerConfiguration\FirstReportDataConfiguration

services:
    datatier.recipe:
        class:      %datatier.recipe.class%
```

The name of your service `datatier.recipe`, will be the key of the call from the console.

Table type
==========

You can add an option when you create your `SqlETLProcess`, this option will give the type of 
table you want to use during the process. 

3 Types Exists :
- Regular table (default when null)
- Temporary table, create temporary table, full control over the table, you can 
redefined all indexes and all column you want to use. Heavier for the server.
- Derived table, create a derived table in your SQL statement which is a simple 
SubRequest, completely accepted by SQL engine and avoid useless database / hard drive work, can be very slow with full table though

For more information about the difference between temporary and derived table, see this page http://www.sql-server-performance.com/2002/derived-temp-tables/

When you use Derived table, you will have to use an alias in you sql statement in order to use this in your code 

Declare Regular table
```php
public function getETLProcesses(Context $context)
{
  //...
  $etlProcesses[] = new SqlETLProcess(
        $this->getSqlSalesPerWeek(), // sql statement, Extractor / Transformer
        'RptSalesPerWeek',  // Entity, Loader
        array('price', 'cost', 'contribution'), // Mapping SQL <=> Entity
        array('dropOnInit' => false)  //options
  );
  //...
}
```

Declare Temporary table
```php
public function getETLProcesses(Context $context)
{
  //...
  $etlProcesses[] = new SqlETLProcess(
        $this->getSqlSalesPerWeek(), 
        'RptSalesPerWeek', 
        array('price', 'cost', 'contribution'), // same thing for all those lines
        array('tableType' => ORMDataSourceType::TEMPORARY_TABLE) //Give types
  );
  //...
}
```

Temporary table will be used like Regular Table, at the difference where the database connection is close, Temporary are deleted automatically.

Use Regular / Temporary  table
```php
public function getETLProcesses(Context $context)
{
  //...

  //temporary table
  $etlProcesses[] = new SqlETLProcess(
        'SELECT priceItem, costItem, contributionItem, weekid, groupItemId FROM sales_table GROUP BY weekId', // can be QueryBuilder, Query or SQL
        'SalesPerWeek', 
        array('price', 'cost', 'contribution', 'weekId', 'groupId'), // same thing for all those lines
        array('tableType' => ORMDataSourceType::TEMPORARY_TABLE) //Give types
  );

  //regular table
  $etlProcesses[] = new SqlETLProcess(
        /* 
         * If you use QueryBuilder or Query, you will give the name of the entity, however if you use SQL, you will have 
         * to get the entity between %name%, automatically OxPecker will replace it by the tableName
         */
        'SELECT s.price, s.cost, s.contribution, g.name FROM %SalesPerWeek% as s INNER JOIN SalGroup as g ON s.groupItemId = g.id', 
        'RptSalesPerWeekAndGroup',
        array('price', 'cost', 'contribution'), // same thing for all those lines
        array('dropOnInit' => false) //type by default regular table
  );

  return $etlProcesses;
  //...
}
```

####TIPS 1:
    Each Temporary table will have unique ID in the database, this is because when you want 2 scripts at the same time you don't want to work on the same tables and cross the data.

####TIPS 2:
    OxPecker will modify the table name temporarily of the entity and put it back at the end of the data treatment.

Declare Derived table
```php
public function getETLProcesses(Context $context)
{
  //...
  $etlProcesses[] = new SqlETLProcess(
        $this->getSqlSalesPerWeek(), //Extractor/Transformer
        ORMDataSource::DERIVED_ALIAS . 'RptFgStore', //Name of the alias
        array(), // no mapping, no need
        array('tableType' => ORMDataSourceType::DERIVED_TABLE) //Give types
  );
  //...
}
```

Use Derived table
```php
public function getETLProcesses(Context $context)
{
  //...
  //derived table
  $etlProcesses[] = new SqlETLProcess(
        'SELECT priceItem, costItem, contributionItem, weekid, groupItemId FROM sales_table GROUP BY weekId', 
        ORMDataSource::DERIVED_ALIAS . 'RptFgStore', //Name of the alias, should always start with 'DerivedDataOx:' contained by the constant
        array(), // no mapping, no need
        array('tableType' => ORMDataSourceType::DERIVED_TABLE) //Give types
  );

  //regular table
  $etlProcesses[] = new SqlETLProcess(
        /* This SQL will depend on the structure of your Entity 'SalesPerWeek' 
         * You can call in your config the temporary table like this, same works for regular table
         * Can only be SQL statement but not QueryBuilder or Query when you use Derived tables Aliases
         */
        'SELECT s.priceItem, s.costItem, s.contributionItem, g.name FROM DerivedDataOx:RptFgStore as s INNER JOIN SalGroup as g ON s.groupItemId = g.id', 
        'RptSalesPerWeekAndGroup', 
        array('price', 'cost', 'contribution'), 
        array('dropOnInit' => false) //type by default regular table
  );

  return $etlProcesses;
  //...
}

```


Command
=======

From Symfony2 you can run your command which will use your config :
```shell
#generic usage
php app/console oxpecker:import service_name args
```
- service_name: it is the name of your service, in our case it is `datatier.recipe`

- args : it is the list of your arguments you want to send to your command, for example 
if you choose to give the Id of an ingredient, `ingredient_id=1582` for more details 
see [doc arguments](arguments.md)

Here some example of calls :

```shell
#example without param: (for a cron job for example)
php app/console oxpecker:run datatier.recipe

#example with param:
php app/console oxpecker:run datatier.recipe recipe_id=1

#example for help:
php app/console oxpecker:run datatier.recipe help

```
