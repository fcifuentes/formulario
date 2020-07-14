<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'config.inc.php';
require_once 'includes/WebService.php';

$webservices = new WebServices($_POST, $wsconfig);
// @note modificar para mapear campos en el CRM
//$webservices->set('bill_city', $webservices->get('mailingcity'));
//$webservices->set('bill_country', $webservices->get('mailingcountry'));
$webservices->set('closingdate', date('Y-m-d', strtotime("+30 days")));
$result = $webservices->process();
echo json_encode($result);

?>
