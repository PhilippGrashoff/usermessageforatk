<?php declare(strict_types=1);

namespace usermessageforatk\tests;

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

    public function testgetUnreadMessagesForLoggedInUser()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->set('param1', 'ALL');
        $message1->save();

        $message2 = new UserMessage($this->persistence);
        $message2->set('param1', 'ALL');
        $message2->save();
        $message2->addMToMRelation(
            new UserMessageToUser($this->persistence),
            $this->user
        );

        $message3 = new UserMessage($this->persistence);
        $message3->set('param1', 'SOMEOTHERROLE1');
        $message3->save();

        $message4 = new UserMessage($this->persistence);
        $message4->set('param1', 'admin');
        $message4->save();

        $res = $message3->getUnreadMessagesForUser($this->user, ['ALL', 'admin']);
        self::assertEquals(2, $res->action('count')->getOne());
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

    public function testExceptionGetUnreadMessagesUserNotLoaded()
    {
        $unloadedUser = new User($this->persistence);
        $message1 = new UserMessage($this->persistence);
        self::expectException(Exception::class);
        $message1->getUnreadMessagesForUser($unloadedUser);
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

    public function testDifferentParamMatches()
    {
        $message1 = new UserMessage($this->persistence);
        $message1->set('param1', 'LALALA');
        $message1->set('param2', 'Hansi');
        $message1->set('param3', 'Was');
        $message1->save();

        $message2 = new UserMessage($this->persistence);
        $message2->set('param1', 'gege');
        $message2->set('param2', 'Hansi');
        $message2->set('param3', '');
        $message2->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message2->save();

        $res = $message1->getUnreadMessagesForUser($this->user);
        self::assertEquals(
            2,
            $res->action('count')->getOne()
        );

        $res = $message1->getUnreadMessagesForUser($this->user, ['LALALA', 'gege']);
        self::assertEquals(
            2,
            $res->action('count')->getOne()
        );

        $res = $message1->getUnreadMessagesForUser($this->user, 'LALALA');
        self::assertEquals(
            1,
            $res->action('count')->getOne()
        );

        $res = $message1->getUnreadMessagesForUser($this->user, null, ['Hansi']);
        self::assertEquals(
            2,
            $res->action('count')->getOne()
        );

        $res = $message1->getUnreadMessagesForUser($this->user, 'LALALA', null, '');
        self::assertEquals(
            0,
            $res->action('count')->getOne()
        );

        $res = $message1->getUnreadMessagesForUser(
            $this->user,
            function ($message) {
                $message->addCondition('param1', 'LIKE', '%geg%');
            }
        );
        self::assertEquals(
            1,
            $res->action('count')->getOne()
        );
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

        $res = $message1->getUnreadMessagesForUser(
            $this->user,
            null,
            null,
            null,
            (new \DateTime())->modify('-1 Month')
        );
        self::assertEquals(
            1,
            $res->action('count')->getOne()
        );

        $message3->set('never_invalid', 1);
        $message3->save();
        $res = $message1->getUnreadMessagesForUser(
            $this->user,
            null,
            null,
            null,
            (new \DateTime())->modify('-1 Month')
        );
        self::assertEquals(
            2,
            $res->action('count')->getOne()
        );
    }
}