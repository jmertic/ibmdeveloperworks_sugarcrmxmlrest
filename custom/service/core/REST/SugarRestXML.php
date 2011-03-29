<?php
if(!defined('sugarEntry')) define('sugarEntry', true);

require_once('service/core/REST/SugarRest.php');

/**
 * This class is a XML implementation of REST protocol
 */
class SugarRestXML extends SugarRest
{
	/**
	 * @see SugarRest::serve()
	 */
	public function serve()
	{
	    $GLOBALS['log']->info('Begin: SugarRestXML->serve');
	    $xml = !empty($_REQUEST['rest_data'])? $_REQUEST['rest_data']: '';
	    if(empty($_REQUEST['method']) || !method_exists($this->implementation, $_REQUEST['method'])){
			$er = new SoapError();
			$er->set_error('invalid_call');
			$this->fault($er);
		}
		else {
			$method = $_REQUEST['method'];
			$data = $this->_convertXMLToArray(from_html($xml));
			if(!is_array($data))$data = array($data);
			$GLOBALS['log']->info('End: SugarRestXML->serve');
			return call_user_func_array(array( $this->implementation, $method),$data);
		}
	}
	
	/**
	 * Converts an XML string into a PHP array
	 *
	 * @param string $xml
	 * @return array
	 */
	protected function _convertXMLToArray($xml)
	{
	    $returnArray = array();
	    $xmlObject = simplexml_load_string($xml);
	    
	    foreach ( $xmlObject->children() as $child ) {
	        // Attempt to convert the child element to a string; if it comes out an a non-empty string then the child element has no children.
	        $contents = trim((string) $child);
	        if ( !empty($contents) ) {
	            $returnArray[$child->getName()] = $contents;
	        }
	        else {
	            // Recursively call this method to convert any child arrays to XML
	            $returnArray[$child->getName()] = $this->_convertXMLToArray($child->asXML());
	        }
	    }
	    
	    return $returnArray;
	}
	
    /**
	 * @see SugarRest::generateResponse()
	 */
	public function generateResponse($input)
	{
	    // If there is a fault object, return it instead.
	    if (isset($this->faultObject)) {
		    $this->generateFaultResponse($this->faultObject);
		} 
		else {
		    ob_clean();
		    echo $this->_convertArrayToXML($input);
		}
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
	        // If this is an array, we’ll call SugarRestXML::_convertArrayItemToXML() to convert the array to XML, containing inside the given $key element.
	        if ( is_array($value) ) {
                $xmlWriter->startElement($key);
                $this->_convertArrayItemToXML($value,$xmlWriter);
                $xmlWriter->endElement();
	        }
	        // If it is just a scalar, we can write out the element directly.
	        else {
	            $xmlWriter->writeElement($key,$value);
	        }
	    }
	    $xmlWriter->endElement();
	    $xmlWriter->endDocument(); 
	    
	    return $xmlWriter->outputMemory(); 
	}
	
	/**
	 * Converts an item in a PHP array into XML
	 *
	 * @param array $item
	 * @param object XMLWriter $xmlWriter
	 * @return string XML
	 */
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
