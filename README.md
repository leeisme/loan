# SmartPay Developer Test

This is a simple web application that displays estimated loan details for a user.  There are no database requirements, so setup should be fairly simple.  To use the Symfony server, you will need to install the [symfony cli](https://symfony.com/download).
If you need a dev environment, let us know, and we can set up an aws instance for you to work on.

The requested changes are:

1. Apply styling to the page to make it visually appealing
	1. Bootstrap 4 is included, but feel free to replace it
2. Add a field for the user to input their monthly gross income, and add check that the loan payment is not more than 15% of their monthly income
3. Display the APR (including the Origination Fee)
4. Add a collapsible amortization table (should show each payment's ( amount, interest, principal, and remaining balance)
5. Add a way to email the results as a pdf to the user

Please clone the repository, make the changes, and send us a link to your repo when you are done.

## Installation

Use composer to install packages

```bash
composer install
```

## Usage

Start Server using [Symfony's local server](https://symfony.com/doc/current/setup/symfony_server.html)

```bash
symfony server:start
```



## Unit Tests
```bash
./bin/phpunit
```
