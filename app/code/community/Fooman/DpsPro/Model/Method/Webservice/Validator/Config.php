<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice_Validator_Config extends Mage_Centinel_Model_Config
{
    public function getStateModelClass($cardType)
    {
        $node = Mage::getConfig()->getNode(
            $this->_cardTypesConfigPath . '/' . $cardType . '/validator/foomandpspro/state'
        );
        if (!$node) {
            return false;
        }
        return $node->asArray();
    }
}
