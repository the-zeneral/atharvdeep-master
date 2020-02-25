<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Stories\Controller\Lazy;

/**
 * stories Shop page view
 */
class Package extends \Magento\Framework\App\Action\Action
{



    protected $_postCollection;

    public function execute()
    {
        $page_size = $_POST['page_size'];
        $page_number = $_POST['page_number'];

        $helper = $this->_objectManager->create('Magefan\Stories\Helper\Image');

        $category = $this->_objectManager->create('Magefan\Stories\Model\Category')->getCollection()
                                            ->addFieldToFilter('title','Package');
                                            
        $this->_postCollection = $this->_objectManager->create('Magefan\Stories\Model\Post')
                                        ->getCollection()
                                        ->addCategoryFilter($category->getFirstItem())
                                        ->setOrder('post_id', 'DESC')
                                        ->setPageSize($page_size)
                                        ->setCurPage($page_number);
        
        foreach ($this->_postCollection as $item) {
 
                 if($item->getTitle()!="promotions") {
                     echo '<a class="card" href="'.$item->getPostUrl().'">
                            <img class="card-img-top img-fluid" src="'.$helper->init($item->getImage())->resize(390).'" alt="Card image cap" width="100%">
                          </a>';
                            // <div class="card-block">
                            //   <h4 class="card-title">
                            //      '.$item->getTitle().'
                            //   </h4>
                            //   <p class="card-text">
                            //      '.$item->getShortDescription().'
                            //   </p>
                            // </div>
                   }
                 else {
                     echo '<a class="card" href="'.$item->getContentHeading().'">
                            <img class="card-img-top img-fluid" src="'.$helper->init($item->getImage())->resize(390).'" alt="Card image cap" width="100%">
                          </a>';
                 }
        }
    }

}
