<?php
namespace EmailQueue\Test\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOutput;
use Cake\Mailer\Email;
use Cake\Network\Exception\SocketException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\View\ViewBuilder;
use EmailQueue\Model\Table\EmailQueueTable;
use EmailQueue\Shell\SenderShell;
use PHPUnit\Framework\MockObject\MockObject;

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
        'plugin.EmailQueue.EmailQueue',
    ];

    /**
     * @var EmailQueueTable
     */
    protected $EmailQueue;

    /**
     * setUp method.
     */
    public function setUp()
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

    public function testMainAllWin()
    {
        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->setMethods(['setTemplate', 'setLayout', 'setTheme'])
            ->getMock();

        $email = $this->getMockBuilder(Email::class)
            ->setMethods(['setTo', 'setViewVars', 'send', 'setSubject', 'setEmailFormat', 'viewBuilder'])
            ->disableOriginalConstructor()
            ->getMock();

        $email->method('viewBuilder')
            ->willReturn($viewBuilder);

        $this->Sender->params['template'] = 'other';
        $this->Sender->params['layout'] = 'custom';
        $this->Sender->params['config'] = 'something';

        $this->Sender->expects($this->exactly(3))
            ->method('_newEmail')
            ->with('something')
            ->will($this->returnValue($email));

        $email->expects($this->exactly(3))
            ->method('send')
            ->will($this->returnValue(true));

        $email->expects($this->exactly(3))
            ->method('setTo')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))
            ->method('setSubject')
            ->with('Free dealz')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))
            ->method('setEmailFormat')
            ->with('both')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))
            ->method('setViewVars')
            ->with(['a' => 1, 'b' => 2])
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setLayout')
            ->with('custom')
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setTheme')
            ->with('')
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setTemplate')
            ->with('other')
            ->will($this->returnSelf());

        $this->Sender->main();

        $emails = $this->EmailQueue
            ->find()
            ->where(['id IN' => ['email-1', 'email-2', 'email-3']])
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
        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->setMethods(['setTemplate', 'setLayout', 'setTheme'])
            ->getMock();

        $email = $this->getMockBuilder(Email::class)
            ->setMethods(['setTo', 'setViewVars', 'send', 'setSubject', 'setEmailFormat', 'viewBuilder'])
            ->disableOriginalConstructor()
            ->getMock();

        $email->method('viewBuilder')
            ->willReturn($viewBuilder);

        $this->Sender->expects($this->exactly(3))
            ->method('_newEmail')
            ->with('default')
            ->will($this->returnValue($email));

        $email->expects($this->exactly(3))
            ->method('send')
            ->will($this->throwException(new SocketException('fail')));

        $email->expects($this->exactly(3))
            ->method('setTo')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))
            ->method('setSubject')
            ->with('Free dealz')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))
            ->method('setEmailFormat')
            ->with('both')
            ->will($this->returnSelf());

        $email->expects($this->exactly(3))->method('setViewVars')
            ->with(['a' => 1, 'b' => 2])
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setLayout')
            ->with('default')
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setTemplate')
            ->with('default')
            ->will($this->returnSelf());

        $viewBuilder->expects($this->exactly(3))
            ->method('setTheme')
            ->with('')
            ->will($this->returnSelf());

        $this->Sender->main();

        $emails = $this->EmailQueue
            ->find()
            ->where(['id IN' => ['email-1', 'email-2', 'email-3']])
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
