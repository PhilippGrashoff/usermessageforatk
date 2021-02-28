<?php

declare(strict_types=1);

namespace usermessageforatk\tests\testclasses;

use atk4\data\Model;

class OtherUserModel extends Model
{

    public $table = 'other_user';

    protected function init(): void
    {
        parent::init();
        $this->addField(
            'role',
            ['type' => 'string']
        );
    }
}