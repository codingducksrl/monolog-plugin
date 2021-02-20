# Coding duck monolog plugin
Simple monolog plugin to use with the coding duck's logging system.

# Installation
`composer install codingduck/monolog-plugin`

## Usage
### Standalone
Standalone usage without any kind of framework.
```php 
use Codingduck\Logger\CodingDuckLogger;

$factory = new CodingDuckLogger;

$logger = $factory($config);

$logger->info("Hello world!");
```

### Laravel / Lumen
Usage with Laravel/Lumen. Create or modify the file `config/logging.php`. If already exists just add a channel.

```php
<?php
return [
    'default' => env('LOG_CHANNEL', 'codingDuck'),
    
    'channels' => [
        'codingDuck' => [
            'driver' => 'custom',
            'via' => \Codingduck\Logger\CodingDuckLogger::class,
            [... $config]
        ]
    ],
];
```

## Configuration
All path are considered from the framework entry point
<table>
    <thead>
        <tr>
            <td><b>Key</b></td>
            <td><b>Description</b></td>
            <td><b>Required</b></td>
            <td><b>Default</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ca</td>
            <td>Path to ca certificate</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>cert</td>
            <td>Path to client certificate</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>key</td>
            <td>Path to client key</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>credentials</td>
            <td>Path to the credentials.json file</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>host</td>
            <td>Domain name of the collector server</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>port</td>
            <td>Port the collector server</td>
            <td>Yes</td>
            <td></td>
        </tr>
        <tr>
            <td>projectRoot</td>
            <td>The root of the project</td>
            <td>No</td>
            <td></td>
        </tr>
        <tr>
            <td>autoSession</td>
            <td>Automatically use sessions to associate the logs to a specific session</td>
            <td>No</td>
            <td>false</td>
        </tr>
    </tbody>
</table>

### Project root
The project root parameter is used to convert the files in the stacktrace from an absolute to a relative path.

Example:

if `projectRoot` is `/etc/projects/test/`, the stacktrace goes from `/etc/projects/test/App/Http/Controller/Test.php ` to `/App/Http/Controller/Test.php `
