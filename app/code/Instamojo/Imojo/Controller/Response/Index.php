<?php
namespace Instamojo\Imojo\Controller\Response;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Instamojo\Imojo\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\Order\Payment\Transaction;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_objectmanager;
    protected $_checkoutSession;
    protected $_orderFactory;
    protected $urlBuilder;
    private $logger;
    protected $response;
    protected $config;
    protected $messageManager;
    protected $transactionRepository;
    protected $cart;
    protected $inbox;
     
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        Http $response,
        TransactionBuilder $tb,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\AdminNotification\Model\Inbox $inbox,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {

      
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->response = $response;
        $this->config = $scopeConfig;
        $this->transactionBuilder = $tb;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->inbox = $inbox;
        $this->transactionRepository = $transactionRepository;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                            ->get('Magento\Framework\UrlInterface');
        
        parent::__construct($context);
    }

    public function execute()
    {
        $payment_id = $this->getRequest()->getParam('payment_id');
        $payment_request_id = $this->getRequest()->getParam('id');
        $storedPaymentRequestId = $this->checkoutSession->getPaymentRequestId();
        
        if ($payment_id and $payment_request_id) {
            $this->logger->info("Callback called with payment ID: $payment_id and payment request ID : $payment_request_id ");
      
            if ($payment_request_id != $storedPaymentRequestId) {
                $this->logger->info("Payment Request ID not matched  payment request stored in session (".$this->session->data['payment_request_id'].") with Get Request ID $payment_request_id.");
                $this->_redirect($this->urlBuilder->getBaseUrl());
            }
     
            try {
                # get Client credintials from configurations.
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $client_id = $this->config->getValue("payment/instamojo/client_id", $storeScope);
                $client_secret = $this->config->getValue("payment/instamojo/client_secret", $storeScope);
                $testmode = $this->config->getValue("payment/instamojo/instamojo_testmode", $storeScope);
                $this->logger->info("Client ID: $client_id | Client Secret : $client_secret | Testmode: $testmode");
                
                # use instamojo library
                $ds = DIRECTORY_SEPARATOR;
                include __DIR__ . "$ds..$ds..$ds/lib/Instamojo.php";
                $api = new \Instamojo($client_id, $client_secret, $testmode);
                
                # fetch transaction status from instamojo.
                $response = $api->getOrderById($payment_request_id);
                $this->logger->info("Response from server for PaymentRequest ID $payment_request_id ".PHP_EOL .print_r($response, true));
                $payment_status = $api->getPaymentStatus($payment_id, $response->payments);
                $this->logger->info("Payment status for $payment_id is $payment_status");
                
                if ($payment_status === "successful" or  $payment_status =="failed") {
                    $this->logger->info("Response from server is $payment_status.");
                    $orderId = $response->transaction_id;
                    $orderId = explode("-", $orderId);
                    $orderId = $orderId[1];
                    $this->logger->info("Extracted order id from trasaction_id: ".$orderId);
                    
                    # get order and payment objects
                    $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                    $payment = $order->getPayment();
                    //print_R($payment);
                    
                    
                    if ($order) {
                        if ($payment_status == "successful") {
                            //$payment->setTransactionId($payment_id);
                              
                            //$trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,null,true);
                            $order->setState(Order::STATE_NEW)
                            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
                            
                            $transaction = $this->transactionRepository->getByTransactionId(
                                "-1",
                                $payment->getId(),
                                $order->getId()
                            );
                            if ($transaction) {
                                 $transaction->setTxnId($payment_id);
                                $transaction->setAdditionalInformation(
                                    "Instamojo Transactio Id",
                                    $payment_id
                                );
                                $transaction->setAdditionalInformation(
                                    "status",
                                    "successfull"
                                );
                                $transaction->setIsClosed(1);
                                $transaction->save();
                            }
                            //exit;
                            
                             $payment->addTransactionCommentsToOrder(
                                 $transaction,
                                 "Transaction is completed succefully"
                             );
                            $payment->setParentTransactionId(null);
                            
                            # send new email
                            $order->setCanSendNewEmailFlag(true);
                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                            //echo $order->getState();
                            //echo $order->getStatus();
                            
                            $payment->save();
                            $order->save();
                            
                            $this->logger->info("Payment for $payment_id was credited.");
                              
                            $this->_redirect($this->urlBuilder->getUrl('checkout/onepage/success/', ['_secure' => true]));
                        } elseif ($payment_status == "failed") {
                            $transaction = $this->transactionRepository->getByTransactionId(
                                "-1",
                                $payment->getId(),
                                $order->getId()
                            );
                            $transaction->setTxnId($payment_id);
                            $transaction->setAdditionalInformation(
                                "Instamojo Transaction Id",
                                $payment_id
                            );
                            $transaction->setAdditionalInformation(
                                "status",
                                "successfull"
                            );
                            $transaction->setIsClosed(1);
                            $transaction->save();
                            $payment->addTransactionCommentsToOrder(
                                $transaction,
                                "The transaction is failed"
                            );
                            try {
                                $items = $order->getItemsCollection();
                                foreach ($items as $item) {
                                    $this->cart->addOrderItem($item);
                                }
                                $this->cart->save();
                            } catch (Exception $e) {
                                $message = $e->getMessage();
                                $this->logger->info("Not able to add Items to cart Exception MEssage".$message);
                            }
                            $order->cancel();

                            $payment->setParentTransactionId(null);
                            $payment->save();
                            $order->save();
                            $this->logger->info("Payment for $payment_id failed.");
                            $this->_redirect($this->urlBuilder->getUrl('checkout/cart', ['_secure' => true]));
                        }
                    } else {
                        $this->logger->info("Order not found with order id $orderId");
                    }
                }
            } catch (CurlException $e) {
                $this->logger->info($e);
                    $this->_redirect($this->urlBuilder->getBaseUrl());
            } catch (ValidationException $e) {
                // handle exceptions releted to response from the server.
                $this->logger->info($e->getMessage()." with ");
                # add message into inbox of admin if authorization error.
                if (stristr($e->getMessage(), "Authorization")) {
                    $this->inbox->addCritical("Instamojo Authoirization Error", "Please contact to instamojo for troubleshooting. ".$e->getMessage());
                }
                $this->logger->info(print_r($e->getResponse(), true)."");
                $method_data['errors'] = $e->getErrors();
            } catch (Exception $e) {
                $this->logger->info($e->getMessage());
                $this->logger->info("Payment for $payment_id was not credited.");
                $this->_redirect($this->urlBuilder->getBaseUrl());
            }
        } else {
            $this->logger->info("Callback called with no payment ID or payment_request Id.");
            $this->_redirect($this->urlBuilder->getBaseUrl());
        }
    }
}
