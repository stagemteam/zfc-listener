# ZF3 Lazy Event Listener

The main problem with standard events registering is the eager dependency resolving. This create unwanted overhead
and increase time response.

By the scene Shared Event Manager is used.

This module is intended to solve this problem and allow register listeners with config and `Zend\EventManager\LazyEventListener`.
Simply speaking, this module register Lazy Listeners and resolve all listener dependencies directly event arise. 

# Installation
Run command `composer require stagem/zfc-listener`

## Usage
Register module in `config/modules.config.php` with `'Stagem\ZfcListener'`

Add your events in `your/module/config/module.config.php`
```php
return [
    'event_manager' => [
        'definitions' => [
            [
                'listener' => \Acme\Work\Listener\WorkListener::class,
                'method' => 'postWorkPaid',
                'event' => 'change.work-paid.post',
                'identifier' => \Acme\Status\Controller\StatusController::class,
                'priority' => 100,
            ],
            // other listeners
            // [...],
        ],
    ],
];
```

Then when in your `StatusController` the event `change.work-paid.post` will be triggered the `LazyEventListener` resolve 
dependencies of `WorkListener` and method `postWorkPaid` will be execute.

## Tips  
If your module has many listeners it's good practice split your config in separate file
```php
// your/module/config/module.config.php
return [
    'event_manager' => require 'listener.config.php',
];
```
```php
// your/module/config/listener.config.php
return [
    'definitions' => [
        [
            'listener' => \Acme\Work\Listener\WorkListener::class,
            'method' => 'postWorkPaid',
            'event' => 'change.work-paid.post',
            'identifier' => \Acme\Status\Controller\StatusController::class,
            'priority' => 100,
        ],
        // other listeners
        // [...],
    ],
];
```