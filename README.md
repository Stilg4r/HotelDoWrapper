# HotelDoWrapper
Es un envoltorio para la api que proporciona HotelDO
depende de Array2xml
https://github.com/Jeckerson/array2xml
# Configuración
El primer paso para el uso de la clase es la configuración 
```php
require_once('HotelDoWrapper.php');
HotelDoWrapper::setDefaultConfig('cached',true);//si se hace cache de la respuesta de la api
HotelDoWrapper::setDefaultConfig('timeExpire',86400);//tiempo en milisegundos en que expira el cache 
HotelDoWrapper::setDefaultConfig('path','/');//ruta donde se guarda lo archivos cache
HotelDoWrapper::setDefaultConfig('url','');//url de la api, esto es importante para salir de sandbox
```
Puede usar un array para definir la configuración
```php
$config = [
    'cached' => true,
    'timeExpire' => 86400,
    'path' => '/',
    'url' => '',
];
HotelDoWrapper::setDefaultConfig($config);
```
O se puede cargar la configuración por defecto
```php
HotelDoWrapper::loadDefaultConfig();
```
De la misma manera, pude establecer datos por defecto para el uso del api, esto es útil cuando cada llamada a la api requiere el mismo dato
```php
HotelDoWrapper::setDefaultData('a','123456');
$data = ['a' => '123456'];
HotelDoWrapper::setDefaultData($data);
```
# Crear un nuevo objeto
El segundo paso seria crear un objeto, se tiene que indicar el elemento a consultar por ejemplo GetQuoteHotels
```php
$query = new HotelDoWrapper('GetQuoteHotels');
```
Tanto la configuración como los valores por defecto afectan a todas las instancias de esta clase, pero se pueden modificar mediante el método setConfig este método solo afecta el objeto, esto se hace mediante un array o clave, valor
```php
$query = new HotelDoWrapper('GetQuoteHotels');
$query->setConfig('cached',false);
$query->setConfig('timeExpire',86000);
$config = [
    'cached' => false,
    'timeExpire' => 86000,
];
$query->setConfig($config);
```
Otra manera de cambiar tanto la configuración o valores por defecto es al crear el objeto
```php
$data = [
    "a" => "123456",
];
$config = [
    'cached' => false,
    'timeExpire' => 86400,
    'path' => '/temp/',
    'url' => '',
];
$override = [
    'data' =>$data, 
    'conf' =>$config,
];
$query = new HotelDoWrapper('GetQuoteHotels',$override);
// solo pude afectar a un elemento,
// solo afecta a lo datos de configuración 
$override = ['conf' =>$config];
$query = new HotelDoWrapper('GetQuoteHotels',$override);
// solo afecta a lo datos por defecto 
$override = ['data' =>$data];
$query = new HotelDoWrapper('GetQuoteHotels',$override);
// otra manera de hacerlo 
$query = new HotelDoWrapper('GetQuoteHotels',['data' => ['a' => '123457']]);
```
El siguiente paso en llenar los datos para la consulta hay dos formas para hacerlo
```php 
$query = new HotelDoWrapper('GetQuoteHotels');
$query->co='MX';//primera forma
$query->set('c','PE');//segunda forma
echo $query->co//retorna el valor de co, en este caso MX
```
# Consultado a la api 
El siguiente paso es ejecutar la consulta 
```php
$query->get();//Este método hace la consulta mediante una petición GET, retorna el string de la respuesta 
$query->post();//Este método hace la consulta mediante una petición POST, retorna el string de la respuesta 
```
El resultado puede estar en diferentes formatos
```php
$query->getRaw();//string
$query->getXml();//SimpleXML
$query->getJson();//json string
$query->getArray();//Array
```
# Manejo de errores
Si el api retornara algún error se se tiene los métodos para manejarlo
```php
$query->fail();//retorna true en caso de api retorne un error
$query->getError();// retorna el mensaje de error 
```
# Algunos otros métodos de la clase
Para el debug hay los siguientes métodos
```php
$query->getConfig('cached');//retorna el valor cached de la configuración 
$query->getRecuestData();//Retorna el array de variables 
$query->getRecuest();//Retorna la url de la petición via GET
$query->postRecuest();//Retorna la url de la petición via POST, y el string de XML de la petición
```
# Ejemplo 
Este es un ejemplo parcial
```php
require_once('HotelDoWrapper.php');
HotelDoWrapper::loadDefaultConfig();
HotelDoWrapper::setDefaultData('a','123456');
$query = new HotelDoWrapper('GetQuoteHotels');
$query->co='MX';
$query->c='PE';
// . . . 
$query->get();
if ($query->fail()) {
    echo $query->getError();
    exit();
}else {
    header("Content-type: text/xml");
    echo->getRaw();
}
```
