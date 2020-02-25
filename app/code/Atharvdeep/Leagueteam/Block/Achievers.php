<?php
namespace Atharvdeep\Leagueteam\Block;

class Achievers extends \Magento\Framework\View\Element\Template
{
    protected $leagueFactory;
        
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
    ) {
        $this->leagueFactory = $leagueFactory;
        parent::__construct($context);
    }
    
    public function getManagerCollection()
    {
        $collection = $this->leagueFactory->create()->getCollection();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('manager', array('neq' => null));
      //  $collection->setPageSize(3); // fetching only 3 products
        return $collection;
    }
}
