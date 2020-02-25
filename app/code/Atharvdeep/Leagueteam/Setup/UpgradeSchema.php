<?php
namespace Atharvdeep\Leagueteam\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('atharvdeep_leagueteam'),
                'customer_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Customer Id',
                    'after' => 'pk'

                ]
            );
        }   if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('atharvdeep_leagueteam'),
                'child_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Child Total'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('atharvdeep_leagueteam'),
                'manager',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 25,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Manager'
                ]
            );
        }
        $installer->endSetup();
    }
}
