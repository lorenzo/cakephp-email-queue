# CakePHP Email Queue plugin #

This plugin provides an interface for creating emails on the fly and
store them in a queue to be processed later by an offline worker using a
cakephp shell command.

It also contains a handy shell for previewing queued emails, a very handy tool for modifying
email templates and watching the result.

## Requirements ##

* CakePHP 4.x

## Installation ##

```sh
composer require lorenzo/cakephp-email-queue
```

The plugin uses Debug email transport, so make sure your email config contain it:

```
'EmailTransport' => [
        'Debug' => [
            'className' => 'Debug'
        ],
]
```

### Enable plugin

```sh
bin/cake plugin load EmailQueue
```

### Load required database table

In order to use this plugin, you need to create a database table.
Required SQL is located at

	# config/Schema/email_queue.sql

Just load it into your database. You are free to change the file to use an integer primary
key instead of UUIDs.

Or run migrations command:

    bin/cake migrations migrate --plugin EmailQueue

## Usage

Whenever you need to send an email, use the EmailQueue model to create
and queue a new one by storing the correct data:

    use EmailQueue\EmailQueue;
    EmailQueue::enqueue($to, $data, $options);

`enqueue` method receives 3 arguments:

- First argument is a string or array of email addresses that will be treated as recipients.
- Second arguments is an array of view variables to be passed to the
  email template
- Third arguments is an array of options, possible options are
 * `subject`: Email's subject
 * `send_at`: date time sting representing the time this email should be sent at (in UTC)
 * `template`:  the name of the element to use as template for the email message. (maximum supported length is 100 chars)
 * `layout`: the name of the layout to be used to wrap email message
 * `format`: Type of template to use (html, text or both)
 * `headers`: A key-value list of headers to send in the email
 * `theme`: The View Theme to find the email templates
 * `config`: the name of the email config to be used for sending
 * `from_name`: String with from name. Must be supplied together with `from_email`.
 * `from_email`: String with from email. Must be supplied together with `from_name`.

### Previewing emails

It is possible to preview emails that are still in the queue, this is very handy during development to check if the rendered
email looks at it should; no need to queue the email again, just make the changes to the template and run the preview again:

	# bin/cake EmailQueue.preview

### Sending emails

Emails should be sent using bundled Sender command, use `-h` modifier to
read available options

	# bin/cake EmailQueue.sender -h

You can configure this command to be run under a cron or any other tool
you wish to use.

# Contributing

## Run the tests

```
./vendor/bin/phpunit tests/
```

## Check style
```
./vendor/bin/phpcs ./src ./tests/ --standard=vendor/cakephp/cakephp-codesniffer/CakePHP
```
