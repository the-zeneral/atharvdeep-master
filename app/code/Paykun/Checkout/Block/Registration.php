<?php
namespace Paykun\Checkout\Block;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Paykun\Checkout\Logger\Logger;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Paykun\Checkout\Controller\Errors\ValidationException;
use Paykun\Checkout\Controller\Payment;
use Paykun\Checkout\Controller\Errors\Error;
use Paykun\Checkout\Controller\Errors\ErrorCodes;

class Registration extends \Magento\Framework\View\Element\Template
{
    const GATEWAY_URL_PROD = "https://checkout.paykun.com/payment";
    const GATEWAY_URL_DEV = "https://sandbox.paykun.com/payment";
    const SUCCESS_URL = "paykun_checkout_gateway/joining/success";
    const FAILED_URL = "paykun_checkout_gateway/joining/cancelled";
    const ALLOWED_CURRENCIES = ['INR'];

    protected $_objectmanager;
    protected $_checkoutSession;
    protected $_scopeConfig;
    protected $_encryptor;
    protected $orderFactory;
    protected $urlBuilder;
    protected $_logger;
    protected $response;
    protected $config;

    protected $_isError;
    protected $_errorMessage;
    protected $_errorCode;
    protected $_orderDetail;
    protected $_url;
    protected $_checkoutHelper;
    protected $_messageManager;
    protected $_countryFactory;
    protected $inbox;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Logger $logger,
        Http $response,
        \Magento\AdminNotification\Model\Inbox $inbox,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator,
        \Magento\Paypal\Helper\Checkout $checkoutHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface
    ) {

      
        $this->_checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->response = $response;
        $this->config = $context->getScopeConfig();
        $this->inbox = $inbox;
        $this->_encryptor           = $encryptor;
        $this->_countryFactory      = $countryFactory;
        $this->_agreementsValidator = $agreementValidator;
        $this->_checkoutHelper      = $checkoutHelper;
        $this->_messageManager      = $messageManager;
        $this->customerRepository   = $customerRepository;
        $this->customerSession      = $customerSession;
        $this->_url                 = $urlInterface;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                           ->get('Magento\Framework\UrlInterface');
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        $orderId = $this->getRealOrderId();
        if ($orderId) {
            //var_dump($trn);exit;
            $this->setOrderDetail($orderId);
            if (in_array($this->_orderDetail->getOrderCurrencyCode(), self::ALLOWED_CURRENCIES)) {
                try {
                    $formData = $this->getPaykunPaymentDetail($orderId);
                    $this->setEncrypt($formData['encrypted_request']);
                    $this->setAction($formData["gateway_url"]);
                    $this->setMerchant($formData['merchant_id']);
                    $this->setAccess($formData['access_token']);
                } catch (\CurlException $e) {
                    // handle exception releted to connection to the sever
                    $this->_logger->info((string)$e);
                    $method_data['errors'][] = $e->getMessage();
                } catch (\ValidationException $e) {
                    // handle exceptions releted to response from the server.
                    $this->_logger->info($e->getMessage()." with ");
                    if (stristr($e->getMessage(), "Authorization")) {
                        $inbox->addCritical("Paykun Authoirization Error", $e->getMessage());
                    }
                    $this->_logger->info(print_r($e->getResponse(), true)."");
                    $method_data['errors'] = $e->getErrors();
                } catch (\Exception $e) { // handled common exception messages which will not caught above.
                    $method_data['errors'][] = $e->getMessage();
                    $this->_logger->info('Error While Creating Order : ' . $e->getMessage());
                }
            } else {
                $this->_logger->info('Order with ID $orderId not found. Quitting :-(');
            }
        } else {
            $this->_logger->info('Order with ID $orderId not found. Quitting :-(');
        }
    }


    private function restoreCart($errorMsg)
    {

        $this->_checkoutHelper->cancelCurrentOrder($errorMsg);

     
        $orderId = $this->getRealOrderId();
        $customerId = $this->_orderDetail->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customer->setCustomAttribute('account_is_active', 0);
        $this->customerRepository->save($customer);
        $this->customerSession->logout();
        $this->_redirect($this->urlBuilder->getUrl('leagueteam/index/thankyou', ['order_id'=>$orderId]));
    }

    private function setOrderDetail($orderId)
    {

        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
        $orderData          = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $this->_orderDetail = $orderData;
    }

    private function getPaykunPaymentDetail($orderId)
    {

        try {
            $orderDetail = $this->getOrderDetail($orderId);
            //$this->debug($orderDetail, true);
            if ($orderDetail !== null) {
                $this->addLog('Order Id Testing => '.$orderId, true);

                $liveOrSandboxMode = $this->getConfig('is_live_or_sandbox') ? true : false;
                $obj = new Payment(

                    $this->getConfig('merchant_gateway_key'),
                    $this->getConfig('merchant_access_key', true),
                    $this->getConfig('merchant_encryption_key', true),
                    $liveOrSandboxMode,
                    true
                );

                // Initializing Order
                $obj->initOrder(

                    $orderDetail['orderId'],
                    $orderDetail['purpose'],
                    $orderDetail['amount'],
                    $orderDetail['successUrl'],
                    $orderDetail['failureUrl']
                );
                //$this->debug($orderDetail, true);
                // Add Customer
                $obj->addCustomer(

                    $orderDetail['customerDetail']['name'],
                    $orderDetail['customerDetail']['email'],
                    $orderDetail['customerDetail']['customerMono']
                );
                //$this->debug($orderDetail, true);
                // Add Shipping address

                $obj->addShippingAddress(

                    $orderDetail['shippingDetail']['country'],
                    $orderDetail['shippingDetail']['state'],
                    $orderDetail['shippingDetail']['city'],
                    $orderDetail['shippingDetail']['pinCode'],
                    $orderDetail['shippingDetail']['addressString']
                );

                // Add Billing Address
                $obj->addBillingAddress(

                    $orderDetail['billingDetail']['country'],
                    $orderDetail['billingDetail']['state'],
                    $orderDetail['billingDetail']['city'],
                    $orderDetail['billingDetail']['pinCode'],
                    $orderDetail['billingDetail']['addressString']
                );
                $obj->setCustomFields(['udf_1' => $orderId]);

                //get form data to submit
                return $obj->submit();
            }

            $this->_isError = true;
            $this->_errorMessage = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_STRING;
            $this->_errorCode = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_CODE;

            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);

            return null;
        } catch (ValidationException $e) {
            //$this->restoreCart($e->getMessage());
            $this->_isError = true;
            $this->_errorMessage = $e->getMessage();
            $this->_errorCode = $e->getCode();
            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);
            return null;
        }
    }

    private function getConfig($name, $isDecrypt = null)
    {

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $conf = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paykun_gateway/'.$name);
            if ($isDecrypt != null) {
                $conf = $this->_encryptor->decrypt($conf);
            }

            return $conf;
        } catch (ValidationException $e) {
            $this->_isError = true;
            $this->_errorMessage = ErrorCodes::INVALID_SYSTEM_CONFIG_STRING;
            $this->_errorCode = ErrorCodes::INVALID_SYSTEM_CONFIG_CODE;
            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);
            return null;
        }
    }

    private function getOrderIdForPaykun($orderId)
    {

        try {
            $orderNumber = str_pad((string)$orderId, 10, '0', STR_PAD_LEFT);
            return $orderNumber;
        } catch (ValidationException $e) {
            $this->_isError = true;
            $this->_errorMessage = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_STRING;
            $this->_errorCode = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_CODE;
            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);
        }
    }

    private function getRealOrderId()
    {

        try {
            $order = $this->_checkoutSession->getLastRealOrder();
            return $order->getId();
        } catch (ValidationException $e) {
            $this->_isError = true;
            $this->_errorMessage = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_STRING;
            $this->_errorCode = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_CODE;
            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);
            return null;
        }
    }

    private function getOrderDetail($orderId)
    {
        try {
            $orderData = $this->_orderDetail;
            $paykunOrderDetail = null;
            if (is_object($orderData) && $orderData->getId()!='') {
                $billingData    = (array)$orderData->getBillingAddress()->getData();
                $shippingData   = (array)$orderData->getBillingAddress()->getData();

                /*
                 * In magento Region or provience is not mandatory field so assign city to region when region is not
                    provided
                */
                $shippingDetail = [
                    'country'           => $this->getCountryname($shippingData['country_id']),
                    'state'             => (isset($shippingData['region'])) ? $shippingData['region'] : $billingData['city'],
                    'city'              => $shippingData['city'],
                    'pinCode'           => $shippingData['postcode'],
                    'addressString'     => $shippingData['street'],
                ];

                $billingDetail = [
                    'country'           => $this->getCountryname($billingData['country_id']),
                    'state'             => (isset($billingData['region'])) ? $billingData['region'] : $billingData['city'],
                    'city'              => $billingData['city'],
                    'pinCode'           => $billingData['postcode'],
                    'addressString'     => $billingData['street'],
                ];

                $customerDetail = [
                    'name'          => ($this->_orderDetail->getCustomerIsGuest())
                        ? $shippingData['firstname'].' '.$shippingData['lastname']
                        : $orderData->getCustomerName(),
                    'email'         => $orderData->getCustomerEmail(),
                    'customerMono'  => $shippingData['telephone'],
                ];

                $paykunOrderDetail = [

                    'orderId'           => $this->getOrderIdForPaykun($orderId),
                    'purpose'           => $this->getItemPurpose($orderData->getAllItems()),
                    'amount'            => $orderData->getBaseGrandTotal(),
                    'successUrl'        => $this->_url->getUrl(self::SUCCESS_URL),
                    'failureUrl'        => $this->_url->getUrl(self::FAILED_URL),
                    'customerDetail'    => $customerDetail,
                    'shippingDetail'    => $shippingDetail,
                    'billingDetail'     => $billingDetail,

                ];
            }
            return $paykunOrderDetail;
        } catch (ValidationException $e) {
            $this->_isError = true;
            $this->_errorMessage = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_STRING;
            $this->_errorCode = ErrorCodes::SESSION_ORDER_ID_NOT_FOUND_CODE;
            $this->addLog('Failed => ['.$this->_errorCode.'] => '. $this->_errorMessage, true);
            return null;
        }
    }

    private function getItemPurpose($orderedItems)
    {

        $itemPurpose = "";
        $numItems = count($orderedItems);
        $currentCount = 0;

        foreach ($orderedItems as $item) {
            $extraStuff = ', ';

            if (++$currentCount === $numItems) {
                $extraStuff = '';
            }

            $item_detail = (array) $item->getData();
            $itemPurpose .= $item_detail['name'].$extraStuff;
        }
        return $itemPurpose;
    }

    private function getCountryname($countryCode)
    {

        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    private function addLog($log, $isError = false)
    {

        if ($this->getConfig('debug')) {
            if (!$isError) {
                $this->_logger->addInfo($log);
            } else {
                $this->_logger->addError($log);
            }
        }
    }
}
