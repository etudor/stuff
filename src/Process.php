<?php

namespace Etudor;

use ReflectionClass;
use ReflectionException;

class Process
{
    public  $reducers;
    private $path;

    public function registerReducer(AbstractAction $action)
    {
        $this->reducers[$action->getName()][$this->getParamsAsString($action)][] = $action;
    }

    public function dispatch($type, $data, &$path = null)
    {
        if (null === $path) {
            $path = &$this->path;
        }

        $path[$type] = [
            'data'     => $data,
            '_children' => [],
        ];

        if (isset($this->reducers[$type][$this->getIdFromParams(array_keys($data))])) {
            $toRun = [];
            /** @var AbstractAction $action */
            foreach ($this->reducers[$type][$this->getIdFromParams(array_keys($data))] as $action) {
                print 'Event fired: ' . $type . PHP_EOL;

                $action->setParent($path[$type]['_children']);
                $action->setDispatch([$this, 'dispatch']);

                $toRun[] = [
                    'action' => $action,
                    'data'   => $data,
                ];
            }

            foreach ($toRun as $action) {
                call_user_func_array([$action['action'], 'execute'], $action['data']);
            }
        } else {
            print 'Event has no action to handle it: ' . $type . PHP_EOL;

            if (isset($this->reducers[$type])) {
                print 'Available reducers for type ' . $type . PHP_EOL;
                print_r(array_keys($this->reducers[$type]));

                print 'Create a reducer with this params: ' . $this->getIdFromParams(array_keys($data)) . PHP_EOL;
            }
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @throws ReflectionException
     */
    private function getParamsAsString(AbstractAction $action)
    {
        $f      = new ReflectionClass($action);
        $params = [];

        foreach ($f->getMethod('execute')->getParameters() as $parameter) {
            $params[] = $parameter->getName();
        }

        return $this->getIdFromParams($params);
    }

    private function getIdFromParams($data)
    {
        if (empty($data)) {
            return '_';
        }

        sort($data);

        return implode(':', $data);
    }
}
