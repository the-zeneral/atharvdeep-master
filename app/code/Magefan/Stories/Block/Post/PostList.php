<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Stories\Block\Post;

/**
 * Stories post list block
 */
class PostList extends \Magefan\Stories\Block\Post\PostList\AbstractList
{
    /**
     * Block template file
     * @var string
     */
	protected $_defaultToolbarBlock = 'Magefan\Stories\Block\Post\PostList\Toolbar';

    /**
     * Retrieve post html
     * @param  \Magefan\Stories\Model\Post $post
     * @return string
     */
    public function getPostHtml($post)
    {
        $itemBlock = $this->getChildBlock('stories.posts.list.item');
        if ($itemBlock) {
            return $itemBlock->setPost($post)->toHtml();
        }

        return false;
    }

    /**
     * Retrieve Toolbar Block
     * @return \Magefan\Stories\Block\Post\PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();

        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    /**
     * Retrieve Toolbar Html
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Before block to html
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getPostCollection();

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);

        return parent::_beforeToHtml();
    }

}
