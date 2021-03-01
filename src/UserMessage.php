<?php declare(strict_types=1);

namespace usermessageforatk;

use atk4\data\Exception;
use atk4\data\Model;
use DateTimeInterface;
use mtomforatk\ModelWithMToMTrait;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;

/**
 * This class represents a message for logged in users. The main concept is to display unread messages on login to
 * inform each individual user about updates, usually in a modal.
 */
class UserMessage extends Model
{

    use ModelWithMToMTrait;
    use CreatedDateAndLastUpdatedTrait;

    public $table = 'user_message';

    public $caption = 'Benachrichtigung';

    protected $userModel = User::class;


    protected function init(): void
    {
        parent::init();
        $this->addFields(
            [
                //Message title, e.g. "UI Update"
                [
                    'title',
                    'type' => 'string',
                    'caption' => 'Titel'
                ],
                //is text HTML?
                [
                    'is_html',
                    'type' => 'integer',
                    'caption' => 'Text ist HTML'
                ],
                //HTML or Text Content of the message
                [
                    'text',
                    'type' => 'Text',
                    'caption' => 'Nachricht'
                ],
                //can be used by UI to force user to click "I have read it!" button instead of just closing the modal
                [
                    'needs_user_confirm',
                    'type' => 'integer',
                    'caption' => 'Muss von Benutzer als gelesen bestätigt werden'
                ],
                //if a date filter is applied, this makes the date filter ignore this message. Useful for e.g. "Welcome new User"
                [
                    'never_invalid',
                    'type' => 'integer',
                    'caption' => ''
                ],
                //extra parameters to further refine for whom this message is/is not
                [
                    'param1',
                    'type' => 'string'
                ],
                [
                    'param2',
                    'type' => 'string'
                ],
                [
                    'param3',
                    'type' => 'string'
                ],
            ]
        );

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
        $this->addMToMReferenceAndDeleteHook(
            UserMessageToUser::class,
            '',
            [],
            ['userModel' => $this->userModel]
        );
    }

    /**
     * Load all unread messages for the current logged in user
     */
    public function getUnreadMessagesForUser(
        $user,
        $param1 = null,
        $param2 = null,
        $param3 = null,
        DateTimeInterface $maxInPast = null
    ): self {
        if (!$user->loaded()) {
            throw new Exception('A user needs to be loaded for ' . __FUNCTION__);
        }

        $messages = new self($this->persistence, ['userModel' => $this->userModel]);
        //make sure there is no record for the current user with is set as read
        $messages->addCondition(
            $messages->refLink(UserMessageToUser::class)
                ->addCondition('user_id', $user->get('id'))
                ->action('count'),
            '<',
            1
        );
        if ($maxInPast) {
            $messages->addCondition(
                Model\Scope::createOr(
                    ['created_date', '>=', $maxInPast->format('Y-m-d')],
                    ['never_invalid', 1]
                )
            );
        }

        $this->_addParamConditionToMessages($messages, $param1, 'param1');
        $this->_addParamConditionToMessages($messages, $param2, 'param2');
        $this->_addParamConditionToMessages($messages, $param3, 'param3');

        return $messages;
    }

    /**
     * Add condition to messages if $param is not null
     */
    protected function _addParamConditionToMessages(self $messages, $param, string $fieldName): void
    {
        if ($param === null) {
            return;
        } elseif (is_callable($param)) {
            call_user_func($param, $messages);
        } elseif (is_array($param)) {
            $messages->addCondition($fieldName, 'in', $param);
        } else {
            $messages->addCondition($fieldName, $param);
        }
    }

    /**
     * mark  message as read for the logged in user.
     */
    public function markAsReadForUser($user): UserMessageToUser
    {
        return $this->addMToMRelation(new UserMessageToUser($this->persistence, ['userModel' => $this->userModel]), $user);
    }

    public function isReadByUser($user): bool
    {
        return $this->hasMToMRelation(new UserMessageToUser($this->persistence, ['userModel' => $this->userModel]), $user);
    }
}