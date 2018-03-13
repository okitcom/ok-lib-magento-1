<?php
		$installer = $this;
		$installer->startSetup();

$installer->addAttribute("customer", "oktoken", array("type"=>"varchar", "visible" => false, "required" => false));
		$installer->endSetup();
			 