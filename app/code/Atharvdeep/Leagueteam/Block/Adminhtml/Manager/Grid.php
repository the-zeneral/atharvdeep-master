<?php
namespace Atharvdeep\Leagueteam\Block\Adminhtml\Manager;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Atharvdeep\Leagueteam\Model\leagueFactory
     */
    protected $leagueFactory;

   
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->leagueFactory = $leagueFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('pk');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->leagueFactory->create()->getCollection()->addFieldToFilter('manager', array('in' => array('Silver','Gold','Diamond','Platinum')));
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'customer_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        
        $this->addColumn(
            'member_id',
            [
                'header' => __('Member Id'),
                'index' => 'member_id',
            ]
        );
         $this->addColumn(
             'member_name',
             [
                'header' => __('Member Name'),
                'index' => 'member_name',
             ]
         );
        $this->addColumn(
            'Joining Date',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
            ]
        );
        $this->addColumn(
            'manager_type',
            [
                'header' => __('Member Type'),
                'index' => 'manager',
            ]
        );
            

        
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
        

        
           $this->addExportType($this->getUrl('leagueteam/*/exportCsv', ['_current' => true]), __('CSV'));
           $this->addExportType($this->getUrl('leagueteam/*/exportExcel', ['_current' => true]), __('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Seedolabs_MemberRequest::package/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('package');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('leagueteam/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        return $this;
    }
}
