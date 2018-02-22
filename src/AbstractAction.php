<?php

namespace Etudor;

abstract class AbstractAction {
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

    public function dispatch(string $type, array $data)
    {
        call_user_func_array($this->dispatch, [
            $type,
            $data,
            &$this->parent
        ]);
    }

    abstract public function getName(): string;
}
