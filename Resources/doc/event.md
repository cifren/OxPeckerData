3 events are available

For more information see [Symfony2 Event/listener](http://symfony.com/doc/current/cookbook/doctrine/event_listeners_subscribers.html), "How to Register Event Listeners and Subscribers".

On Success
==========

When the command finished and it was a success
```yml
services:
    myListener:
        class: Acme\Bundle\ExampleBundle\CommandListener
        tags:
            - { name: kernel.event_listener, event: run_command.success, method: onSuccess }
```

On Failed
=========

When the command finished and it was a fail
```yml
services:
    myListener:
        class: Acme\Bundle\ExampleBundle\CommandListener
        tags:
            - { name: kernel.event_listener, event: run_command.failed, method: onFailed }
```


On Stop
======

When the command finished after "run_command.success" and "run_command.failed"
```yml
services:
    myListener:
        class: Acme\Bundle\ExampleBundle\CommandListener
        tags:
            - { name: kernel.event_listener, event: run_command.stop, method: onStop }
```

The class event
===============

Here is your listener.

```php
    namespace Acme\Bundle\ExampleBundle;  
    use Cifren\OxPeckerData\Dispatcher\RunCommandEvent; 
     
    class CommandListener
    {
     
        public function onSuccess(RunCommandEvent $event) {
     
            // Your stuff..
            $name = $event->getName();
            $commandArgs = $event->getArgs();
            
        }
    }
```
