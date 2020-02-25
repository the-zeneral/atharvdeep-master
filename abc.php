<?php
use Magento\Framework\App\Bootstrap;
 
require __DIR__ . '/app/bootstrap.php';
 
$params = $_SERVER;
 
$bootstrap = Bootstrap::create(BP, $params);
 
$objectManager = $bootstrap->getObjectManager();
 
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
echo "-------THIS IS PHP SCRIPT FOR TESTING MAGENTO 2 OBJECT --------";
echo "<pre>";
$sponsor_id = 12;
$member_id = 4;
$data['level1'] = 5;
$primaryKey = 1;

$league = $objectManager->create('Atharvdeep\Leagueteam\Model\League');
$model = $league->getCollection()->addFieldToSelect('*')
                                 ->addFieldToFilter('member_id', $sponsor_id);
echo $parent = $model->getFirstItem()->getMemberName();

$customerObj = $objectManager->get('Magento\Customer\Model\Session');
           // echo $sponsorName = $customerObj->getCustomer()->getId();
$customerSession = $objectManager->create('Magento\Customer\Model\Session');
