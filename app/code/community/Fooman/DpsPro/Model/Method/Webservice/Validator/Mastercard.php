<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_DpsPro
 * @copyright  Copyright (c) 2013 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_DpsPro_Model_Method_Webservice_Validator_Mastercard extends Mage_Centinel_Model_State_Mastercard
{
    public function isAuthenticateSuccessful()
    {
        //DPS does not have a separate service call to check the results of the call
        //to the term_url
        //PARes is checked with SubmitTransaction instead
        if ($this->getLookupEnrolled() == 'N') {
            return true;
        } else {
            if ($this->getLookupAcsUrl()) {
                return true;
            }
        }
        return false;
    }
}
