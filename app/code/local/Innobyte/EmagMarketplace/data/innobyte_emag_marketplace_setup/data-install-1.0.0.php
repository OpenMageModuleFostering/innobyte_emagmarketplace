<?php

//eMAG statuses
$statuses = array(
    'emag_canceled' => 'eMAG - Canceled ',
    'emag_new' => 'eMAG - New',
    'emag_in_progress' => 'eMAG - In progress',
    'emag_prepared' => 'eMAG - Prepared',
    'emag_finalized' => 'eMAG - Finalized',
    'emag_returned' => 'eMAG - Returned'
);

// state assignment
$assignedState = array(
    'emag_new' => 'new',
    'emag_in_progress' => 'processing',
    'emag_prepared' => 'processing',
    'emag_finalized' => 'processing',
    'emag_canceled' => 'processing',
    'emag_returned' => 'closed'
);

foreach ($statuses as $code => $label) {
    /** @var $status Mage_Sales_Model_Order_Status */
    $status = Mage::getModel('sales/order_status')->load($code, 'status');
    if ($status->getStatus()) {
        continue;
    }
    // save first then assign state (state table has foreign key on status)
    $status->setStatus($code);
    $status->setLabel($label);
    $status->save();

    $status = Mage::getModel('sales/order_status')->load($code, 'status');
    $status->assignState($assignedState[$code]);
    $status->setIsDefault(0);
    $status->save();
}
