<?php

namespace usermessageforatk\tests\phpunit;

use atk4\login\Auth;
use atk4\ui\Exception;
use atk4\ui\Layout\Generic;
use traitsforatkdata\TestCase;
use usermessageforatk\tests\testclasses\AppWithTrait;
use usermessageforatk\User;
use usermessageforatk\UserMessage;
use usermessageforatk\UserMessageModal;
use usermessageforatk\UserMessageToUser;

class UserMessageModalTest extends TestCase
{

    private $persistence;
    private $user;
    private $app;

    protected $sqlitePersistenceModels = [
        User::class,
        UserMessage::class,
        UserMessageToUser::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->persistence = $this->getSqliteTestPersistence();
        $this->user = new User($this->persistence);
        $this->user->save();
        $this->app = new AppWithTrait(['always_run' => false]);
        $this->app->db = $this->persistence;
        $this->app->initLayout([Generic::class]);
        $this->app->auth = new Auth();
    }

    public function testRenderViewExceptionNoUserModel(): void
    {
        $modal = UserMessageModal::addTo($this->app);
        self::expectException(Exception::class);
        $modal->renderView();
    }

    public function testRenderView(): void
    {
        $userMessage = new UserMessage($this->persistence);
        $userMessage->save();

        $modal = UserMessageModal::addTo($this->app);
        $modal->setUserModel($this->user);
        $modal->renderView();
        self::assertTrue(true);
    }

    public function testReturnOnNoMessageLoaded(): void
    {
        $modal = UserMessageModal::addTo($this->app);
        $modal->setUserModel($this->user);
        $modal->renderView();
        self::assertTrue(true);
    }

    public function testHTMLAndNotClosable(): void
    {
        $userMessage = new UserMessage($this->persistence);
        $userMessage->set('is_html', 1);
        $userMessage->set('needs_user_confirm', 1);
        $userMessage->save();

        $modal = UserMessageModal::addTo($this->app);
        $modal->setUserModel($this->user);
        $modal->renderView();
        self::assertTrue(true);
    }

    public function testButtonCallback(): void
    {
        $userMessage = new UserMessage($this->persistence);
        $userMessage->save();

        $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = false;

        $modal = $this->app->addUserMessageModal();
        self::assertInstanceOf(
            UserMessageModal::class,
            $modal
        );
        $modal->setUserModel($this->user);

        self::assertFalse(
            $userMessage->hasMToMRelation(new UserMessageToUser($this->persistence), $this->user)
        );

        $_GET['__atk_callback'] = '_cdf1f7ea__dal_button_click';
        $_GET['_cdf1f7ea__dal_button_click'] = 'ajax';
        $_POST['c0'] = $userMessage->get('id');
        ob_start();
        $this->app->run();
        ob_end_clean();

        self::assertTrue(
            $userMessage->hasMToMRelation(new UserMessageToUser($this->persistence), $this->user)
        );
    }
}