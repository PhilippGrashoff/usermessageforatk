<?php declare(strict_types=1);

namespace usermessageforatk;

use atk4\data\Model;
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
                    'type' => 'text',
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
                //extra parameters to further refine for whom this message is/is not, e.g. by user role
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

        //show older messages first if there is more than one to show to user
        $this->setOrder(['created_date' => 'ASC']);
    }

    public function addUserCondition($user): void
    {
        $this->addCondition(
            $this->refLink(UserMessageToUser::class)
                ->addCondition('user_id', $user->get('id'))
                ->action('count'),
            '<',
            1
        );
    }

    public function addMaxDaysInPastCondition(\DateTimeInterface $maxInPast): void
    {
        $this->addCondition(
            Model\Scope::createOr(
                ['created_date', '>=', $maxInPast->format('Y-m-d')],
                ['never_invalid', 1]
            )
        );
    }

    /**
     * mark  message as read for the logged in user.
     */
    public function markAsReadForUser($user): UserMessageToUser
    {
        return $this->addMToMRelation(
            new UserMessageToUser($this->persistence, ['userModel' => $this->userModel]),
            $user
        );
    }

    public function isReadByUser($user): bool
    {
        return $this->hasMToMRelation(
            new UserMessageToUser($this->persistence, ['userModel' => $this->userModel]),
            $user
        );
    }
}