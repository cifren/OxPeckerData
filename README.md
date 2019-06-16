OxPeckerData
============

[![Build Status](https://travis-ci.org/cifren/OxPeckerData.svg?branch=master)](https://travis-ci.org/cifren/OxPeckerData)
[![Coverage Status](https://coveralls.io/repos/github/cifren/OxPeckerData/badge.svg?branch=master)](https://coveralls.io/github/cifren/OxPeckerData?branch=master)

This Symfony2 bundle aims to provide commands to build your own report data, simply, 
organized and quickly. It can as well be used for importing data from one system to another.

OxPeckerData use a system of ETL via 2 solutions. 

First solution is using SQL, which give you a real fast and powerful tool to extract, 
transform and load a big amount of data, easily but it is limited to Sql functions. Oxpecker 
allow to organize quickly your commands.

Second solution is to use ETL using PHP, this method gives you unlimited possibility 
but it is kind of slow when you have to iterate each line of your data. This method is using [ETLknpBundle](https://github.com/docteurklein/php-etl).

The idea is to:

1. Create a configuration file defining ETL you want to use for the import data. (SQL/PHP ETL)
2. Launch the command via cron task, with parameters or not.


Theory
======

In your config you define :

- `Pre Process` method where you define all your command, for example you can do data 
deletion, data control, table structure etc...

- `Etl Processes` method where you define a list of ETL, of course when you use SQL ETL, 
this one will allow only import from SQL to SQL, but nothing forbidden to create a previous 
ETL to import CSV to SQL and after link this data to a table in SQL.
    
- `Post Process` method where you define all your command after Etls have been executed, 
for example delete temporary tables, test the data etc...

Your command will be based on the creation of a service for the declaration and the config you created.


Documentation
=============

For installation and how to use the bundle refer to [Resources/doc/index.md](Resources/doc/index.md)
