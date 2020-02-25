<?php
namespace Atharvdeep\Leagueteam\Controller\Ajax;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $leagueFactory;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->leagueFactory = $leagueFactory;
        parent::__construct($context);
    }
    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $memberId = $this->getRequest()->getPost('member_id');
        if ($memberId) {
            $collection = $this->leagueFactory->create()->getCollection()
                                        ->addFieldToSelect('member_name')
                                        ->addFieldToFilter('member_id', $memberId);
            $name = $collection->getFirstItem()->getMemberName();
        
            $member = $name?$name:null;
            $response=array('MemberName' => $member);
            return $result->setData($response);
        }
    }
}
