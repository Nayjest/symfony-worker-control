symfony-worker-control
===

Console command for maintaining worker processes

## Installation

**Installation as lib to existing project:**

`composer require nayjest/symfony-worker-control`

**Installation as standalone script:**

`git clone git@github.com:Nayjest/symfony-worker-control.git && cd symfony-worker-control && composer install && chmod +x workers`

## Testing

Code-style (PSR2):
`composer cs`

Wroker for tests: `tests/example-worker.php`
 
## Usage
`workers [--qty QTY] [-o|--output OUTPUT] [-e|--errors ERRORS] [--] <action> <cmd>`

Command may be executed directly (`./workers ...`) or as argument of php command (`php workers ...`).

See `./workers --help` for help.

### Actions

**start** -- start QTY processes

**restart** -- stop all processes and then start QTY processes

**stop** -- stop all processes

**maintain** -- start QTY - N processes, where N &mdash; quantity of currently running processes.

**count** -- print count of running processes

**list** -- print information about running processes

### Options

**--qty=\<value\>** &mdash; specifies quantity of processes to start/restart/maintain
If value isn't specified, DEFAULT_WORKER_QTY environment variable will be used.
if DEFAULT_WORKER_QTY isn't defined, qty = 1

**--output=\<value\> -o \<value\>** output file for workers (STDOUT + STDERR if --errors not specified), following placeholder: {i} will be replaced to process number. Default value: /dev/null

**--errors=\<value\> -e \<value\>** output file for workers (STDERR), following placeholder: {i} will be replaced to process number.

**--quiet -q**  Do not output any message

Help:
`./workers --help`

Examples:

`./workers start   --qty=2 "php my_process.php"`

`./workers restart --qty=3 "my_process.php"`

`php workers maintain --qty=4 "php my_process.php"`

`php workers stop "php my_process.php"`

## Security

If you discover any security related issues, please email mail@vitaliy.in instead of using the issue tracker.

## License

© 2017&mdash;2018 Vitalii Stepanenko

Licensed under the MIT License. 

Please see [License File](LICENSE) for more information.
