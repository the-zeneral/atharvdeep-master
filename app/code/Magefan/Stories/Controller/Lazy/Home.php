<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Stories\Controller\Lazy;

/**
 * Stories home page view
 */
class Home extends \Magento\Framework\App\Action\Action
{



    protected $_postCollection;

    public function execute()
    {
        $page_size = $_POST['page_size'];
        $page_number = $_POST['page_number'];

        $last_sort = $_POST['last_sort'];
        $last_id = $_POST['last_id'];

        $counts=0;
        $idhtml="";

        $helper = $this->_objectManager->create('Magefan\Stories\Helper\Image');

        $category = $this->_objectManager->create('Magefan\Stories\Model\Category')->getCollection()
            ->addFieldToFilter('title','Home');

        $this->_postCollection = $this->_objectManager->create('Magefan\Stories\Model\Post')
            ->getCollection()
            ->addCategoryFilter($category->getFirstItem())
            ->setOrder('sort', 'DESC')
            ->setOrder('post_id', 'DESC')
            ->addFieldToFilter('sort', array('gteq' => $last_sort))
            ->addFieldToFilter('post_id', array('neq' => $last_id))
            ->addFieldToFilter('is_displayed_home', array('neq' => 1))
            ->setCurPage($page_number);

        $post_count =$this->_postCollection->count();
            
        if($counts<=10)
        {

            foreach ($this->_postCollection as $item)
            {
                if(++$counts>10) break;
                $item->setData('is_displayed_home',1);
                $item->save();
                if($item->getTitle()!="promotions")
                {
                    $idhtml= $idhtml.'<div class="home-card-parent"><a class="card" href="'.$item->getPostUrl().'">
                               <img class="card-img-top img-fluid" src="'.$helper->init($item->getImage())->resize(390).'" alt="Card image cap" width="100%">
                             </a></div>';
                }
                else
                {
                    $idhtml= $idhtml.'<div class="home-card-parent"><a class="card" href="'.$item->getContentHeading().'">
                               <img class="card-img-top img-fluid" src="'.$helper->init($item->getImage())->resize(390).'" alt="Card image cap" width="100%">
                             </a></div>';
                }
                if($post_count>=10)
                {
                    if($counts==10)
                    {
                        $last_sort = $item->getSort();
                        $last_id = $item->getId();
                    }
                } 

            }
        }
        echo $idhtml.",".$last_sort.",".$last_id;
        exit();
    }

}
