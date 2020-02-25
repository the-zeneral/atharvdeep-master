<?php
namespace Atharvdeep\Leagueteam\Model\ResourceModel;

class League extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('atharvdeep_leagueteam', 'pk');
    }
}
