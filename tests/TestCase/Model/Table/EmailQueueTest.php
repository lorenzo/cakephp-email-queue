<?php
declare(strict_types=1);

namespace EmailQueue\Test\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use DateTime;
use EmailQueue\EmailQueue;
use EmailQueue\Model\Table\EmailQueueTable;

class EmailQueueTest extends TestCase
{
    /**
     * @var EmailQueueTable
     */
    protected $EmailQueue;

    /**
     * Fixtures.
     *
     * @var array
     */
    public $fixtures = [
        'plugin.EmailQueue.EmailQueue',
    ];

    /**
     * setUp method.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->EmailQueue = TableRegistry::getTableLocator()
            ->get('EmailQueue', ['className' => EmailQueueTable::class]);
    }

    /**
     * testEnqueue method.
     */
    public function testEnqueue()
    {
        $count = $this->EmailQueue->find()->count();
        $this->EmailQueue->enqueue(
            'someone@domain.com',
            ['a' => 'variable', 'some' => 'thing'],
            [
            'subject' => 'Hey!',
            'headers' => ['X-FOO' => 'bar', 'X-BAZ' => 'thing'],
            ]
        );

        $this->assertEquals(++$count, $this->EmailQueue->find()->count());

        $result = $this->EmailQueue->find()
            ->where(['email' => 'someone@domain.com'])
            ->first()
            ->toArray();

        $expected = [
            'email' => 'someone@domain.com',
            'subject' => 'Hey!',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => ['a' => 'variable', 'some' => 'thing'],
            'sent' => false,
            'locked' => false,
            'send_tries' => 0,
            'config' => 'default',
            'headers' => ['X-FOO' => 'bar', 'X-BAZ' => 'thing'],
            'error' => null,
            'from_name' => null,
            'from_email' => null,
        ];
        $sendAt = new Time($result['send_at']);
        unset($result['id'], $result['created'], $result['modified'], $result['send_at']);
        $this->assertEquals($expected, $result);
        $this->assertEquals(gmdate('Y-m-d H'), $sendAt->format('Y-m-d H'));

        $date = new Time('2019-01-11 11:14:15');
        $this->EmailQueue->enqueue(['a@example.com', 'b@example.com'], ['a' => 'b'], ['send_at' => $date, 'subject' => 'Hey!']);
        $this->assertEquals($count + 2, $this->EmailQueue->find()->count());

        $email = $this->EmailQueue
            ->find()
            ->where(['email' => 'a@example.com'])
            ->first();
        $this->assertEquals(['a' => 'b'], $email['template_vars']);
        $this->assertEquals($date, $email['send_at']);

        $email = $this->EmailQueue
            ->find()
            ->where(['email' => 'b@example.com'])
            ->first();
        $this->assertEquals(['a' => 'b'], $email['template_vars']);
        $this->assertEquals($date, $email['send_at']);

        $result = $this->EmailQueue->enqueue(
            'c@example.com',
            ['a' => 'c'],
            ['subject' => 'Hey', 'send_at' => $date, 'config' => 'other', 'template' => 'custom', 'layout' => 'email']
        );
        $this->assertTrue($result);
        $email = $this->EmailQueue
            ->find()
            ->where(['email' => 'c@example.com'])
            ->first();
        $this->assertEquals(['a' => 'c'], $email['template_vars']);
        $this->assertEquals($date, $email['send_at']);
        $this->assertEquals('other', $email['config']);
        $this->assertEquals('custom', $email['template']);
        $this->assertEquals('email', $email['layout']);
    }

    /**
     * testGetBatch method.
     */
    public function testGetBatch()
    {
        $batch = $this->EmailQueue->getBatch();
        $this->assertEquals(['email-1', 'email-2', 'email-3'], collection($batch)->extract('id')->toList());

        //At this point previous batch should be locked and next call should return an empty set
        $batch = $this->EmailQueue->getBatch();
        $this->assertEmpty($batch);

        //Let's change send_at date for email-6 to get it on a batch
        $this->EmailQueue->updateAll(['send_at' => '2011-01-01 00:00'], ['id' => 'email-6']);
        $batch = $this->EmailQueue->getBatch();
        $this->assertEquals(['email-6'], collection($batch)->extract('id')->toList());
    }

    /**
     * testReleaseLocks method.
     */
    public function testReleaseLocks()
    {
        $batch = $this->EmailQueue->getBatch();
        $this->assertNotEmpty($batch);
        $this->assertEmpty($this->EmailQueue->getBatch());
        $this->EmailQueue->releaseLocks(collection($batch)->extract('id')->toList());
        $this->assertEquals($batch, $this->EmailQueue->getBatch());
    }

    /**
     * testClearLocks method.
     */
    public function testClearLocks()
    {
        $batch = $this->EmailQueue->getBatch();
        $this->assertNotEmpty($batch);
        $this->assertEmpty($this->EmailQueue->getBatch());
        $this->EmailQueue->clearLocks();
        $batch = $this->EmailQueue->getBatch();
        $this->assertEquals(['email-1', 'email-2', 'email-3', 'email-5'], collection($batch)->extract('id')->toList());
    }

    /**
     * testSuccess method.
     */
    public function testSuccess()
    {
        $this->EmailQueue->success('email-1');
        $this->assertEquals(1, $this->EmailQueue->get('email-1')->sent);
    }

    /**
     * testFail method.
     */
    public function testFail()
    {
        $this->EmailQueue->fail('email-1');
        $this->assertEquals(2, $this->EmailQueue->get('email-1')->send_tries);

        $this->EmailQueue->fail('email-1');
        $this->assertEquals(3, $this->EmailQueue->get('email-1')->send_tries);
    }

    public function testProxy()
    {
        $date = new DateTime('2019-01-11 11:14:15');
        $result = EmailQueue::enqueue(
            'c@example.com',
            ['a' => 'c'],
            ['subject' => 'Hey', 'send_at' => $date, 'config' => 'other', 'template' => 'custom', 'layout' => 'email']
        );
        $this->assertTrue($result);
        $email = $this->EmailQueue->find()
            ->where(['email' => 'c@example.com'])
            ->first()
            ->toArray();
        $this->assertEquals(['a' => 'c'], $email['template_vars']);
        $this->assertEquals($date, $email['send_at']);
        $this->assertEquals('other', $email['config']);
        $this->assertEquals('custom', $email['template']);
        $this->assertEquals('email', $email['layout']);
    }
}
