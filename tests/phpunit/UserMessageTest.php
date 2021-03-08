<?php declare(strict_types=1);

namespace usermessageforatk\tests\phpunit;

use atk4\data\Exception;
use traitsforatkdata\TestCase;
use usermessageforatk\User;
use usermessageforatk\UserMessage;
use usermessageforatk\UserMessageToUser;

class UserMessageTest extends TestCase
{

    private $persistence;
    private $user;

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
    }

    public function testUseParam1AsUserRole()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->set('param1', 'ALL');
        $message1->save();

        $message2 = new UserMessage($this->persistence);
        $message2->set('param1', 'ALL');
        $message2->save();

        $message3 = new UserMessage($this->persistence);
        $message3->set('param1', 'SOMEOTHERROLE1');
        $message3->save();

        $message4 = new UserMessage($this->persistence);
        $message4->set('param1', 'admin');
        $message4->save();

        $message1->addUserCondition($this->user);
        $message1->addCondition('param1', 'in', ['ALL', 'admin']);
        self::assertEquals(
            3,
            $message1->action('count')->getOne()
        );
    }

    public function testIsReadByLoggedInUser()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->save();
        self::assertFalse($message1->isReadByUser($this->user));

        $message1->addMToMRelation(new UserMessageToUser($this->persistence), $this->user);
        self::assertTrue($message1->isReadByUser($this->user));
    }

    public function testMarkMessageAsRead()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->save();

        self::assertFalse($message1->isReadByUser($this->user));

        $message1->markAsReadForUser($this->user);
        self::assertTrue($message1->isReadByUser($this->user));
    }

    public function testExceptionMarkAsReadNotLoaded()
    {
        $message1 = new UserMessage($this->persistence);
        self::expectException(Exception::class);
        $message1->markAsReadForUser($this->user);
    }

    public function testExceptionIsReadByUserNotLoaded()
    {
        $message1 = new UserMessage($this->persistence);
        self::expectException(Exception::class);
        $message1->isReadByUser($this->user);
    }

    public function testExceptionMarkAsReadUserNotLoaded()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->save();
        self::expectException(Exception::class);
        $message1->markAsReadForUser(new User($this->persistence));
    }

    public function testDateFilter()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->save();

        $message2 = new UserMessage($this->persistence);
        $message2->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message2->save();

        $message3 = new UserMessage($this->persistence);
        $message3->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message3->save();

        $message1->addMaxDaysInPastCondition((new \DateTime())->modify('-30 Days'));

        self::assertEquals(
            1,
            $message1->action('count')->getOne()
        );

        $message3->set('never_invalid', 1);
        $message3->save();

        self::assertEquals(
            2,
            $message1->action('count')->getOne()
        );
    }
}