<?php

class Reducers
{
    public  $reducers;
    private $path;

    public function registerReducer(Action $action)
    {
        $this->reducers[$action->getName()][$this->getID($action)][] = $action;
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
            /** @var Action $action */
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
    private function getID($action)
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

$reducers = new Reducers();
$reducers->registerReducer(new InputAction());
$reducers->registerReducer(new CreateUserAction());
$reducers->registerReducer(new Stuff());
$reducers->registerReducer(new StuffThis());
$reducers->registerReducer(new SendEmailAction());

$reducers->dispatch('input', [
    'user'     => 'ion',
    'password' => 'password',
]);

print(json_encode($reducers->getPath(), JSON_PRETTY_PRINT));
print(json_encode($reducers->reducers, JSON_PRETTY_PRINT));

abstract class Action {
    private   $dispatch;
    protected $parent;

    public function setDispatch(callable $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    public function setParent(&$parent)
    {
        $this->parent = &$parent;
    }

    public function dispatch($type, $data)
    {
        call_user_func_array($this->dispatch, [
            $type,
            $data,
            &$this->parent
        ]);
    }

    abstract public function getName(): string;
}

class InputAction extends Action {
    public function getName(): string
    {
        return 'input';
    }

    public function execute(string $user, string $password = null)
    {
        print('hereaaaa--------------');
        // do stuff
        $this->dispatch('sendEmail', [
            'from' => 'from',
            'to'   => 'to',
        ]);

        $this->dispatch('createUser', [
            'user' => new stdClass()
        ]);

        $this->dispatch('error', [
            'message' => 'This is sparta',
        ]);

    }
}

class CreateUserAction extends Action {
    public function getName(): string
    {
        return 'createUser';
    }

    public function execute($user)
    {
        $this->dispatch('response', []);
        $this->dispatch('sendEmail', []);
    }
}

class Stuff extends Action {
    public function getName(): string
    {
        return 'input';
    }

    public function execute()
    {
        print 'Here yeeeessss          ........ ' . PHP_EOL;
    }
}

class StuffThis extends Action {
    public function getName(): string
    {
        return 'input';
    }

    public function execute($user, $password, $email)
    {
        print 'Here noooooooooooooooooooooooo ........ ' . PHP_EOL;
    }
}

class SendEmailAction extends Action {
    public function getName(): string
    {
        return 'sendEmail';
    }

    public function execute($from, $to)
    {

    }
}
