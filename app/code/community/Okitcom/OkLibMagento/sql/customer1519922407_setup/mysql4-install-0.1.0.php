<?php
		$installer = $this;
		$installer->startSetup();

if ($installer->attributeExists("customer", 'oktoken')) {
    $installer->removeAttribute("customer", 'oktoken');
}

$installer->addAttribute("customer", "oktoken", array("type"=>"varchar", "visible" => false, "required" => false));
		$installer->endSetup();
			 