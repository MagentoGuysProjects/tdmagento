<?php

class Sns_I8style_Model_System_Config_Source_ListGoogleFont
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'', 'label'=>Mage::helper('i8style')->__('No select')),
			array('value'=>'Anton', 'label'=>Mage::helper('i8style')->__('Anton')),
			array('value'=>'Roboto+Condensed', 'label'=>Mage::helper('i8style')->__('Roboto Condensed')),
			array('value'=>'Port+Lligat+Slab', 'label'=>Mage::helper('i8style')->__('Port Lligat Slab')),
			array('value'=>'Questrial', 'label'=>Mage::helper('i8style')->__('Questrial')),
			array('value'=>'Kameron', 'label'=>Mage::helper('i8style')->__('Kameron')),
			array('value'=>'Oswald', 'label'=>Mage::helper('i8style')->__('Oswald')),
			array('value'=>'Open+Sans:300,400,600,700', 'label'=>Mage::helper('i8style')->__('Open Sans')),
			array('value'=>'Open+Sans+Condensed:300,300italic,700', 'label'=>Mage::helper('i8style')->__('Open Sans Condensed')),
			array('value'=>'BenchNine', 'label'=>Mage::helper('i8style')->__('BenchNine')),
			array('value'=>'Droid Sans', 'label'=>Mage::helper('i8style')->__('Droid Sans')),
			array('value'=>'Droid Serif', 'label'=>Mage::helper('i8style')->__('Droid Serif')),
			array('value'=>'PT Sans', 'label'=>Mage::helper('i8style')->__('PT Sans')),
			array('value'=>'Vollkorn', 'label'=>Mage::helper('i8style')->__('Vollkorn')),
			array('value'=>'Ubuntu', 'label'=>Mage::helper('i8style')->__('Ubuntu')),
			array('value'=>'Neucha', 'label'=>Mage::helper('i8style')->__('Neucha')),
			array('value'=>'Cuprum', 'label'=>Mage::helper('i8style')->__('Cuprum')),
			array('value'=>'Passion+One:400,700', 'label'=>Mage::helper('i8style')->__('Passion One'))
		);
	}
}
