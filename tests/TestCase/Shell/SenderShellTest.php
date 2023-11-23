<?php
declare(strict_types=1);

namespace EmailQueue\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOutput;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Mailer\Transport\MailTransport;
use Cake\Network\Exception\SocketException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use EmailQueue\Model\Table\EmailQueueTable;
use EmailQueue\Shell\SenderShell;
use EmailQueue\Test\Fixture\EmailQueueFixture;
use TestApp\Mailer\TestMailer;

/**
 * SenderShell Test Case.
 */
class SenderShellTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var ConsoleOutput
     */
    protected $io;

    /**
     * @var MockObject
     */
    protected $out;

    /**
     * @var MockObject
     */
    protected $Sender;

    /**
     * Fixtures.
     *
     * @var array
     */
    public $fixtures = [
        EmailQueueFixture::class,
    ];

    /**
     * @var EmailQueueTable
     */
    protected $EmailQueue;

    /**
     * setUp method.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->out = new ConsoleOutput();
        $this->out = $this->getMockBuilder(ConsoleOutput::class)
            ->setMethods(['write'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->io = new ConsoleIo($this->out, $this->out);

        $this->Sender = $this->getMockBuilder(SenderShell::class)
            ->setMethods(['in', 'createFile', '_stop', '_newEmail'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->Sender->params = [
            'limit' => 10,
            'template' => 'default',
            'layout' => 'default',
            'config' => 'default',
            'stagger' => false,
        ];

        $this->EmailQueue = TableRegistry::getTableLocator()
            ->get('EmailQueue', ['className' => EmailQueueTable::class]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Sender);
    }

    public function testMainAllWin()
    {
        $email = new TestMailer();
        $email->setTo('you@example.com')
            ->setSubject('About');

        $this->Sender->expects($this->exactly(3))
            ->method('_newEmail')
            ->will($this->returnValue($email));

        $this->Sender->main();

        $emails = $this->EmailQueue
            ->find()
            ->where(['id IN' => [1, 2, 3]])
            ->all()
            ->toList();

        $this->assertEquals(1, $emails[0]['send_tries']);
        $this->assertEquals(2, $emails[1]['send_tries']);
        $this->assertEquals(3, $emails[2]['send_tries']);

        $this->assertFalse($emails[0]['locked']);
        $this->assertFalse($emails[1]['locked']);
        $this->assertFalse($emails[2]['locked']);

        $this->assertTrue($emails[0]['sent']);
        $this->assertTrue($emails[1]['sent']);
        $this->assertTrue($emails[2]['sent']);
    }

    public function testMainAllFail()
    {
        $transport = $this->getMockBuilder(MailTransport::class)
            ->onlyMethods(['send'])
            ->getMock();

        $transport->expects($this->exactly(3))
            ->method('send')
            ->will($this->throwException(new SocketException('fail')));

        $email = new TestMailer();
        $email->setTo('you@example.com')
            ->setSubject('About')
            ->setTransport($transport);

        $this->Sender->expects($this->exactly(3))
            ->method('_newEmail')
            ->with('default')
            ->will($this->returnValue($email));

        $this->Sender->main();

        $emails = $this->EmailQueue
            ->find()
            ->where(['id IN' => [1, 2, 3]])
            ->all()
            ->toList();

        $this->assertEquals(2, $emails[0]['send_tries']);
        $this->assertEquals(3, $emails[1]['send_tries']);
        $this->assertEquals(4, $emails[2]['send_tries']);

        $this->assertFalse($emails[0]['locked']);
        $this->assertFalse($emails[1]['locked']);
        $this->assertFalse($emails[2]['locked']);

        $this->assertFalse($emails[0]['sent']);
        $this->assertFalse($emails[1]['sent']);
        $this->assertFalse($emails[2]['sent']);
    }

    public function testClearLocks()
    {
        $this->EmailQueue->getBatch();
        $this->Sender->clearLocks();
        $this->assertEmpty($this->EmailQueue->findByLocked(true)->toArray());
    }
}
