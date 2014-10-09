<?php
/*------------------------------------------------------------------------
 * Copyright (C) 2013 The SNS Theme. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: SNS Theme
 * Websites: http://www.snstheme.com
-------------------------------------------------------------------------*/

include_once (dirname(__FILE__).'/includes/lessc.inc.php');

$_helper = Mage::helper('i8style/data');
$_helper->compileLess();


