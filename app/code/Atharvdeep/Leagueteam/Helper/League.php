<?php
/**
 * Copyright © 2016 Rouven Alexander Rieker
 * See LICENSE.md bundled with this module for license details.
 */
namespace Atharvdeep\Leagueteam\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class League
 *
 * @package Atharvdeep\Leagueteam\Helper;
 */
class League extends AbstractHelper
{

    const XML_PATH_LEAGUETEAM = 'customer/leagueteam/active';
    /**
     * Login constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

  
    /**
     * Retrieve the configured login mode
     *
     * @return int
     */
    public function getLeagueteam()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_LEAGUETEAM);
    }
}
