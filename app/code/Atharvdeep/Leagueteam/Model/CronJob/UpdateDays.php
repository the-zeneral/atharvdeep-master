<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Atharvdeep\Leagueteam\Model\CronJob;

use Magento\Store\Model\StoresConfig;
use Magento\Sales\Model\Order;

class UpdateDays
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    protected $scopeConfig;
    protected $orderManagement;
    protected $objectManager;
    /**
     * @param StoresConfig $storesConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->orderCollectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->objectManager = $objectManager;
        $this->leagueFactory = $leagueFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $league =$this->leagueFactory->create();
        $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('atharvdeep_leagueteam');
        /**
         *Setting silver manager
         */
        //Select Data from table
        $sql = "Select * FROM " . $tableName." where (LENGTH(level2) - LENGTH(REPLACE(level2, ',', ''))+1) = 5 and  (LENGTH(level3) - LENGTH(REPLACE(level3, ',', ''))+1) = 25  and floor(TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`))/86400) <= 25 and manager not like 'Silver'";
        $silver = $connection->fetchAll($sql);
        foreach ($silver as $value) {
            $league->load($value['pk']);
            $league->setData('manager', 'Silver');
            $league->setPk($value['pk']);
            $league->save();
        }

        /**
         *Setting gold manager
         */

        $sql = "Select * FROM " . $tableName." where floor(TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`))/86400) <= 100 and child_total > 5000 and manager not like 'Gold'";
        $gold = $connection->fetchAll($sql);
        foreach ($gold as $value) {
            $league->load($value['pk']);
            $league->setData('manager', 'Gold');
            $league->setPk($value['pk']);
            $league->save();
        }
        /**
         *Setting Diamond manager
         */

        $sql = "Select * FROM " . $tableName." where floor(TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`))/86400) <= 200 and child_total > 25000 and manager not like 'Diamond'";
        $platinum = $connection->fetchAll($sql);
        foreach ($platinum as $value) {
            $league->load($value['pk']);
            $league->setData('manager', 'Diamond');
            $league->setPk($value['pk']);
            $league->save();
        }
         /**
         *Setting Platinum manager
         */

        $sql = "Select * FROM " . $tableName." where child_total > 50000 and manager not like 'Platinum'";
        $platinum = $connection->fetchAll($sql);
        foreach ($platinum as $value) {
            $league->load($value['pk']);
            $league->setData('manager', 'Platinum');
            $league->setPk($value['pk']);
            $league->save();
        }
        $this->logger->debug(__METHOD__);
    }
}
