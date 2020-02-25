<?php

namespace Atharvdeep\Leagueteam\Observer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Phrase;

class CustomerRegisterBefore implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
    */
    protected $objectManager;
    protected $request;
    protected $redirect;
    protected $actionFlag;
    protected $messageManager;
    /**
     * [__construct description]
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository [description]
     * @param \Magento\Framework\App\RequestInterface           $request            [description]
     * @param ObjectManagerInterface                            $objectManager      [description]
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\RequestInterface $request,
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->redirect = $context->getRedirect();
        $this->actionFlag = $context->getActionFlag();
        $this->messageManager = $context->getMessageManager();
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $customer = $observer->getEvent()->getCustomer();
        $sponsorId = $this->request->getPost('sponsor_id') ? $this->request->getPost('sponsor_id') : 0 ;
        if ($sponsorId == 0) {
            return;
        }

        // $customerObj = $this->objectManager->
        //                                 get('Magento\Customer\Model\Customer')
        //                                 ->load($sponsorId);
        // $sponsorName = $customerObj->getFirstName();
        $league = $this->objectManager->get('Atharvdeep\Leagueteam\Model\League');
                $model = $league->getCollection()
                                 ->addFieldToSelect('member_id')
                                 ->addFieldToSelect('child_count')
                                 ->addFieldToFilter('member_id', $sponsorId);
            $childCount = $model->getFirstItem()->getChildCount();

        if ($childCount==null) {
            $this->messageManager->addError(
                new Phrase('Your sponsorId is Not register with us')
            );
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), 'customer/account/create');
        } elseif ($childCount == 5) {
             $this->messageManager->addError(
                 new Phrase('Your sponsorId is having reached maximum member.')
             );
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), 'customer/account/create');
        }
        return ;
    }
}
