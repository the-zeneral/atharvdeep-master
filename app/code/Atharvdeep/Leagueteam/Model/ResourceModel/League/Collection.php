<?php

namespace Atharvdeep\Leagueteam\Model\ResourceModel\League;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Atharvdeep\Leagueteam\Model\League', 'Atharvdeep\Leagueteam\Model\ResourceModel\League');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
