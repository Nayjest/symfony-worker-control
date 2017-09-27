symfony-worker-control
===

Console command for maintaining worker processes

# Installation
`composer require nayjest/symfony-worker-control`

# Testing

Code-style (PSR2):
`composer cs`
 
# Usage

Examples:

`./workers start   --qty=2 "php my_process.php"`
`./workers restart --qty=3 "php my_process.php"`
`php workers maintain --qty=4 "php my_process.php"`
`php workers stop "php my_process.php"`

## Security

If you discover any security related issues, please email mail@vitaliy.in instead of using the issue tracker.

## License

© 2017&mdash;2018 Vitalii Stepanenko

Licensed under the MIT License. 

Please see [License File](LICENSE) for more information.