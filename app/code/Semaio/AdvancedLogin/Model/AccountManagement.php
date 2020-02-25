<?php
/**
 * Copyright © 2016 Rouven Alexander Rieker
 * See LICENSE.md bundled with this module for license details.
 */
namespace Semaio\AdvancedLogin\Model;

use Magento\Customer\Model\AccountManagement as CustomerAccountManagement;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Config\Share;
use Semaio\AdvancedLogin\Model\ConfigProvider as AdvancedLoginConfigProvider;
use Semaio\AdvancedLogin\Model\Config\Source\LoginMode;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class AccountManagement
 *
 * @package Semaio\AdvancedLogin\Model
 */
class AccountManagement extends CustomerAccountManagement
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $advancedLoginConfigProvider;
    /**
     * @var mathRandom
     */
    private $mathRandom;

    /**
     * AccountManagement constructor.
     *
     * @param \Magento\Customer\Model\CustomerFactory                      $customerFactory
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\Math\Random                               $mathRandom
     * @param \Magento\Customer\Model\Metadata\Validator                   $validator
     * @param \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface             $addressRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface              $customerMetadataService
     * @param \Magento\Customer\Model\CustomerRegistry                     $customerRegistry
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Encryption\EncryptorInterface             $encryptor
     * @param \Magento\Customer\Model\Config\Share                         $configShare
     * @param \Magento\Framework\Stdlib\StringUtils                        $stringHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder            $transportBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor            $dataProcessor
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Customer\Helper\View                                $customerViewHelper
     * @param \Magento\Framework\Stdlib\DateTime                           $dateTime
     * @param \Magento\Customer\Model\Customer                             $customerModel
     * @param \Magento\Framework\DataObjectFactory                         $objectFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter         $extensibleDataObjectConverter
     * @param SearchCriteriaBuilder                                        $searchCriteriaBuilder
     * @param FilterBuilder                                                $filterBuilder
     * @param ConfigProvider                                               $advancedLoginConfigProvider
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Model\Metadata\Validator $validator,
        \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        AdvancedLoginConfigProvider $advancedLoginConfigProvider,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $customerRegistry,
            $logger,
            $encryptor,
            $configShare,
            $stringHelper,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $dataProcessor,
            $registry,
            $customerViewHelper,
            $dateTime,
            $customerModel,
            $objectFactory,
            $extensibleDataObjectConverter
        );

        $this->customerRepository = $customerRepository;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
        $this->advancedLoginConfigProvider = $advancedLoginConfigProvider;
        $this->mathRandom = $mathRandom;
        $this->addressRepository = $addressRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        try {
            switch ($this->advancedLoginConfigProvider->getLoginMode()) {
                case LoginMode::LOGIN_TYPE_ONLY_ATTRIBUTE:
                    $customer = $this->loginViaCustomerAttributeOnly($username);
                    break;
                case LoginMode::LOGIN_TYPE_BOTH:
                    $customer = $this->loginViaCustomerAttributeOrEmail($username);
                    break;
                default:
                    $customer = $this->loginViaEmailOnly($username);
                    break;
            }
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($customer->getCustomAttribute('account_is_active')) {
            if ($customer->getCustomAttribute('account_is_active')->getValue()== 0) {
                $value = $this->urlBuilder->getUrl('leagueteam/registration/joiningfee', ['customer_id' => $customer->getId()]);
                throw new LocalizedException(__(
                    'This account is not confirmed due to pending payment. <a href="%1">Click here</a> to payment against this account.',
                    $value
                ));
            }
        }
        $this->checkPasswordStrength($password);
        $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__('This account is not confirmed.'));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * Process login by email address
     *
     * @param string $username Username
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function loginViaEmailOnly($username)
    {
        return $this->customerRepository->get($username);
    }

    /**
     * Process login by customer attribute
     *
     * @param string $username Username
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     * @throws NoSuchEntityException
     */
    private function loginViaCustomerAttributeOnly($username)
    {
        $customer = $this->findCustomerByLoginAttribute($username);
        if (false == $customer) {
            throw new NoSuchEntityException();
        }

        return $customer;
    }

    /**
     * Process login by customer attribute or email
     *
     * @param string $username Username
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    private function loginViaCustomerAttributeOrEmail($username)
    {
        $customer = $this->findCustomerByLoginAttribute($username);
        if (false === $customer) {
            $customer = $this->customerRepository->get($username);
        }

        return $customer;
    }

    /**
     * Find a customer
     *
     * @param string $attributeValue Attribute Value
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    private function findCustomerByLoginAttribute($attributeValue)
    {
        // Retrieve the customer login attribute and check if valid
        $loginAttribute = $this->advancedLoginConfigProvider->getLoginAttribute();
        if (false === $loginAttribute) {
            return false;
        }

        // Add website filter if customer accounts are shared per website
        $websiteIdFilter = false;
        if ($this->advancedLoginConfigProvider->getCustomerAccountShareScope() == Share::SHARE_WEBSITE) {
            $websiteIdFilter[] = $this->filterBuilder
                ->setField('website_id')
                ->setConditionType('eq')
                ->setValue($this->storeManager->getStore()->getWebsiteId())
                ->create();
        }

        // Add customer attribute filter
        $customerNumberFilter[] = $this->filterBuilder
            ->setField($this->advancedLoginConfigProvider->getLoginAttribute())
            ->setConditionType('eq')
            ->setValue($attributeValue)
            ->create();

        // Build search criteria
        $searchCriteriaBuilder = $this->searchCriteriaBuilder->addFilters($customerNumberFilter);
        if ($websiteIdFilter) {
            $searchCriteriaBuilder->addFilters($websiteIdFilter);
        }
        $searchCriteria = $searchCriteriaBuilder->create();

        // Retrieve the customer collection and return customer if there was exactly one customer found
        $collection = $this->customerRepository->getList($searchCriteria);
        if ($collection->getTotalCount() == 1) {
            return $collection->getItems()[0];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }
        $customerIds = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSort('entity_id', 'DESC');
        $customerId = $customerIds->getFirstItem()->getId();
        // Associate member_id with customer
        if (!$customer->getCustomAttribute('member_id')) {
            $formattedNumber = sprintf('%05d', $customerId+1);
            $memberId = 'AVD'.$formattedNumber;
            $customer->setCustomAttribute('member_id', $memberId);
            $customer->setCustomAttribute('account_is_active', 0);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }
}
