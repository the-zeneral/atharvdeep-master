<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Stories\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Stories post related posts block
 */
class RelatedPosts extends \Magefan\Stories\Block\Post\PostList\AbstractList
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->setPageSize(5);
        return parent::_construct();
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection
            ->addFieldToFilter('post_id', array('in' => $this->getPost()->getRelatedPostIds() ?: array(0)))
            ->addFieldToFilter('post_id', array('neq' => $this->getPost()->getId()))
            ->setPageSize(
                (int) $this->_scopeConfig->getValue(
                    'mfstories/post_view/related_posts/number_of_posts',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
    }

    /**
     * Retrieve true if Display Related Posts enabled
     * @return boolean
     */
    public function displayPosts()
    {
        return (bool) $this->_scopeConfig->getValue(
            'mfstories/post_view/related_posts/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Stories\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post',
                $this->_coreRegistry->registry('current_stories_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Get Block Identities
     * @return Array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_relatedposts_'.$this->getPost()->getId()  ];
    }
}
