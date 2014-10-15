<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_PxFusion_Validator extends Mage_Centinel_Model_Service
{

    public function shouldAuthenticate()
    {
        return false;
    }

    public function getAuthenticationStartUrl()
    {
        return Fooman_DpsPro_Model_Method_PxFusion::URL_PXFUSION;

    }

}
