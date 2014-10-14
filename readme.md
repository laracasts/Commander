# Laravel Commander

This package gives you an easy way to leverage commands and domain events in your Laravel projects.

## Installation

Per usual, install Commander through Composer.

```js
"require": {
    "laracasts/commander": "~1.0"
}
```

Next, update `app/config/app.php` to include a reference to this package's service provider in the providers array.

```php
'providers' => [
    'Laracasts\Commander\CommanderServiceProvider'
]
```

## Usage

Easily, the most important piece of advice I can offer is to keep in mind that this approach isn't for everything. If you're building a simple CRUD app that does not have much business logic, then you likely don't need this. Still want to move ahead? Okay - onward!

### The Goal

Imagine that you're building an app for advertising job listings. Now, when an employer posts a new job listing, a number of things need to happen, right?
Well, don't put all that stuff into your controller! Instead, let's leverage commands, handlers, and domain events to clean up our code.

### The Controller

To begin, we can inject this package's `CommanderTrait` into your controller (or a BaseController, if you wish). This will give you a couple helper methods to manage the process of passing commands to the command bus.

```php
<?php

use Laracasts\Commander\CommanderTrait;

class JobsController extends \BaseController {

	use CommanderTrait;

	/**
	 * Publish the new job listing.
	 *
	 * @return Response
	 */
	public function store()
	{

	}

}
```

Good? Next, we'll represent this "instruction" (to post a job listing) as a command. This will be nothing more than a simple DTO.

```php
<?php

use Laracasts\Commander\CommanderTrait;
use Acme\Jobs\PostJobListingCommand;

class JobsController extends \BaseController {

	use CommanderTrait;

	/**
	 * Post the new job listing.
	 *
	 * @return Response
	 */
	public function store()
	{
        $this->execute(PostJobListingCommand::class);

		return Redirect::home();
	}
```

Notice how we are representing the user's instruction (or command) as a readable class: `PostJobListingCommand`. The `execute` method will expect the command's class path, as a string. Above, we're using the helpful `PostJobListingCommand::class` to fetch this. Alternatively, you could manually write out the path as a string.

### The Command DTO

Pretty simply, huh? We make a command to represent the instruction, and then we throw that command into a command bus.
Here's what that command might look like:

```php
<?php namespace Acme\Jobs;

class PostJobListingCommand {

    public $title;

    public $description;

    public function __construct($title, $description)
    {
        $this->title = $title;
        $this->description = $description;
    }

}
```

> When you call the `execute` method on the `CommanderTrait`, it will automatically map the data from `Input::all()` to your command. You won't need to worry about doing that manually.

So what exactly does the command bus do? Think of it as a simple utility that will translate this command into an associated handler class that will, well, handle the command! In this case, that means delegating as needed to post the new job listing.

By default, the command bus will do a quick search and replace on the name of the command class to figure out which handler class to resolve out of the IoC container. As such:

- PostJobListingCommand => PostJobListingCommandHandler
- ArchiveJobCommand => ArchiveJobCommandHandler

Make sense? Good. Keep in mind, though, that if you prefer a different naming convention, you can override the defaults. See below.

### Decorating the Command Bus

There may be times when you want to decorate the command bus to first perform some kind of action...maybe you need to first sanitize some data. Well, that's easy. First, create a class that implements the `Laracasts\Commander\CommandBus` contract...

```php
<?php namespace Acme\Jobs;

use Laracasts\Commander\CommandBus;

class JobSanitizer implements CommandBus {

    public function execute($command)
    {
       // sanitize the job data
    }

}
```

...and now reference this class, when you execute the command in your controller.

```php
$this->execute(PostJobListingCommand::class, null, [
    'JobSanitizer'
]);
```

And that's it! Now, you have a hook to sanitize the command/data before it's passed on to the handler class. On that note...

### The Handler Class

Let's create our first handler class now:

```php
<?php namespace Acme\Jobs;

use Laracasts\Commander\CommandHandler;
use Laracasts\Commander\Events\DispatchableTrait;

class PostJobListingCommandHandler implements CommandHandler {

    use DispatchableTrait;

    public function handle($command)
    {
        $job = Job::post($command->title, $command->description);

        $this->dispatchEventsFor($job);

        return $job;
    }

}
```

For this demo, our handler is fairly simple. In real-life, more would be going on here. Notice that `dispatchEventsFor` method? This will handle the process of firing all queued events for your entity. This way, other parts of your app may listen
for when a job has been published, and respond accordingly.

### Raising an Event

Here's a quick and dirty example of what that `Job` model might look like:

