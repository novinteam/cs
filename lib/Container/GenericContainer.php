<?php

namespace Magium\Configuration\Container;

use Interop\Container\ContainerInterface;

class GenericContainer implements ContainerInterface
{

    protected $container = [];

    public function set($value)
    {
        if (!is_object($value)) {
            throw new InvalidObjectException('The GenericContainer can only accept objects');
        }
        $class = get_class($value);
        $this->container[$class] = $value;
    }

    public function get($id)
    {
        if (!isset($this->container[$id])) {
            $this->set($this->newInstance($id));
        }
        return $this->container[$id];
    }

    public function newInstance($type)
    {
        $reflection = new \ReflectionClass($type);
        $constructor = $reflection->getConstructor();
        $constructorParams = $this->getParams($constructor);
        if ($constructorParams) {
            $requestedInstance = $reflection->newInstanceArgs($constructorParams);
        } else {
            $requestedInstance = $reflection->newInstance();
        }
        return $requestedInstance;
    }

    protected function getParams(\ReflectionMethod $method = null)
    {
        if (!$method instanceof \ReflectionMethod) {
            return [];
        }
        $constructorParams = [];
        $params = $method->getParameters();
        foreach ($params as $param) {
            if ($param->getClass() instanceof \ReflectionClass) {
                $class = $param->getClass()->getName();
                $instance = $this->get($class);
                $constructorParams[] = $instance;
            } else if (!$param->isOptional()) {
                throw new InvalidObjectException(
                    'The generic container will only manage constructor arguments that are objects'
                );
            }
        }
        return $constructorParams;
    }

    public function has($id)
    {
        return isset($this->container[$id]);
    }

}
