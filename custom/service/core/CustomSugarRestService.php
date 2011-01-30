<?php
require_once('service/core/SugarRestService.php');

class CustomSugarRestService extends SugarRestService
{
    /**
	 * @see SugarRestService::_getTypeName()
	 */
	protected function _getTypeName($name)
	{
	    if ( strtolower($name) == 'xml' ) {
	        return 'SugarRestXML';
	    }
	    
        return parent::_getTypeName($name);
	}
	
	/**
	 * @see SugarRestService::__construct()
	 */
	public function __construct($url)
	{
		$GLOBALS['log']->info('Begin: SugarRestService->__construct');
		$this->restURL = $url;

		$this->responseClass = $this->_getTypeName($_REQUEST['response_type']);
		$this->serverClass = $this->_getTypeName($_REQUEST['input_type']);
		$GLOBALS['log']->info('SugarRestService->__construct serverclass = ' . $this->serverClass);
		if ( file_exists('service/core/REST/'. $this->serverClass . '.php') ) {
		    require_once('service/core/REST/'. $this->serverClass . '.php');
		}
        elseif ( file_exists('custom/service/core/REST/'. $this->serverClass . '.php') ) {
            require_once('custom/service/core/REST/'. $this->serverClass . '.php');
        }
        else {
            $GLOBALS['log']->fatal('ERROR: SugarRestService->__construct serverClass '.$this->serverClass.' not found');
        }
		$GLOBALS['log']->info('End: SugarRestService->__construct');
	} // ctor
	
	/**
	 * @see SugarRestService::serve()
	 */
	public function serve()
	{
		$GLOBALS['log']->info('Begin: SugarRestService->serve');
		if ( file_exists('service/core/REST/'. $this->responseClass . '.php') ) {
		    require_once('service/core/REST/'. $this->responseClass . '.php');
		}
        elseif ( file_exists('custom/service/core/REST/'. $this->responseClass . '.php') ) {
            require_once('custom/service/core/REST/'. $this->responseClass . '.php');
        }
        else {
            $GLOBALS['log']->fatal('ERROR: SugarRestService->__construct serverClass '.$this->responseClass.' not found');
        }
		$response  = $this->responseClass;

		$responseServer = new $response($this->implementation);
		$this->server->faultServer = $responseServer;
		$this->responseServer->faultServer = $responseServer;
		$responseServer->generateResponse($this->server->serve());
		$GLOBALS['log']->info('End: SugarRestService->serve');
	} // fn
}