```php
<?php namespace Acme\Jobs;

use Laracasts\Commander\Events\EventGenerator;
use Acme\Jobs\Events\JobWasPublished;

class Job extends \Eloquent {

    use EventGenerator;

    protected $fillable = ['title', 'description'];

    public static function post($title, $description)
    {
        // We're ignoring persistence for this demo
        $job = new static(compact('title', 'description'));

        $job->raise(new JobWasPublished($job));

        return $job;
    }
}
```

Pay close to attention to where we raise that event.

```php
$job->raise(new JobWasPublished($job));
```

Once again, this `JobWasPublished` object is nothing more than a simple transport object.

```php
<?php namespace Acme\Jobs\Events;

use Acme\Jobs\Job;

class JobWasPublished {

    public $job;

    public function __construct(Job $job) /* or pass in just the relevant fields */
    {
        $this->job = $job;
    }

}
```

Also, the `raise` method is available through that `EventGenerator` trait. It simply stores the event in an array.

### Event Listeners

Now, because the handler class dispatched all events, that means you can register any number of listeners. The event name to listen for follows, once again, a simple convention:

- Path\To\Raised\Event => Path.To.Raised.Event

So, essentially, we replace slashes with periods to make it appear just a bit more object-oriented. So, if we raise:

- Acme\Jobs\Events\JobWasPublished

Then, the event to listen for will be:

- Acme.Jobs.Events.JobWasPublished

Let's register a basic event listener to fire off an email.

```php
Event::listen('Acme.Jobs.Events.JobWasPublished', function($event)
{
    var_dump('Send a notification email to the job creator.');
});
```

Remember: you can register multiple listeners for the same event. Maybe you also need to do something related to reporting when a job is published. Well, add a new event listener!

Now, this example above uses a simple closure. If you want, you could take a more "catch-all" approach, which this package can help with.

First, let's setup an `EmailNotifier` class that will be given a chance to handle all fired events for our app.

```php
Event::listen('Acme.*', 'Acme\Listeners\EmailNotifier');
```

So, now, any time that you raise an event in the `Acme` namespace, once dispached, the `EmailNotifier` class' handle method will fire. Naturally, though,
we don't need to respond to *every* event! Just a few. Well, once again, we can follow a simple method naming convention to respond to only the events that we are interested in.

The `JobWasPublished` event class will look for a `whenJobWasPublished` method on your event listener. If it exists, it will call it. Otherwise, it'll simply continue on. That means our `EmailNotifier` class might look like so:

```php
<?php namespace Acme\Listeners;

use Laracasts\Commander\Events\EventListener;
use Acme\Jobs\Events\JobWasPublished;

class EmailNotifier extends EventListener {

    public function whenJobWasPublished(JobWasPublished $event)
    {
        var_dump('send an email');
    }

}
```

Because this class extends `EventListener`, that parent class will manage all the details of determining if `whenJobWasPublished` should be called.

## File Generation

You'll likely find yourself manually creating lots and lots of commands and handler classes. Instead, use the Artisan command that is included with this package!
Simply run:

```bash
php artisan commander:generate Acme/Bar/SubscribeUserCommand
```

This will generate both `SubscribeUserCommand` and a `SubscribeUserCommandHandler` classes. By default, it will look for that "Acme" directory within "app/". If your base domain directory is somewhere else, pass the `--base="src"`.

#### The Command

```php
<?php namespace Acme\Bar;

class SubscribeUserCommand {

    /**
     * Constructor
     */
    public function __construct()
    {
    }

}
```

#### The Handler

```php
<?php namespace Acme\Bar;

use Laracasts\Commander\CommandHandler;

class SubscribeUserCommandHandler implements CommandHandler {

    /**
     * Handle the command.
     *
     * @param object $command
     * @return void
     */
    public function handle($command)
    {

    }

}
```

Or, if you also want boilerplate for the properties, you can do that as well.

```bash
php artisan commander:generate Acme/Bar/SubscribeUserCommand --properties="first, last"
```

When you add the `--properties` flag, the handle class will remain the same, however, the command, itself, will be scaffolded, like so:

```php
<?php namespace Acme\Bar;

class SubscribeUserCommand {

    /**
     * @var string
     */
    public $first;

    /**
     * @var string
     */
    public $last;

    /**
     * Constructor
     *
     * @param string first
     * @param string last
     */
    public function __construct($first, $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

}
```

Nifty, ay? That'll save you a lot of time, so remember to use it.

> When calling this command, use forward slashes for your class path: `Acme/Bar/MyCommand`. If you'd rather use backslashes, you'll need to wrap it in quotes.

## That Does It!

This can be complicated stuff to read. Be sure to check out the [Commands and Domain Events](https://laracasts.com/series/commands-and-domain-events) series on [Laracasts](https://laracasts.com) to learn more about this stuff.
