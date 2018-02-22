<?php

require './vendor/autoload.php';

use Etudor\Process;
use Etudor\AbstractAction as Action;

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


$reducers = new Process();
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
