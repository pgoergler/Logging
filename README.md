Logging
=======

Logger for PHP (PSR-3)


Usage
-----

```php
<?php
    $prefix = '[%datetime%][%name%][%level%][%file%][%function%@%line%][%pid%][%client_ip%][%client_useragent%]';

    $appender1 = new \Logging\Appenders\EchoAppender('test 1', array(\Psr\Log\LogLevel::DEBUG, \Psr\Log\LogLevel::WARNING), $prefix);
    $appender2 = new \Logging\Appenders\CliAppender('test 2', 'DEBUG,NOTICE,INFO,WARNING,ERROR,ALERT,CRITICAL,EMERGENCY', $prefix);
    $appender3 = new \Logging\Appenders\FileAppender('test 3', '*', $prefix, array('filename' => '/tmp/test.log'));
    $logger = new \Logging\Logger('test-logger', array($appender1, $appender2, $appender3));
    
    $logger->debug("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->notice("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->info("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->warning("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->error("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->critical("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->alert("echo \\{foo\\} = {foo}", array('foo' => 123));
    $logger->emergency("echo \\{foo\\} = {foo}", array('foo' => 123));
?>
``


This will output:
```
[2013-02-16 15:34:37,469][test 1][    DEBUG][TestTask.php][testLogging@43][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,469][test 2][    DEBUG][TestTask.php][testLogging@43][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,506][test 2][   NOTICE][TestTask.php][testLogging@44][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,506][test 2][     INFO][TestTask.php][testLogging@45][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,564][test 1][  WARNING][TestTask.php][testLogging@46][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,564][test 2][  WARNING][TestTask.php][testLogging@46][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,565][test 2][    ERROR][TestTask.php][testLogging@47][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,588][test 2][ CRITICAL][TestTask.php][testLogging@48][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,613][test 2][    ALERT][TestTask.php][testLogging@49][30307][paul@localhost][xterm-color]echo {foo} = 123
[2013-02-16 15:34:37,613][test 2][EMERGENCY][TestTask.php][testLogging@50][30307][paul@localhost][xterm-color]echo {foo} = 123
```