<?php 
class HotelDoWrapper
{
	/**
	 * Array de configuración por defecto
	 * @var array
	 */
    protected static $_conf = [];
    /**
     * Array de variables por defecto
     * @var array
     */
    protected static $_data = [];
    /**
     * Array de configuración
     * @var array
     */
    protected $conf = [];
    /**
     * Array de variable
     * @var array
     */
    protected $data = [];
    /**
     * Respuesta del servidor en string
     * @var null
     */
	protected $raw = null;
	/**
	 * Respuesta del servidor en simpleXML
	 * @var null
	 */
	protected $xml = null;
	/**
	 * Respuesta del servidor en Json string
	 * @var null
	 */
	protected $json = null;
	/**
	 * Respuesta del servidor en Array
	 * @var null
	 */
	protected $array = null;
	/**
	 * Almacena si ocurrió un error
	 * @var null
	 */
	protected $error = null;
	/**
	 * El mensaje de error
	 * @var null
	 */
	protected $error_description = null;
	/**
	 * Acción a ejecutar
	 * @var null
	 */
	protected $action = null;
	/**
	 * Constructor de la clase
	 * @param string $action acción o petición a la api
	 * @param array $array  array que sobrescribe la configuración o las variable por defecto
	 */
    public function __construct($action, $array = null) {
		$this->action=$action;
		if (is_null($array['data'])) {
			$this->data = self::$_data;
		}else{
			$this->data = $array['data'];
		}
		if (is_null($array['conf'])) {
			$this->conf = self::$_conf;
		}else{
			$this->conf = $array['conf'];
		}
    }
    /**
     * Establece la configuración por defecto, es posible pesarle un array que contenga la configuración
     * @param string $key nonbre de variable
     * @param string $val valor
     */
    public static function setDefaultConfig($key, $val = null)
    {
        if (is_array($key) and is_null($val) ) {
        	self::$_conf = $key;
        }else{
        	self::$_conf[$key] = $val;
        }
    }
    /**
     * Establece la variables por defecto, es posible pesarle un array que contenga la configuración
     * @param string $key nonbre de variable
     * @param string $val valor
     */
    public static function setDefaultData($key, $val = null)
    {
        if (is_array($key) and is_null($val) ) {
        	self::$_data = $key;
        }else{
        	self::$_data[$key] = $val;
        }
    }
    /**
     * Carga la configuración por defecto
     */
    public static function loadDefaultConfig()
    {
		self::$_conf['cached']  = true;
		self::$_conf['timeExpire']  = 86400;
		self::$_conf['path'] =getcwd().DIRECTORY_SEPARATOR;
		self::$_conf['url'] ='http://testxml.e-tsw.com/AffiliateService/AffiliateService.svc/restful/';
    }
    /**
     * Cambia la configuración por defecto, es posible pesarle un array que contenga la configuración
     * @param [type] $key nombre de variable
     * @param [type] $val valor
     */
    public function setConfig($key, $val = null)
    {
        if (is_array($key) and is_null($val) ) {
        	$this->conf = $this->conf+$key;
        }else{
        	$this->conf[$key] = $val;
        }
    }
	/**
	 * Retorna el valor de variable de configuración
	 * @param  string $key nombre de la variable
	 * @return string      valor de la configuración
	 */
    public function getConfig($key)
    {
    	return $this->conf[$key];
    }
    /**
     * Retorna el array de variables
     * @return array array de variables
     */
	public function getRecuestData()
	{
		return $this->data;
	}
	/**
	 * Agrega una variable al array de variable
	 * @param string $name  Nombre de la variable
	 * @param string $value Valor de la variable
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}
	/**
	 * Método mágico, Agrega una variable al array de variable
	 * @param string $name  Nombre de la variable
	 * @param string $value Valor de la variable
	 */
	public function __set($name, $value) 
	{
		$this->set($name, $value);
	}
	/**
	 * Método mágico, Retorna el valor de del array de una variable
	 * @param  string $name Nombre de la variable
	 * @return string       Valor
	 */
	public function __get($name){
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		$trace = debug_backtrace();
		trigger_error('Propiedad indefinida mediante __get(): ' . $name .' en ' . $trace[0]['file'] .' en la línea ' . $trace[0]['line'],E_USER_NOTICE);
		return null;
	}
	/**
	 * Método mágico, determina si esta definida una variable dentro del array de variable
	 * @param  string  $name Nombre de la variable
	 * @return boolean       True si lo esta o false si no
	 */
	public function __isset($name){
		return isset($this->data[$name]);
	}
	/**
	 * Método mágico, elimina una variable del array de variables
	 */
	public function __unset($name){
		unset($this->data[$name]);
	}
	/**
	 * Pone en null las variable contenedoras
	 */
	protected function reset()
	{
		$this->raw=null;
		$this->xml=null;
		$this->json=null;
		$this->array=null;
		$this->error=null;
		$this->error_description=null;		
	}
	/**
	 * Hace petición a la api vía get 
	 * @return string Respuesta del api
	 */
	public function get(){
		if (is_null($this->raw)) {
		}else{
			$this->reset();
		}
		$url=$this->getRecuest();
		if (isset($this->conf['cached']) and $this->conf['cached'] ) {
			if (isset($this->conf['path'])) {
				$file_cache=$this->conf['path'].$this->action.'-'.md5($url).'.xml';
				if (file_exists($file_cache)) {
					$xml=simplexml_load_file($file_cache);
					if ((integer) $xml->Expire > time() ) {
						$this->xml=$xml;
						$this->raw=$this->xml->asXML();
						return $this->raw;
					}
				}				
			}else{
				$trace = debug_backtrace();
				trigger_error('Error, cache esta activado pero la ruta no lo esta '.$trace[0]['file'].' en la línea '.$trace[0]['line'],	E_USER_ERROR);
			}
		}
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$this->raw = curl_exec ($curl);
		$http_code=curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close ($curl);
		if ($http_code!=200) {
			$trace = debug_backtrace();
			trigger_error('ERROR HTTP CODE '.$http_code.' en '.$trace[0]['file'].' en la línea '.$trace[0]['line'],	E_USER_ERROR);
			$this->raw=null;
			return null;
		}
		if (isset($file_cache)) {
			$this->xml=simplexml_load_string($this->raw);
			if (empty($this->xml->Error)) {
				$this->xml->addChild('Expire', time()+$this->conf['timeExpire']);
				$this->xml->asXML($file_cache);
			}
		}
		return $this->raw;
	}
	/**
	 * Retorna la respuesta en string
	 * @return string 
	 */
	public function getRaw() {
		return $this->raw;
	}
	/**
	 * Retorna la respuesta en simpleXml
	 * @return simpleXML
	 */
	public function getXml() {
		if (is_null($this->raw)) {
			return $this->raw;
		}elseif (is_null($this->xml)) {
			$this->xml=simplexml_load_string($this->raw);
		}
		return $this->xml;
	}
	/**
	 * Retorna la respuesta en Json string
	 * @return string 
	 */	
	public function getJson(){
		if (is_null($this->raw)) {
			return $this->raw;
		}elseif (is_null($this->json)) {
			$this->json = json_encode($this->getXml());
		}
		return $this->json;
	}
	/**
	 * Retorna la respuesta en array
	 * @return array 
	 */
	public function getArray(){
		if (is_null($this->raw)) {
			return $this->raw;
		}elseif (is_null($this->array)) {
			$this->array=json_decode($this->getJson(),TRUE);
		}
		return $this->array;
	}
	/**
	 * Verifica si la api retorno un error
	 * @return boolean true si ocurrio un error 
	 */
	public function fail(){
		if (is_null($this->raw)) {
			return true;
		}elseif (is_null($this->error)) {
			if (empty($this->getXml()->Error)) {
				$this->error=false;
			}else{
				$this->error=true;
				$this->error_description=$this->getXml()->Error->Description;
			}
		}
		return $this->error;
	}
	/**
	 * Retorna el string de error de la api
	 * @return stiing Mensaje de error
	 */
	public function getError(){
		if (is_null($this->raw)) {
			return "unexecuted";
		}elseif (is_null($this->error_description)) {
			$this->fail();
		}
		return $this->error_description;
	}
	/**
	 * Retorna la url con variables 
	 * @return string url 
	 */
	public function getRecuest()
	{
		$vars='?'.http_build_query($this->data);
		$vars=str_ireplace("%3A",":",$vars);
		return $this->conf['url'].$this->action.$vars;
	}
	/**
	 * Retorna el datos de post 
	 * @return array url=>contiene la url, xml=>contiene datos del post en xml
	 */
	public function postRecuest()
	{
		$xml = $this->prepareXML();
		return ['url'=>$this->conf['url'].$this->action, 'xml'=> $xml->convert($this->data) ];
	}
	/**
	 * Llena estructura base del petición XML
	 * @return Array2xml Estructura, esqueleto XML
	 */
	protected function prepareXML()
	{
		require_once('array2xml.php');
		$xml = new Array2xml();
		$xml->setRootName('Request');
		$xml->setRootAttrs(['Type' => 'Reservation','version' => '1.0']);
		$xml->setFilterNumbersInTags(true);
		return $xml;
	}
	/**
	 * Hace petición vía post al api, no hay cache en este petición
	 * @return string respuesta del api
	 */
	public function post(){
		if (is_null($this->raw)) {
		}else{
			$this->reset();
		}
		$xml = $this->prepareXML();
		$vars=$xml->convert($this->data);
		$url=$this->conf['url'].$this->action;
		$headers = [
		    "Content-type: text/xml",
		    "Content-length: " . strlen($vars),
		    "Connection: close",
		];
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$raw = curl_exec ($curl);
		$http_code=curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close ($curl);
		if ($http_code!=200) {
			$trace = debug_backtrace();
			trigger_error(
				'ERROR HTTP CODE' .$http_code.
				' en ' . $trace[0]['file'] .
				' en la línea ' . $trace[0]['line'],
				E_USER_ERROR);
			$raw=null;
			return null;
		}
		$this->raw=$raw;
		return $raw;
	}
}
