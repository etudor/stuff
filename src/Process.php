<?php

namespace Etudor;

use const PHP_EOL;
use function print_r;
use ReflectionClass;
use ReflectionException;

class Process
{
    /** @var array */
    public $actions;

    /** @var array */
    private $path;

    private $debug = false;

    public function registerAction(AbstractAction $action, bool $debug = false)
    {
        $this->actions[$action->getName()][$this->getParamsAsString($action)][] = $action;
        $this->debug                                                            = $debug;
    }

    public function dispatch($type, $data, &$path = null)
    {
        if (null === $path) {
            $path = &$this->path;
        }

        $path[$type] = [
            'data'      => $data,
            '_children' => [],
        ];

        if (isset($this->actions[$type][$this->getIdFromParams(array_keys($data))])) {
            $toRun = [];
            /** @var AbstractAction $action */
            foreach ($this->actions[$type][$this->getIdFromParams(array_keys($data))] as $action) {
                $this->debug('Event fired: ' . $type);

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
            $this->debug('Event has no action to handle it: ' . $type);

            if (isset($this->actions[$type])) {
                $this->debug('Available reducers for type ' . $type);
                $this->debug(array_keys($this->actions[$type]), true);

                $this->debug('Create a reducer with this params: ' . $this->getIdFromParams(array_keys($data)));
            }
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    private function getParamsAsString(AbstractAction $action): string
    {
        try {
            $f = new ReflectionClass($action);
        } catch (ReflectionException $exception) {
            // todo log error
        }

        $params = [];

        foreach ($f->getMethod('execute')->getParameters() as $parameter) {
            $params[] = $parameter->getName();
        }

        return $this->getIdFromParams($params);
    }

    private function getIdFromParams($data): string
    {
        if (empty($data)) {
            return '_';
        }

        sort($data);

        return implode(':', $data);
    }

    private function debug($message, bool $nice = false)
    {
        if (true === $this->debug) {
            if (true === $nice) {
                print_r($message);
                print PHP_EOL;
            } else {
                print $message . PHP_EOL;
            }
        }
    }
}
