<?php
namespace Instamojo\Imojo\Controller\Registration;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
   
class Redirect extends \Magento\Framework\App\Action\Action
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
