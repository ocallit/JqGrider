<?php

namespace Ocallit\JqGrider;

trait EventEmitter {
    protected array $observers = [];

    public function attach(string $event, callable $callback):void { $this->observers[$event][] = $callback; }

    public function notify(string $event, ...$args):void {
        if(!empty($this->observers[$event]))
            foreach($this->observers[$event] as $callback)
                if(empty($args))
                    call_user_func($callback, $event);
                else
                    call_user_func($callback, $event, ...$args);
    }

}
