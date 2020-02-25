<?php

namespace Atharvdeep\Leagueteam\Model;

class LeagueFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Atharvdeep\Leagueteam\Model\League', $arguments, false);
    }
}
