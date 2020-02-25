<?php
/**
 * Copyright (c) 2018 BrainActs Commerce OÜ, All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace BrainActs\RewardPoints\Observer\BV;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class MemberBvDistribution
 *
 * @author BrainActs Core Team <support@brainacts.com>
 */
class MemberBvDistribution implements ObserverInterface
{

    /**
     * @var \BrainActs\RewardPoints\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \BrainActs\RewardPoints\Helper\Data
     */
    private $helper;

    /**
     * CustomerRegisterSuccess constructor.
     * @param \BrainActs\RewardPoints\Model\HistoryFactory $historyFactory
     * @param \Psr\Log\LoggerInterface $loger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \BrainActs\RewardPoints\Helper\Data $helper
     */
    public function __construct(
        \BrainActs\RewardPoints\Model\HistoryFactory $historyFactory,
        \Psr\Log\LoggerInterface $loger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \BrainActs\RewardPoints\Helper\Data $helper,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->historyFactory = $historyFactory;
        $this->loger = $loger;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->leagueFactory = $leagueFactory;
        $this->objectManager =$objectManager;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId = $observer->getData('customer_id');
        //$memberId = $observer->getData('member_id');
        $memberColl = $this->leagueFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
        $memberPath = $memberColl->getFirstItem()->getPath();
        $levelMembers = explode("\\", $memberPath);
        $membernos = count($levelMembers);
        try {
            foreach ($levelMembers as $key => $value) {
                $level = 'level'.$membernos;
                if ($membernos <= 10 && $membernos > 1) {
                    $points = $this->helper->getBvLevelPoints($level);
                    if (empty($points)) {
                         return $this;
                    }
                    /**
                     * @var \BrainActs\RewardPoints\Model\History $model
                     */
                    $model = $this->historyFactory->create();
                    $model->setCustomerId($this->getCustomerId($value));
                    $model->setCustomerName(implode(', ', $this->getCustomerName($value)));
                    $model->setPoints($points);
                    $model->setRuleName(__('Registration'));
                    $model->setStoreId($this->storeManager->getStore()->getId());
                    $model->setTypeRule(3);
                    $model->save();
                }
                $membernos--;
            };
        } catch (\Exception $e) {
            $this->loger->critical($e);
        }
        return $this;
    }

    public function getCustomerId($memberId)
    {
        $memberObj = $this->objectManager->get('Magento\Customer\Model\Customer')
                        ->getCollection()
                        ->addFieldToFilter('member_id', $memberId);
        $id = $memberObj->getFirstItem()->getId();
        return $id;
    }

    public function getCustomerName($memberId)
    {
        $memberObj = $this->objectManager->get('Magento\Customer\Model\Customer')
                        ->getCollection()
                        ->addFieldToFilter('member_id', $memberId);
        $name = [$memberObj->getFirstItem()->getFirstname(), $memberObj->getFirstItem()->getLastname()];
        return $name;
    }
}
