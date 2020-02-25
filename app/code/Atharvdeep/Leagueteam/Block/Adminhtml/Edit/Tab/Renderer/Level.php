<?php
 
namespace Atharvdeep\Leagueteam\Block\Adminhtml\Edit\Tab\Renderer;
 
use Magento\Framework\DataObject;
use Magento\Customer\Controller\RegistryConstants;

class Level extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->leagueFactory = $leagueFactory;
    }
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    /**
     * @return string|null
     */
    public function getMemberId()
    {
        $collection = $this->leagueFactory->create()->getCollection();
        $memberCollection = $collection->addFieldToFilter('customer_id', ['eq' => $this->getCustomerId()]);
        $memberId = $memberCollection->getFirstItem()->getData('member_id');
        return $memberId;
    }
    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $path = $row->getPath();
        $subStrPos = strpos($path, $this->getMemberId());
        $subStrPath = substr($path, $subStrPos);
        $level = substr_count($subStrPath, '\\');
        $resultLevel = $level+1;
      
        return "Level".$resultLevel;
    }
}
