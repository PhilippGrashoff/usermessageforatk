<?php

namespace usermessageforatk\tests\phpunit;

use Atk4\Login\Auth;
use Atk4\Ui\Exception;
use Atk4\Ui\Layout\Centered;
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
        $this->app->initLayout([Centered::class]);
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
        $modal->setActiveUser($this->user);
        $modal->renderView();
        self::assertTrue(true);
    }

    public function testReturnOnNoMessageLoaded(): void
    {
        $modal = UserMessageModal::addTo($this->app);
        $modal->setActiveUser($this->user);
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
        $modal->setActiveUser($this->user);
        $modal->renderView();
        self::assertTrue(true);
    }

    /*
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
        $modal->setActiveUser($this->user);

        self::assertFalse(
            $userMessage->hasMToMRelation(new UserMessageToUser($this->persistence), $this->user)
        );

        $this->app->run();
        $modalId = $modal->markAsReadButton->name;
        $_GET['__atk_callback'] = $modalId;
        $_GET[$modalId] = 'ajax';
        $_POST['c0'] = $userMessage->get('id');
        ob_start();
        $this->app->run();
        ob_end_clean();

        self::assertTrue(
            $userMessage->hasMToMRelation(new UserMessageToUser($this->persistence), $this->user)
        );
        self::assertTrue(
            $_SESSION['MESSAGES_FOR_USER_DISPLAYED']
        );
    }
    */

    public function testSessionParamSetAlsoOnNoNewMessage(): void
    {
        $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = false;

        $modal = $this->app->addUserMessageModal();
        $modal->setActiveUser($this->user);
        $modal->renderView();
        self::assertTrue(
            $_SESSION['MESSAGES_FOR_USER_DISPLAYED']
        );
    }
}