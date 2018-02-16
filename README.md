# A very simple unit-test component

A very quick learning time unit test component, that uses the command line to
run the tests. Although it is under development, this component works.

## Installation

You need at least php 7.1 and `composer` to use this component:

```bash
sudo apt install composer
```

### From github

Installation:

```bash
git clone git@github.com:osflab/test.git
cd test && composer install
```

### Use osflab/test in your app with composer

To use it in your project, just add `osflab/test` in your composer.json file.

## Usage

To run your tests, use the command line:

```bash
bin/runtests [directory] [filter]
```

The test runner find recursively the `Test.php` files in `[directory]` (default
is current) and run it. The second parameter `[filter]` can be used to run only
certain tests.

Simple example:

```bash
bin/runtests
```

Output:

```
- \Osf\Test\Test ...................................................... [  OK  ]
- 1 test file(s), 2 tests passed, success ^^
```

When this component is installed as a dependency of your project, the `runtests` 
binary is available in `vendor/bin` directory.

Example in the `Application` component project:


```bash
vendor/bin/runtests . Acl
```

Output:

```
- \Osf\Application\Acl\Test ........................................... [  OK  ]
- \Osf\Application\Config\Test ........................................ [ SKIP ]
- 1 test file(s), 51 tests passed, success ^^
```

## Writing Test.php files

The `Test.php` file content look like this:

```php
use Osf\Test\Runner as OsfTest;

class Test extends OsfTest
{
    public static function run()
    {
        self::reset();

        // Your test here
        try {
            self::assert(/* condition */, /* [message if fails] */);
            self::assertEqual(/* calculated */, /* expected */, /* [message if fails] */);
            // ...
        }

        // Displays an exception
        catch (\Exception $e) {
            self::assertFalseException($e);
        }

        return self::getResult();
    }
}
```
