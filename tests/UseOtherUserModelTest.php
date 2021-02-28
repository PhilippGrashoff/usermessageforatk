<?php declare(strict_types=1);

namespace usermessageforatk\tests;

use atk4\data\Exception;
use traitsforatkdata\TestCase;
use usermessageforatk\tests\testclasses\OtherUserModel;
use usermessageforatk\UserMessage;
use usermessageforatk\UserMessageToUser;

class UseOtherUserModelTest extends TestCase
{

    private $persistence;
    private $user;

    protected $sqlitePersistenceModels = [
        OtherUserModel::class,
        UserMessage::class,
        UserMessageToUser::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->persistence = $this->getSqliteTestPersistence();
        $this->user = new OtherUserModel($this->persistence);
        $this->user->save();
    }

    public function testUseOtherUserModel(): void
    {
        $message1 = new Message();
    }
}