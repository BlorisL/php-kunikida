# php-kunikida


## Description

This project aim to gain city position and its translation to a specific language by using differents free services with the same json structure. Just change options.json and start to make your requests. 

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [License](#license)

## Installation

Just PHP.

## Usage 

### A Kunikida\AService will be represented like this:

| Property | Type | Description |
| --- | --- | --- |
| **name** | `string` |  |
| **rModes** | `array` |  |
| **rServices** | `array` |  |
| **service** | `\stdClass` |  |
| **services** | `\stdClass` |  |


### A Kunikida\Geo will be represented like this:

| Property | Type | Description |
| --- | --- | --- |
| **name** | `string` |  |
| **rModes** | `array` |  |
| **rServices** | `array` |  |
| **service** | `\stdClass` |  |
| **services** | `\stdClass` |  |
| **language** | `string` |  |
| **translate** | `\Kunikida\Translate` |  |

### A Kunikida\Translate will be represented like this:

| Property | Type | Description |
| --- | --- | --- |
| **name** | `string` |  |
| **rModes** | `array` |  |
| **rServices** | `array` |  |
| **service** | `\stdClass` |  |
| **services** | `\stdClass` |  |

### Examples

```php
$params = new stdClass();
$geo = new \Kunikida\Geo('it');

$type = 'search';
$params->q = 'Napoli';

$result = $geo->request($type, $params);
if(count($result) > 0):
    echo $result[0]->label . "\n\n";
endif;
```

## License

Code licensed under the [MIT License](https://github.com/BlorisL/php-tsuruya/blob/main/LICENSE).

Do whatever you want with this code, but remember opensource projects work with the help of the community so would be really useful if any errors, updates, features or ideas were reported.

[Share with me a cup of tea](https://www.buymeacoffee.com/bloris) ☕
