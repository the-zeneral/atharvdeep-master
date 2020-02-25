<?php
namespace Magefan\Stories\Block\Homepage;
use Magento\Store\Model\ScopeInterface;


class StoriesList extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;
    protected $_postCollectionFactory;
    protected $_categoryCollectionFactory;

    protected $_postCollection;
    protected $_categoryCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        // \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magefan\Stories\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magefan\Stories\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        // \Magento\Framework\View\Page\Config $postConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;

        $this->_storeManager = $context->getStoreManager();
        $this->_postCollectionFactory = $postCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        // $this->pageConfig = $context->getConfig();
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        $this->_categoryCollection = $this->_categoryCollectionFactory->create()
                                            ->addFieldToFilter('title','Home');
                                            
        $this->_postCollection = $this->_postCollectionFactory->create()
                                    ->addCategoryFilter($this->_categoryCollection->getFirstItem())
                                    ->setOrder('sort', 'DESC')
                                    ->setOrder('post_id', 'DESC');
    }

    /**
     * Prepare posts collection
     *
     * @return \Magefan\Stories\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection()
    {
        if (is_null($this->_postCollection)) {
            $this->_preparePostCollection();
        }
                                    
        $data['total_posts'] = $this->_postCollection->count();
        $data['posts'] = $this->_postCollection;

        return $data;
    }

}