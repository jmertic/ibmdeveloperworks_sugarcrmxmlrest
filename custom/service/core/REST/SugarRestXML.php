<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *
 ********************************************************************************/

require_once('service/core/REST/SugarRest.php');

/**
 * This class is a XML implementation of REST protocol
 */
class SugarRestXML extends SugarRest
{
    /**
	 * @see SugarRest::generateResponse()
	 */
	public function generateResponse($input)
	{
		ob_clean();
		if (isset($this->faultObject)) {
			$this->generateFaultResponse($this->faultObject);
		} else {
			echo $this->_convertArrayToXML($input);
		}
	}

	/**
	 * Returns a fault since we cannot accept XML as an input type
	 *
	 * @see SugarRest::serve()
	 */
	public function serve()
	{
	    $this->fault('Error: Cannot use XML as an input type');
	}

	/**
	 * @see SugarRest::generateFaultResponse()
	 */
	public function generateFaultResponse($errorObject)
	{
		$error = $errorObject->number . ': ' . $errorObject->name . '<br>' . $errorObject->description;
		$GLOBALS['log']->error($error);
		ob_clean();
		echo $this->_convertArrayToXML($errorObject);
	}

	
	/**
	 * Converts a PHP array into XML
	 *
	 * @param array $input
	 * @return string XML
	 */
	protected function _convertArrayToXML($input)
	{
	    $xmlWriter = new XMLWriter();
	    $xmlWriter->openMemory();
	    $xmlWriter->setIndent(true);
	    $xmlWriter->startDocument('1.0','UTF-8');
	    $xmlWriter->startElement('result');
	    foreach ( $input as $key => $value ) {
	        if ( is_array($value) ) {
                $xmlWriter->startElement($key);
                $this->_convertArrayItemToXML($value,$xmlWriter);
                $xmlWriter->endElement();
	        }
	        else {
	            $xmlWriter->writeElement($key,$value);
	        }
	    }
	    $xmlWriter->endElement();
	    $xmlWriter->endDocument(); 
	    
	    return $xmlWriter->outputMemory(); 
	}
	
	protected function _convertArrayItemToXML(array $item, XMLWriter $xmlWriter)
	{
	    foreach ( $item as $key => $value ) {
            if ( is_array($value) ) {
                $xmlWriter->startElement($key);
                $this->_convertArrayItemToXML($value,$xmlWriter);
                $xmlWriter->endElement();
	        }
	        else {
	            $xmlWriter->writeElement($key,$value);
	        }
        }
	}
}
