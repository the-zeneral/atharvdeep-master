<?php
namespace Atharvdeep\Leagueteam\Block\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;

class Thankyou extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        Http $response,
        TransactionBuilder $tb
    ) {

      
        $this->_checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->response = $response;
        $this->config = $context->getScopeConfig();
        $this->transactionBuilder = $tb;
        $this->logger = $logger;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magento\Framework\UrlInterface');
        parent::__construct($context);
        $this->pageConfig->getTitle()->set(__('Thankyou'));
    }

     public function getOrderId()
     {
     	return $this->getRequest()->getParam('order_id');
     }
  
     public function getOrder()
    {
    	$orderId = $this->getOrderId();
        return  $this->orderFactory->create()->loadByIncrementId($orderId);
    }

   
     public function getOrderStatus()
     {
        return $this->getOrder()->getStatus();
     }
       public function getRetryLink()
     {
        $order = $this->getOrder();
        $customerId =$order->getCustomerId();
        return $this->urlBuilder->getBaseUrl().'leagueteam/registration/joiningfee/customer_id/'.$customerId;
     }
}
