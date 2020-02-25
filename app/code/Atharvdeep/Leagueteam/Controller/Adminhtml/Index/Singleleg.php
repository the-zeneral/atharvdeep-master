<?php
    /**
     * Shekhar Hello Hello Edit Tabs Admin Block
     *
     * @category    Webkul
     * @package     Atharvdeep_Leagueteam
     * @author      shekhar
     *
     */
namespace Atharvdeep\Leagueteam\Controller\Adminhtml\Index;
 
class Singleleg extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer compare grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
