<?php
/**
 * Copyright (c) 2018 BrainActs Commerce OÜ, All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace BrainActs\RewardPoints\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DB\Select;

/**
 * Class History
 * @author BrainActs Core Team <support@brainacts.com>
 */

class WithdrawVoucher extends index
{
  
    protected $points = 0;
    protected $customer_id;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        DataObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \BrainActs\RewardPoints\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \BrainActs\RewardPoints\Model\HistoryFactory $historyFactory
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $customerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $customerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $customerAccountManagement,
            $addressRepository,
            $customerDataFactory,
            $addressDataFactory,
            $customerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
    }
   
    /**
     * @return \Magento\Framework\View\Result\Layout
    */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->customer_id = $this->getRequest()->getParam('customer_id')) {
            $points = $this->getRewardPoints();
            $customer = $this->_customerRepository->getById($this->customer_id);
            $name = [$customer->getFirstname(), $customer->getLastname()];
            $withdraw_bv = (int)$this->getRequest()->getParam('withdraw_bv');
            $totalBv = $this->getRewardPoints();
            if (isset($withdraw_bv) && $totalBv >= $withdraw_bv) {
                try {
                    /** @var \BrainActs\RewardPoints\Model\History $model */
                    $model = $this->historyFactory->create();
                    $model->setCustomerId($this->customer_id);
                    $model->setCustomerName(implode(', ', $name));
                    $model->setPoints(-$withdraw_bv);
                    $model->setRuleName('Withdrawal Vouchers');
                    $model->setRuleSpendId(null);
                       // $model->setOrderId($order->getId());
                   //     $model->setOrderIncrementId($order->getIncrementId());
                    $model->setStoreId(1);
                    $model->setTypeRule(1);
                    $model->save();
                    $this->messageManager->addSuccess(__('You have Withdraw %1 Points.', $withdraw_bv));
                    $resultRedirect->setPath('customer/index/edit', ['id' => $this->customer_id, '_current' => true]);
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                    $resultRedirect->setPath('customer/index/edit', ['id' => $this->customer_id, '_current' => true]);
                }
            } else {
                    $this->messageManager->addError(__('You have not sufficient Points to withdrawal.', $withdraw_bv));
                    $resultRedirect->setPath('customer/index/edit', ['id' => $this->customer_id, '_current' => true]);
            }
        }
        return $resultRedirect;
    }

    public function getRewardPoints()
    {
        if ($this->points == null) {
            /** @var \BrainActs\RewardPoints\Model\ResourceModel\History\Collection $collection */
            $collection = $this->historyCollectionFactory->create();
            $collection->getSelect()->reset(Select::COLUMNS)
                ->columns(['total' => new \Zend_Db_Expr('SUM(points)')])->group('customer_id');
            $collection->addFieldToFilter('customer_id', ['eq' => $this->customer_id]);
            $collection->load();
            $item = $collection->fetchItem();
            if ($item) {
                $this->points = $item->getData('total');
            }
        }

        return $this->points;
    }
}
