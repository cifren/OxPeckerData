This Example will show a complete example of OxPeckerData.

Your config :

```php
<?php

namespace Project\DataTierBundle\OxPeckerConfiguration;

use Cifren\OxPeckerDataBundle\Definition\DataConfiguration;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Cifren\OxPeckerDataBundle\DataSource\DataSourceManager;
use Cifren\OxPeckerDataBundle\Definition\Context;
use Doctrine\ORM\EntityManager;
use Cifren\OxPeckerDataBundle\ETL\Core\SqlETLProcess;

/**
 * config to create RptIngredientUsageDaily
 * 
 */
class IngredientUsageDataConfiguration extends DataConfiguration
{

    const MAX_PERIOD_HISTORY = '3Y';

    protected $entityManager;

    public function __construct(Registry $doctrine)
    {
        $this->setEntityManager($doctrine->getManager());
    }

    /**
     * authorized arguments
     *
     * @return array
     */
    public function setParamsMapping()
    {
        //if argument is not given during the command call, default value will be null or as defined below
        $mapping = array(
            'store_id' => null,
            'week_id' => null,
            'plating_id' => null,
            'ingredient_id' => null
        );

        return $mapping;
    }

    /**
     * - Create table RptIngredientUsageDaily if doesnt exist
     * - Prepare weeks need to be updated, get last modified week limited to  self::MAX_YEAR_HISTORY, if args doesnt contain week_id
     * - Delete all items selected from arguments
     *
     * @param \Cifren\OxPeckerDataBundle\Definition\Context $context
     */
    public function preProcess(Context $context)
    {
        //DataSourceManager can help you to manage your tables, based on entity definition
        $dataSourceManager = new DataSourceManager();
        $dataSourceManager->setEntityManager($this->getEntityManager());
        $dataSourceManager->setLogger($this->getLogger());        
        $dataSourceManager->createTable('RptIngredientUsageDaily');

        //get arguments from the command
        $args = $context->getArgs();

        //the config will adapt if there is or not arguments week_id
        if (empty($args['week_id'])) {
            $args['week_ids'] = $this->getWeekMissingFromYesterday($args);
        } else {
            $args['week_ids'] = array($args['week_id']);
        }
        unset($args['week_id']);

        //delete current week in order to add a new information for this week
        $this->deleteRptIngredientUsageDailyFromArgs($args);

        //if you change values of the arguments you can change it back here for the next function, getETLProcesses and postProcess
        //in this case we will change the week
        $context->setArgs($args);
    }

    /**
     * Will create tables from entity in dataSource and fill up with the SQL|Query
     *
     * The last Entity RptIngredientUsageDaily is the final form of the report table
     *
     * @param  \Cifren\OxPeckerDataBundle\Definition\Context       $context
     * @return array
     */
    public function getETLProcesses(Context $context)
    {
        //gets arguments after preProcess changed the week_id
        $args = $context->getArgs();

        //prepares tables, and simply them
        $etlProcesses = array(
            new SqlETLProcess(
                    //like said earlier, this class can accept Raw SQL, Doctrine QueryBuilder or Doctrine Query
                    $this->getEntityManager()->getRepository('store')->getQbActiveStores($args['store_id'])->select('s.storeId, s.storeName', 's.microsId', 's.posRegionId'), 
                    //entityName
                    'RptUsageStore', 
                    //mapping betWeen QueryBuilder and the entity
                    array('id', 'displayName', 'microsId', 'posRegionId')
            ),
            new SqlETLProcess(
                    $this->getEntityManager()->getRepository('weeklyCalendar')->getQbAllOrSelectedweeklyCalendar($args['week_ids'])->select('w.weekId, w.startDate, w.endDate'), 
                    'RptUsageweeklyCalendar', 
                    array('id', 'startDate', 'endDate')
            ),
            new SqlETLProcess(
                    $this->getQbRptRecipeExploded($args['plating_id'], $args['ingredient_id']), 
                    'RptUsageRptRecipeExploded', 
                    array('id', 'parentStockbook', 'childStockbook', 'MeasurePacksize', 'factorQty', 'metricQty', 'metricType')
            ),
            new SqlETLProcess(
                    "SELECT id, display_name, default__measure_pack_size_id, is_am_pm_prep FROM {$this->getTableName('Stockbook')}", 
                    'RptUsageStockbook', 
                    array('id', 'displayName', 'defaultMeasurePacksize', 'isAmPmPrep')
            ),
        );
        
        //The database has several tables changing based on the period, need to gather all information in one table
        $periods = $this->getEntityManager()->getRepository('weeklyCalendar')->getPeriodFromWeekIds($args['week_ids']);
        foreach ($periods as $key => $weeklyCalendar) {
            $etlProcesses[] = new SqlETLProcess(
                    $this->getSqlSalesWithWeekId($weeklyCalendar), 
                    'RptUsageSalesWithWeek', 
                    array('weeklyCalendar', 'miId', 'businessDate', 'store'), 
                    //this option depending if the call is done first or not, will drop the table on first call or not after the first execution
                    ($key == 0) ? array('dropOnInit' => true) : array('dropOnInit' => false)
            );
        }

        $etlProcesses[] = new SqlETLProcess(
                $this->getSqlSalesPerDay(), 
                'RptUsageSalesPerDay', 
                array('weeklyCalendar', 'platingStockbook', 'itemSold', 'businessDate', 'store')
        );

        //final table where all data is stored and the report will gets its information to display on the screen
        $etlProcesses[] = new SqlETLProcess(
                $this->getQbRptUsage(), 
                'RptIngredientUsageDaily', 
                array('numSold', 'usageDate', 'weeklyCalendar', 'store', 'storeName', 'metricMeasureQty', 'metricMeasureType', 'parentStockbook', 'childStockbook', 'amPm', 'MeasurePacksize', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'), 
                //this table will never be dropped on init
                array('dropOnInit' => false)
        );

        //give back the list of ETLProcess you want the core to execute
        return $etlProcesses;
    }

    /**
     * Delete all temporary tables used only to create RptIngredientUsageDaily
     *
     * @param \Cifren\OxPeckerDataBundle\Definition\Context $context
     */
    public function postProcess(Context $context)
    {
        $dataSourceManager = new DataSourceManager();
        $dataSourceManager->setEntityManager($this->getEntityManager());
        $dataSourceManager->setLogger($this->getLogger());

        //At the end of the process, you want maybe delete all temporary tables, we could use as well an option to create Temporary Table on SQL directly but it is not build yet
        $dataSourceManager->dropTable('RptUsageStore');
        $dataSourceManager->dropTable('RptUsageweeklyCalendar');
        $dataSourceManager->dropTable('RptUsageRptRecipeExploded');
        $dataSourceManager->dropTable('RptUsageStockbook');
        $dataSourceManager->dropTable('RptUsageSalesWithWeek');
        $dataSourceManager->dropTable('RptUsageSalesPerDay');
    }
    
    //*********************************************************************
    //BACKGROUND FUNCTIONS AFTER THIS POINT, USEFUL ONLY FOR BUSINESS LOGIC
    //HERE ONLY FOR EXAMPLE
    //*********************************************************************
    
    /**
     * merge week and sales, in order to obtain a week id for each row
     *
     * @param  \Fuller\FullerGroupBundle\Entity\\weeklyCalendar $weeklyCalendar
     * @return string
     */
    protected function getSqlSalesWithWeekId(weeklyCalendar $weeklyCalendar)
    {
        $database = $this->getEntityManager()->getConnection()->getDatabase();
        $periodId = str_pad($weeklyCalendar->getPeriod(), 2, "0", STR_PAD_LEFT);

        $this->getLogger()->notice("DataSource created for 'sal_detail_ye{$weeklyCalendar->getYearEnd()}_p{$periodId}'");

        return $sql = "
            SELECT
                w.id,
                sdyp.mi_id,
                sdyp.business_date,
                sdyp.store_id
            FROM {$database}.sal_detail_ye{$weeklyCalendar->getYearEnd()}_p{$periodId} sdyp
                INNER JOIN {$this->getTableName('RptUsageweeklyCalendar')} w ON sdyp.business_date >= w.start_date AND sdyp.business_date <= w.end_date
                INNER JOIN {$this->getTableName('RptUsageStore')} s ON sdyp.store_id = s.id
        ";
    }

    /**
     * Group sales by pp_stockbook_id and business_date and count rows, get the total sold
     *
     * @return string
     */
    protected function getSqlSalesPerDay()
    {
        return $sql = "
            SELECT
                sdyp._weekly_calendar_id,
                pps1.stockbook_id as stockbook_id,
                count(pps1.stockbook_id) as item_sold,
                sdyp.business_date,
                s.id
            FROM rpt_usage_sales_with_week sdyp
                INNER JOIN {$this->getTableName('RptUsageStore')} s ON sdyp._store_id = s.id
                INNER JOIN {$this->getTableName('Stockbook')} as pps1 ON pps1.id = sdyp.stockbook_id
            GROUP BY pps1.stockbook_id, sdyp.business_date
            ORDER BY sdyp.business_date
        ";
    }

    /**
     * Join all tables in order to create a table RptIngredientUsageDaily regrouping all data
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQbRptUsage()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select("
                    sl.itemSold,
                    sl.businessDate,
                    w.id as weekId,
                    fs.id as storeId,
                    fs.displayName as storeName,
                    re.metricQty,
                    re.metricType,
                    ppp.id as parentStockbookId,
                    cpp.id as childStockbookId,
                    cpp.isAmPmPrep,
                    ps.id as childMeasurePacksizeId,
                    CURRENT_TIMESTAMP() as createdAt,
                    CURRENT_TIMESTAMP() as updatedAt,
                    '123' as createdBy,
                    '123' as updatedBy
                    ")
                ->from('RptUsageSalesPerDay', 'sl')
                ->innerJoin('sl.store', 'fs')
                ->innerJoin('sl.platingStockbook', 're')
                ->innerJoin('sl.weeklyCalendar', 'w')
                ->innerJoin('re.parentStockbook', 'ppp')
                ->innerJoin('re.childStockbook', 'cpp')
                ->innerJoin('cpp.defaultMeasurePacksize', 'ps')
        ;

        return $qb;
    }

    /**
     * getQbRptRecipeExploded
     *
     * @param  integer                    $platingId
     * @param  integer                    $ingredientId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQbRptRecipeExploded($platingId, $ingredientId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('p.id, ppp.id, cpp.id, fps.id, p.factorQty, p.metricQty, p.metricType')
                ->from('RptRecipeExploded', 'p')
                ->innerJoin('p.parentStockbook', 'ppp')
                ->innerJoin('p.childStockbook', 'cpp')
                ->innerJoin('p.MeasurePacksize', 'fps')
        ;

        if ($platingId) {
            $qb->where("parentStockbook = $platingId");
        }
        if ($ingredientId) {
            $qb->andWhere("childStockbook = $ingredientId");
        }

        return $qb;
    }

    //Depend on enity Manager selected, us or ca, will be earls or joeys or other, but always refering main database,
    //for example if em=cifrensus, name will be cifrensus.pp_stockbook
    //for example if em=cifren, name will be earls.pp_stockbook
    //for example if em=joeysus, name will be joeys.pp_stockbook
    protected function getTableName($entityName)
    {
        return $this->getEntityManager()->getClassMetadata($entityName)->getTableName();
    }

    /**
     * Get an array of weeks from the last date imported for the store if there is a store in args
     *
     * @param  array $args
     * @return array
     */
    protected function getWeekMissingFromYesterday(array $args)
    {
        $storeId = $args['store_id'];

        $week = $this->getLastImportedWeek($storeId);
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval("P1D"));

        $weeklyCalendars = $this->getEntityManager()
                ->getRepository('weeklyCalendar')
                ->getQueryBuildWeeksByStartAndEnd($week->getStartDate(), $yesterday)
                ->getQuery()
                ->getResult();

        $weeks = array();
        foreach ($weeklyCalendars as $week) {
            $weeks[] = $week->getWeekId();
        }

        return $weeks;
    }

    /**
     * get last imported week from RptIngredientUsageDaily table, based on store if there is
     *
     * @param  integer|null     $storeId
     * @return weeklyCalendar
     */
    protected function getLastImportedWeek($storeId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('pi')
                ->from('RptIngredientUsageDaily', 'pi')
                ->orderBy('pi.usageDate', 'DESC');

        if ($storeId) {
            $qb
                    ->where('pi.store = :storeId')
                    ->setParameter('storeId', $storeId);
        }

        $lastWeekIngUsage = $qb
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        $maxDateHistory = new \DateTime();
        $maxPeriodHistory = self::MAX_PERIOD_HISTORY;
        $maxDateHistory->sub(new \DateInterval("P{$maxPeriodHistory}"));
        $maxWeekHistory = $this->getEntityManager()->getRepository('weeklyCalendar')->getOneWeekByDate($maxDateHistory);

        $lastWeek = $maxWeekHistory;
        if ($lastWeekIngUsage && $lastWeekIngUsage->getweeklyCalendar()->getWeekId() > $maxWeekHistory->getWeekId()) {
            $lastWeek = $lastWeekIngUsage->getweeklyCalendar();
        }

        return $lastWeek;
    }

    /**
     * delete all rows based on arguments
     *
     * @param array $args
     */
    public function deleteRptIngredientUsageDailyFromArgs(array $args)
    {
        $qb = $this->getEntityManager()
                ->createQueryBuilder();

        $strWeeks = implode(',', $args['week_ids']);
        $qb
                ->delete('RptIngredientUsageDaily', 'ud')
                ->where("ud.weeklyCalendar IN({$strWeeks})");

        $platingId = $args['plating_id'];
        $ingredientId = $args['ingredient_id'];
        $storeId = $args['store_id'];
        if ($platingId) {
            $qb->andWhere("ud.parentStockbook = $platingId");
        }
        if ($ingredientId) {
            $qb->andWhere("ud.childStockbook = $ingredientId");
        }
        if ($storeId) {
            $qb->andWhere("ud.store = $storeId");
        }

        $qb->getQuery()->execute();
    }

}

```
