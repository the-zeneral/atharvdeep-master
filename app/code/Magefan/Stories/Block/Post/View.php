<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Stories\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Stories post view
 */
class View extends AbstractPost
{
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $post = $this->getPost();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $slider = $objectManager->create('\Emthemes\Slideshow\Model\Items')
        //                         ->getCollection()
        //                         ->addFieldToFilter('identity', ["like" => $post->getIdentifier()."%"]);
                                
        // $this->setSlider($slider);

        $this->_addBreadcrumbs($post);
        $this->pageConfig->addBodyClass('stories-post-' . $post->getIdentifier());
        $this->pageConfig->getTitle()->set($post->getTitle());
        $this->pageConfig->setKeywords($post->getMetaKeywords());
        $this->pageConfig->setDescription($post->getMetaDescription());

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Magefan\Stories\Model\Post $post
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs(\Magefan\Stories\Model\Post $post)
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'stories',
                [
                    'label' => __('Stories'),
                    'title' => __('Go to Stories Home Page'),
                    'link' => $this->_storeManager->getStore()->getUrl('stories')
                ]
            );
            $breadcrumbsBlock->addCrumb('stories_post', [
                'label' => $post->getTitle(),
                'title' => $post->getTitle()
            ]);
        }
    }
}
