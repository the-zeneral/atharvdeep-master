<?php
namespace Atharvdeep\Leagueteam\Model;

class League extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Atharvdeep\Leagueteam\Model\ResourceModel\League');
    }
}
