<?php declare(strict_types=1);

namespace usermessageforatk;

use Atk4\Ui\Button;
use Atk4\Ui\Jquery;
use Atk4\Data\Model;
use Atk4\Ui\Exception;
use Atk4\Ui\jsExpression;
use Atk4\Ui\Modal;
use DateTimeInterface;

/**
 * This modal automatically opens itself if there are any unread messages for the currently logged in user
 */
class UserMessageModal extends Modal
{
    public $labelMessageRead = 'Benachrichtigung gelesen';

    public ?Button $markAsReadButton = null;

    //can the modal only be closed by the "Read it" button?
    public $forceApproveRead;

    protected $userModel;

    protected $activeUser;


    protected function init(): void
    {
        parent::init();
        $this->model = new UserMessage($this->getApp()->db);
    }

    public function setActiveUser(Model $user): void
    {
        $this->activeUser = $user;
    }

    public function renderView(): void
    {
        if (
            !$this->activeUser
            || !$this->activeUser->loaded()
        ) {
            throw new Exception(__CLASS__ . ' can only be used with a loaded user model');
        }

        $this->model->addUserCondition($this->activeUser);
        $this->model->tryLoadAny();
        if (!$this->model->loaded()) {
            $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = true;
            return;
        }

        $this->title = $this->model->get('created_date') instanceof DateTimeInterface ?
            $this->model->get('created_date')->format('d.m.Y') . ' ' : '';

        $this->title .= $this->model->get('title');
        $this->addScrolling();

        if (
            $this->forceApproveRead
            || $this->model->get('needs_user_confirm')
        ) {
            $this->notClosable();
        }

        if ($this->model->get('is_html')) {
            $this->template->dangerouslySetHtml('Content', $this->model->get('text'));
        } else {
            $this->template->set('Content', $this->model->get('text'));
        }

        $this->_addMessageReadButton($this->model);
        $this->js(true, $this->show());

        parent::renderView();
    }

    protected function _addMessageReadButton(UserMessage $message): void
    {
        $this->markAsReadButton = new Button();
        $this->markAsReadButton->set($this->labelMessageRead)->addClass('green ok');
        $this->markAsReadButton->setAttr('data-id', $message->get('id'));
        $this->addButtonAction($this->markAsReadButton);
        $this->markAsReadButton->on(
            'click',
            function ($e, $messageId) {
                $mfu = new UserMessage($this->getApp()->db, ['userModel' => $this->userModel]);
                $mfu->load($messageId);
                $mfu->markAsReadForUser($this->activeUser);
                $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = true;
                return $this->hide();
            },
            [
                'args' => [
                    (new Jquery(new jsExpression('this')))->data('id'),
                ]
            ]
        );
    }
}