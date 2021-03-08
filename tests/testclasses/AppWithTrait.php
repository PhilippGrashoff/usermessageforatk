<?php

declare(strict_types=1);

namespace usermessageforatk\tests\testclasses;

use atk4\ui\App;
use usermessageforatk\ShowUserMessageTrait;

class AppWithTrait extends App
{
    use ShowUserMessageTrait;

    public function callExit($for_shutdown = false): void
    {
        return;
    }
}