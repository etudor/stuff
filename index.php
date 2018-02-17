<?php

class Reducers
{
    public $reducers;
    private $path;

    public function registerReducer(string $type, callable $function)
    {
        $this->reducers[$type][$this->getID($function)][] = $function;
    }

    public function dispatch($type, $data, &$path = null)
    {
        if (null === $path) {
            $path = &$this->path;
        }

        $path[$type] = [
            'data'     => $data,
            'children' => [],
        ];

        if (isset($this->reducers[$type][$this->getIdFromParams(array_keys($data))])) {
            $toRun = [];
            foreach ($this->reducers[$type][$this->getIdFromParams(array_keys($data))] as $reducer) {
                print 'Event: ' . $type . PHP_EOL;

                $toRun[] = [
                    'callable' => $reducer,
                    'args'     => array_merge($data, ['dispatch' => [$this, 'dispatch'], 'path' => &$path[$type]['children']])
                ];
            }

            foreach ($toRun as $run) {
                call_user_func_array($run['callable'], $run['args']);
            }
        } else {
            print 'Event has no reducer: ' . $type . PHP_EOL;
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    private function getID($function)
    {
        $f      = new ReflectionFunction($function);
        $params = [];

        foreach ($f->getParameters() as $parameter) {
            if (in_array($parameter->getName(), ['dispatch', 'parent'])) {
                continue;
            }

            $params[] = $parameter->getName();
        }

        return $this->getIdFromParams($params);
    }

    private function getIdFromParams($data)
    {
        sort($data);

        return implode(':', $data);
    }
}

$reducers = new Reducers();
$reducers->registerReducer('input', function (string $user, string $password, callable $dispatch, &$parent) {
    // do stuff
    $dispatch('sendEmail', [
        'from' => 'from',
        'to'   => 'to',
    ], $parent);

    $dispatch('createUser', [
        'user' => new stdClass()
    ], $parent);

    $dispatch('error', [
        'message' => 'This is sparta',
    ], $parent);
});

$reducers->registerReducer('createUser', function ($user, callable $dispatch, &$parent) {
    // create user

    $dispatch('response', [], $parent);
    $dispatch('sendEmail', [], $parent);
});

$reducers->registerReducer('input', function (string $user, string $password, callable $dispatch, &$parent) {
    // do nothing or log something
    print 'Here yeeeeeeeees .............. ' . PHP_EOL;
});

$reducers->registerReducer('input', function (
    string $user,
    string $password,
    string $email,
    callable $dispatch,
    &$parent
) {
    // do nothing or log something
    print 'Here noooooooooooooooooooooooooooooooooooooooooooo.............. ' . PHP_EOL;
});

$reducers->dispatch('input', [
    'user'     => 'ion',
    'password' => 'password',
]);

print(json_encode($reducers->getPath(), JSON_PRETTY_PRINT));

foreach ($reducers->reducers as $type => $reducer) {
    print($type . ' - ');
    print(json_encode(array_keys($reducer), JSON_PRETTY_PRINT));
}
