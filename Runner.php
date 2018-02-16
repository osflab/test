<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Test;

use Osf\Console\ConsoleHelper as Console;
use Exception;

/**
 * Simple unit test manager for OSF components
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 11 sept. 2013
 * @package osf
 * @subpackage test
 */
class Runner
{
    private static $count            = 0;
    private static $totalCount       = 0;
    private static $totalCountErrors = 0;
    private static $totalCountFiles  = 0;
    private static $result           = false;

    /**
     * Simple assert of the test application
     * @param boolean $condition
     * @param string $errorMessage
     * @param boolean $exitOnError
     * @return boolean
     */
    protected static function assert($condition, string $errorMessage = '', bool $exitOnError = false): bool
    {
        self::$totalCount++;
        self::$count++;
        if (!$condition) {
            $trace = debug_backtrace();
            $errorPrefix = (isset($trace[1]['class']) ? ($trace[1]['class'] 
                    . (isset($trace[1]['function']) ? '::' . $trace[1]['function'] : '') 
                    . (isset($trace[1]['line']) ? ' (' . $trace[1]['line'] . ')' : '') 
                    . ' -> ') : '')
                    . $trace[0]['class'] . ' (' . $trace[0]['line'] . ")\n    ";
            self::addError($errorPrefix . $errorMessage);
            self::$totalCountErrors++;
            if ($exitOnError) {
                exit;
            }
        }
        return (bool) $condition;
    }
    
    /**
     * Equality assert
     * @param mixed $calculatedValue
     * @param mixed $expectedValue
     * @param bool $identical
     * @param bool $exitOnError
     * @return bool
     */
    protected static function assertEqual($calculatedValue, $expectedValue, bool $identical = true, bool $exitOnError = false): bool
    {
        $result = $identical 
                ? $calculatedValue === $expectedValue 
                : $calculatedValue ==  $expectedValue;
        $msg = '';
        if (!$result) {
            $value = preg_replace('/  +/', ' ', str_replace("\n", '', print_r($calculatedValue, true)));
            $msg = '     Found: ' . self::varExport($calculatedValue) . ",\n      Expected: " . self::varExport($expectedValue);
        }
        return self::assert($result, $msg, $exitOnError);
    }
    
    private static function varExport($var): string
    {
        return preg_replace('/  +/', ' ', str_replace("\n", '', print_r($var, true)));
    }
    
    /**
     * Assert false and display exception
     * @param Exception $e
     */
    protected static function assertFalseException(\Exception $e): bool
    {
        $msg = 'EXCEPTION: ' . $e->getMessage();
        $msg .= "\n    " . $e->getFile() . ' ('  . $e->getLine() . ')';
        self::assert(false, $msg);
    }

    /**
     * Reset the test scope (new file)
     */
    protected static function reset(): void
    {
        self::$totalCountFiles++;
        self::$count = 0;
        self::$result = false;
        
        if (class_exists('\Osf\Container\OsfContainer')) {
            \Osf\Container\OsfContainer::setMockNamespace('MOCK-' . self::$totalCountFiles);
        }
    }

    /**
     * Get the table of errors
     * @return boolean|array
     */
    protected static function getResult()
    {
        if (self::$result === false && self::$count == 0) {
            return true;
        }
        return self::$result;
    }

    /**
     * Add an error in the table
     * @param string $message
     */
    private static function addError($message): void
    {
        if (self::$result === false) {
            self::$result = array();
        }
        self::$result[] = $message;
    }

    /**
     * Lance les jeux de test
     * @return boolean|array
     */
    public static function run()
    {
        self::reset();
        return self::getResult();
    }

    /**
     * Run recursively a testsuite in a directory.
     * @param string $path
     * @return boolean
     */
    public static function runDirectory(
            string $path, 
            string $testSuffix = '/Test', 
            ?string $filter = null,
            ?string $exclude = '/vendor/'): bool
    {
        foreach (new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($path,
        \RecursiveDirectoryIterator::KEY_AS_PATHNAME),
        \RecursiveIteratorIterator::CHILD_FIRST) as $file => $info) {
            if (!preg_match('#^' . $path . '.*' . $testSuffix . '\.php$#', $file) ||
                ($exclude !== null && strpos($file, $exclude) !== false)) {
                continue;
            }
            $matches = [];
            if (!preg_match('/^.*\nnamespace ([a-zA-Z\\\\]+); *\n.*$/m', file_get_contents($file), $matches)) {
                trigger_error($file . ' namespace not found');
            }
            $className = '\\' . strtr($matches[1] . '/' . basename($file, '.php'), '/', '\\');
            self::runClass($className, $filter);
        }
        echo "- " . self::$totalCountFiles . " test file(s), ";
        echo self::$totalCount . ' tests passed, ';
        if (self::$totalCountErrors > 0) {
            echo chr(033) . '[1;31m' . self::$totalCountErrors . ' failed' . chr(033) . '[0;0m';
        } else {
            echo chr(033) . '[1;32msuccess ^^' . chr(033) . '[0;0m';
        }
        echo "\n";
        
        return !self::$totalCountErrors;
    }
    
    /**
     * Run a single test class
     * @param string $className
     * @param string|null $filter
     * @return bool
     */
    private static function runClass(string $className, ?string $filter): bool
    {
        echo Console::beginActionMessage($className);
        flush();
        if (!is_null($filter) && !preg_match($filter, $className)) {
            echo Console::endActionSkip();
            flush();
            return false;
        }
        try {
            $result = call_user_func(array($className, 'run'));
        } catch (\Exception $e) {
            $result = array($e->getMessage(), 'Exception catched in the test library :', 
            $e->getFile() . '(' . $e->getLine() . ')');
            if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'development') {
                echo Console::endActionFail();
                echo self::exceptionToStr($e);
                exit;
            }
        }
        if ($result === false) {
            echo Console::endActionOK();
        } else if ($result === true) {
            echo Console::endActionSkip();
        } else {
            echo Console::endActionFail();
            foreach ($result as $line) {
                echo '  - ' . $line . "\n";
            }
        }
        flush();
        
        return true;
    }
    
    /**
     * @param Exception $e
     * @return string
     */
    protected static function exceptionToStr(Exception $e): string
    {
        $msg  = 'ERR: ' . $e->getMessage() . ' (' . $e->getCode() . ")\n";
        $msg .= '     in file ' . $e->getFile() . ':' . $e->getLine() . "\n";
        $msg .= $e->getTraceAsString() . "\n";
        return $msg;
    }
}
