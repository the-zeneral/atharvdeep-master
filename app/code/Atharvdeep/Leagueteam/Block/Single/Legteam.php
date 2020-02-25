<?php
namespace Atharvdeep\Leagueteam\Block\Single;

use Magento\Framework\Exception\NoSuchEntityException;

class Legteam extends \Magento\Framework\View\Element\Template
{
    protected $leagueFactory;
    protected $registry;
    protected $objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->leagueFactory = $leagueFactory;
        $this->objectManager = $objectManager;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
        //get collection of data
        $this->pageConfig->getTitle()->set(__('Single League Team'));
    }
    public function getMemberCollection()
    {
        $memberId = $this->getMemberId();
        $collection = $this->leagueFactory->create()->getCollection()
                                            ->addFieldToSelect('*')
                                            ->addFieldToFilter('member_id', array('eq' => $memberId));
        return $collection;
    }
    public function getCollection()
    {

        //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;
        $memberId = $this->getMemberId();
        $collection = $this->leagueFactory->create()->getCollection()
                                            ->addFieldToSelect('*')
                                            ->addFieldToFilter('path', array('regexp' => $memberId.'[\\]'))
                                             ->setPageSize($pageSize)
                                            ->setCurPage($page);
         return $collection;
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
   
    public function getMemberId()
    {
        return $this->getCustomer()->getCustomAttribute('member_id')->getValue();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'singleleg.team.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }
  
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getLevelTwoMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level2');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelThreeMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level3');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelFourMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level4');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelFiveMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level5');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelSixMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level6');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelSevenMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level7');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelEightMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level8');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelNineMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level9');
        return $memberListArr = explode(",", $memberList);
    }
    public function getLevelTenMember()
    {
        $memberList = $this->getMemberCollection()->getFirstItem()->getData('level10');
        return $memberListArr = explode(",", $memberList);
    }
}
