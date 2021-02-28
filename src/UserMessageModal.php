<?php declare(strict_types=1);

namespace usermessageforatk;

use atk4\ui\Button;
use atk4\ui\Exception;
use atk4\ui\jQuery;
use atk4\ui\jsExpression;
use atk4\ui\Modal;
use DateTimeInterface;

/**
 * This modal automatically opens itself if there are any unread messages for the currently logged in user
 */
class UserMessageModal extends Modal
{
    public $labelMessageRead = 'Benachrichtigung gelesen';

    //can the modal only be closed by the "Read it" button?
    public $forceApproveRead;

    public $param1;
    public $param2;
    public $param3;

    //if there is more than one message, show them in a "row"? TODO: Currently not implemented!
    public $showMultiple = false;

    public function renderView(): void
    {
        if (!$this->app->getAuth()->user->loaded()) {
            throw new Exception(__CLASS__ . ' can only be used with a logged in user');
        }

        $i = 0;
        $messages = (new UserMessage($this->app->db))
            ->getUnreadMessagesForUser(
                $this->app->getAuth()->user,
                $this->param1,
                $this->param2,
                $this->param3,
                (new \DateTime())->modify('-30 Days')
            );
        foreach ($messages as $message) {
            $i++;
            if ($i > 1 && !$this->showMultiple) {
                break;
            }
            $this->_addMessage($message);
        }

        parent::renderView();
    }

    protected function _addMessage(UserMessage $message)
    {
        $this->title = $message->get('created_date') instanceof DateTimeInterface ? $message->get(
                'created_date'
            )->format('d.m.Y') . ' ' : '';
        $this->title .= $message->get('title');
        $this->addScrolling();
        if (
            $this->forceApproveRead
            || $message->get('needs_user_confirm')
        ) {
            $this->notClosable();
        }
        $this->addClass('fullHeightModalWithButtons');
        if ($message->get('is_html')) {
            $this->template->setHTML('Content', $message->get('text'));
        } else {
            $this->template->set('Content', $message->get('text'));
        }

        $this->_addMessageReadButton($message);
        $this->js(true, $this->show());
    }

    protected function _addMessageReadButton(UserMessage $message)
    {
        $b = new Button();
        $b->set($this->labelMessageRead)->addClass('green ok');
        $b->setAttr('data-id', $message->get('id'));
        $this->addButtonAction($b);
        $b->on(
            'click',
            function ($e, $mfu_id) {
                $mfu = new UserMessage($this->app->db);
                $mfu->load($mfu_id);
                $mfu->markAsReadForUser($this->app->getAuth()->user);
                $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = 1;
                return $this->hide();
            },
            [
                'args' => [
                    (new jQuery(new jsExpression('this')))->data('id'),
                ]
            ]
        );
    }
}