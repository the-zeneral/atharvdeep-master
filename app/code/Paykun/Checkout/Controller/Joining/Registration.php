<?php
namespace Paykun\Checkout\Controller\Joining;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
   
class Registration extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        
        parent::__construct($context);
    }

    public function execute()
    {
         return $this->pageFactory->create();
    }
}
