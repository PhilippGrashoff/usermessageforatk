<?php

declare(strict_types=1);

namespace usermessageforatk\tests\testclasses;

use Atk4\Ui\App;
use usermessageforatk\ShowUserMessageTrait;

class AppWithTrait extends App
{
    use ShowUserMessageTrait;

    public function callExit($for_shutdown = false): void
    {
        return;
    }
}