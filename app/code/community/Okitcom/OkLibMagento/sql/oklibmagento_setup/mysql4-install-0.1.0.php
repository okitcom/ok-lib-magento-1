<?php
$installer = $this;
$installer->startSetup();

$checkoutTable = $installer->getConnection()->newTable(
    $installer->getTable('oklibmagento/checkout')
)->addColumn(
    'id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
    'ID'
)->addColumn(
    'quote_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
    'Quote Id'
)->addColumn(
    'sales_order_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
    'Quote Id'
)->addColumn(
    'external_id',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'primary' => false],
    'External Identifier'
)->addColumn(
    'ok_transaction_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
    'OK Transaction Id'
)->addColumn(
    'guid',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    [],
    'OK Guid'
)->addColumn(
    'state',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    [],
    'OK State'
)->addColumn(
    'discount',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    255,
    [],
    'OK Discount in cents'
)->addColumn(
    'created_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Created At'
)->addColumn(
    'updated_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE],
    'Updated At'
)->setComment(
    'OK checkout transactions table'
);

$authTable = $installer->getConnection()->newTable(
    $installer->getTable('oklibmagento/authorization')
)->addColumn(
    'id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
    'ID'
)->addColumn(
    'external_id',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'primary' => false],
    'External Identifier'
)->addColumn(
    'ok_transaction_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
    'OK Transaction Id'
)->addColumn(
    'guid',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    [],
    'OK Guid'
)->addColumn(
    'state',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    [],
    'OK State'
)->addColumn(
    'created_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Created At'
)->addColumn(
    'updated_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE],
    'Updated At'
)->setComment(
    'OK authorization request table'
);

$installer->getConnection()->createTable($checkoutTable);
$installer->getConnection()->createTable($authTable);


$installer->endSetup();
	 