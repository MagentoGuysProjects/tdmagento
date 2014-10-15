<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_DpsPro_Block_Pxfusion_Info extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fooman/dpspro/pxfusion/info.phtml');
    }
    public function toPdf()
    {
        $this->setTemplate('fooman/dpspro/pxfusion/pdf/pxfusion.phtml');
        return $this->toHtml();
    }

    public function getAdditionalData($key)
    {
        return Mage::helper('foomandpspro')->getAdditionalData($this->getInfo(), $key);
    }

    public function getMaxmindData()
    {
        return Mage::helper('foomandpspro')->getMaxmindData($this->getInfo());
    }

    public function wasThreeDSecure()
    {
        return Mage::helper('foomandpspro')->wasThreeDSecure($this->getInfo());
    }

}
