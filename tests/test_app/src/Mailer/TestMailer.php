<?php
declare(strict_types=1);

namespace TestApp\Mailer;

use Cake\Mailer\Mailer;

/**
 * Fake TestMailer to test transport without rendering
 * @package EmailQueue\Test\TestCase\Mailer
 */
class TestMailer extends Mailer
{
    /**
     * Override deliver method to skip mail rendering
     *
     * @param string $content
     * @return array
     */
    public function deliver(string $content = '')
    {
        return $this->getTransport()->send($this->message);
    }
}
