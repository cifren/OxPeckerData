<?php

namespace Cifren\OxPeckerDataBundle\ETL\SQL\DataSource;

class ORMDataSourceType extends DataSource
{
    const REGULAR_TABLE = 0;

    /**
     * diff between temporary and derived tables : http://www.sql-server-performance.com/2002/derived-temp-tables/.
     */
    const TEMPORARY_TABLE = 1;

    const DERIVED_TABLE = 2;
}
