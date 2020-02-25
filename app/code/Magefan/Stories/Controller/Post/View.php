<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Stories\Controller\Post;

/**
 * stories post view
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * View stories post action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        $id = $this->getRequest()->getParam('id');
        $post = $this->_objectManager->create('Magefan\Stories\Model\Post')->load($id);
        if (!$post->getId()) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_stories_post', $post);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
