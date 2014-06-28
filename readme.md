# Laravel Commander

This package gives you an easy way to leverage commands and domain events in your Laravel projects.

## Installation

Per usual, install Commander through Composer.

```js
"require": {
    "laracasts/commander": "1.1.*"
}
```

Next, update `app/config/app.php` to include a reference to this package's service provider in the providers array.

```php
'providers' => [
    'Laracasts\Commander\CommanderServiceProvider'
]
```

## Usage

Easily, the most important piece of advice I can offer is to keep in mind that this approach isn't for everything. If you're building a simple CRUD app that does not
have much business logic, then you likely don't need this. Still want to move ahead? Okay - onward!

### The Goal

Imagine that you're building an app for advertising job listings. Now, when an employer posts a new job listing, a number of things need to happen, right?
Well, don't put all that stuff into your controller! Instead, let's leverage commands, handlers, and domain events to clean up our code.

### The Controller

To begin, we can inject this package's command bus into your controller (or a BaseController, if you wish).

```php
<?php

use Laracasts\Commander\CommandBus;

class JobsController extends \BaseController {

	protected $commandBus;

	public function __construct(CommandBus $commandBus)
	{
		$this->commandBus = $commandBus;
	}

	/**
	 * Post the new job listing.
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

use Laracasts\Commander\CommandBus;
use Acme\Jobs\PostJobListingCommand;

class JobsController extends \BaseController {

	protected $commandBus;

	public function __construct(CommandBus $commandBus)
	{
		$this->commandBus = $commandBus;
	}

	/**
	 * Post the new job listing.
	 *
	 * @return Response
	 */
	public function store()
	{
	    // We'll just simulate form data as the two arguments...
		$command = new PostJobListingCommand('Demo Title', 'Demo Description');

		$this->commandBus->execute($command);

		return Redirect::home();
	}
```

> Pay attention to the namespaces for these classes to get an idea for how you might arrange your directory tree.

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

So what exactly does the command bus do? Think of it as a simple utility that will translate this command into an associated handler class that will, well, handle the command! In this case, that means delegating as needed to post the new job listing.

By default, the command bus will do a quick search and replace on the name of the command class to figure out which handler class to resolve out of the IoC container. As such:

- PostJobListingCommand => PostJobListingCommandHandler
- ArchiveJobCommand => ArchiveJobCommandHandler

Make sense?

### The Handler Class

Let's create that first handler class now:

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

### Validation

This package also includes a validation trigger automatically. As an example, when you throw a command into the command bus, it will also determine whether an associated validator object exists. If it does,
it will call a `validate` method on this class. If it doesn't exist, it'll simply continue on. So, this gives you a nice hook to perform validation before executing the command and firing domain events.
The convention is:

- PostJobListingCommand => PostJobListingValidator

So, simply create that class, and include a `validate` method, which we'll receive the `PostJobListingCommand` object. Then, perform your validation however you normally do. I recommend that, for failed validation, you throw an exception - perhaps `ValidationFailedException`. This way, either within your controller - or even `global.php` - you can handle failed validation appropriately (probably by linking back to the form and notifying the user).

## Overriding Paths

By default, this package makes some assumptions about your file structure. As demonstrated above:

- Path/To/PostJobListingCommand => Path/To/PostJobListingCommandHandler
- Path/To/PostJobListingCommand => Path/To/PostJobListingValidator

Perhaps you had something different in mind. No problem! Just create your own command translator class that implements the `Laracasts\Commander\CommandTranslator` interface. This interface includes two methods:

- `toCommandHandler`
- `toValidator`

Maybe you want to place your validators within a `Validators/` directory. Okay:

```php
<?php namespace Acme\Core;

use Laracasts\Commander\CommandTranslator;

class MyCommandTranslator implements CommandTranslator {

    /**
     * Translate a command to its handler counterpart
     *
     * @param $command
     * @return mixed
     * @throws HandlerNotRegisteredException
     */
    public function toCommandHandler($command)
    {
        $handler = str_replace('Command', 'Handler', get_class($command));

        if ( ! class_exists($handler))
        {
            $message = "Command handler [$handler] does not exist.";

            throw new HandlerNotRegisteredException($message);
        }

        return $handler;
    }

    /**
     * Translate a command to its validator counterpart
     *
     * @param $command
     * @return mixed
     */
    public function toValidator($command)
    {
        $segments = explode('\\', get_class($command));

        array_splice($segments, -1, false, 'Validators');

        return str_replace('Command', 'Validator', implode('\\', $segments));
    }

}
```

Now, a `Path/To/MyGreatCommand` will look for a `Path/To/Validators/MyGreatValidator` class instead.

> It might be useful to copy and paste the `Laracasts\Commander\BasicCommandTranslator` class, and then modify as needed.

The only remaining step is to update the binding in the IoC container.

```php
// We want to use our own custom translator class
App::bind(
    'Laracasts\Commander\CommandTranslator',
    'Acme\Core\MyCommandTranslator'
);
```

Done!

## That Does It!

This can be complicated stuff to read. Be sure to check out the [Commands and Domain Events](https://laracasts.com/series/commands-and-domain-events) series on [Laracasts](https://laracasts.com) to learn more about this stuff.