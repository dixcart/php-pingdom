# Pingdom PHP Api

## Install

### Composer
In your composer.json add the following in your require statement

````
'dixcart/php-pingdom': 'dev-master'
````

## Basic usage

````
require 'vendor/autoload.php';
$api = new \Pingdom\Api(USERNAME, PASSWORD);
try {
    $response = $api->getChecks();
    echo json_encode($response);
} catch (\Exception $e) {
    echo json_encode(array('error' => $e->getMessage));
}