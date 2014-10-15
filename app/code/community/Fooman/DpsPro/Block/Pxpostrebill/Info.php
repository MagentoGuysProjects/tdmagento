<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Block_Pxpostrebill_Info extends MageBase_DpsPaymentExpress_Block_Pxpost_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fooman/dpspro/pxpostrebill/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('fooman/dpspro/pxpostrebill/pdf/pxpostrebill.phtml');
        return $this->toHtml();
    }
}
