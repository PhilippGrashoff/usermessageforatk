<?php declare(strict_types=1);

namespace usermessageforatk\tests\phpunit;

use atk4\login\Auth;
use atk4\ui\Layout;
use traitsforatkdata\TestCase;
use usermessageforatk\tests\testclasses\AppWithTrait;
use usermessageforatk\User;
use usermessageforatk\UserMessage;
use usermessageforatk\UserMessageModal;
use usermessageforatk\UserMessageToUser;

class ShowUserMessageTraitTest extends TestCase
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
        $this->app->initLayout(new Layout());
        $this->app->auth = new Auth();
    }

    public function testAddUserModal()
    {
        $return = $this->app->addUserMessageModal();
        self::assertInstanceOf(
            UserMessageModal::class,
            $return
        );
        self::assertArrayHasKey(
            'usermessageforatk_usermessagemodal',
            $this->app->layout->elements
        );
    }

    public function testAddUserModalReturnsNullIfSessionParamSet()
    {
        $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = true;
        self::assertNull($this->app->addUserMessageModal());
    }
}