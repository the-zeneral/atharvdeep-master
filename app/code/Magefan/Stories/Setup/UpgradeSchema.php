<?php
 
namespace Magefan\Stories\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface {
 
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;
 
        $installer->startSetup();
    
    if(version_compare($context->getVersion(), '2.0.1', '<')) 
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('magefan_stories_post'),
                'sort',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Sort order of post'
                ]);
    }
    if(version_compare($context->getVersion(), '2.0.2', '<')) 
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('magefan_stories_post'),
                'is_displayed_home',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'loaded posts in home'
                ]);
        $installer->getConnection()->addColumn(
            $installer->getTable('magefan_stories_post'),
                'is_displayed_shop',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'loaded posts in shop'
                ]);
    }
        $installer->endSetup();
    }
}
