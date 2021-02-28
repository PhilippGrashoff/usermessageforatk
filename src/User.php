<?php declare(strict_types=1);

namespace usermessageforatk;

use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;

/**
 * This class represents a message for logged in users. The main concept is to display
 * unread messages on login to
 * inform each individual user about updates, usually in a modal.
 */
class User extends Model
{

    use ModelWithMToMTrait;

    public $table = 'user';


    protected function init(): void
    {
        parent::init();
        $this->addField(
            'role',
            [
                'type' => 'string'
            ]
        );

        $this->addMToMReferenceAndDeleteHook(UserMessageToUser::class);
    }
}