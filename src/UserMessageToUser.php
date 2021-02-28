<?php declare(strict_types=1);

namespace usermessageforatk;

use mtomforatk\MToMModel;


class UserMessageToUser extends MToMModel
{

    public $table = 'user_message_to_user';

    protected $userModel = User::class;

    protected function init(): void
    {
        $this->fieldNamesForReferencedClasses = [
            'user_message_id' => UserMessage::class,
            'user_id' => $this->userModel
        ];

        parent::init();
    }
}