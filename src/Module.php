<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_<package>
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Stagem\ZfcListener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\LazyEventListener;
use Zend\EventManager\Exception;

class Module
{
    public function getConfig()
    {
        $config = include __DIR__ . '/../config/module.config.php';

        return $config;
    }

    /**
     * @param EventInterface $e
     * @see https://gist.github.com/xtreamwayz/f10292fab031a0088632f52b82a5f663
     * @see https://zendframework.github.io/zend-eventmanager/tutorial/#shared-managers
     * @see https://zend-eventmanager.readthedocs.io/en/latest/lazy-listeners/lazy-event-listener/
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var EventManager $eventManager */
        $eventManager = $e->getTarget()->getEventManager();
        $container = $e->getApplication()->getServiceManager();
        $sem = $eventManager->getSharedManager(); // shared events manager

        $listeners = $container->get('config')['event_manager']['definitions'];

        // This would raise an exception for invalid structure
        foreach ($listeners as $listener) {
            if (is_array($listener)) {
                $definition = $listener;
                if (is_array($definition['listener'])) {
                    $definition['listener'] = $listener['listener'][0];
                    $definition['method'] = $listener['listener'][1];
                }

                $events = (array) $listener['event'];
                foreach ($events as $event) {
                    $definition['event'] = $event;
                    $lazyListener = new LazyEventListener($definition, $container);
                    if (!$lazyListener instanceof LazyEventListener) {
                        throw new Exception\InvalidArgumentException(sprintf(
                            'All listeners must be LazyEventListener instances or definitions; received %s',
                            (is_object($lazyListener) ? get_class($lazyListener) : gettype($lazyListener))
                        ));
                    }
                    $sem->attach(
                        $definition['identifier'],
                        $lazyListener->getEvent(),
                        $lazyListener,
                        $lazyListener->getPriority()
                    );
                }
            }
        }
    }
}