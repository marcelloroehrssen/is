<?php
header("Content-type: text/html");
//
// Run composer with a PHP script in browser
//
// http://stackoverflow.com/questions/17219436/run-composer-with-a-php-script-in-browser
error_reporting(E_ALL);
ini_set('display_errors', 1);
// 600 seconds = 10 minutes
set_time_limit(600);
ini_set('max_execution_time', 600);
// https://getcomposer.org/doc/articles/troubleshooting.md#memory-limit-errors
ini_set('memory_limit', '-1');

// Download composer
/*$composerPhar = __DIR__ . '/../composer.phar';
if (!file_exists($composerPhar)) {
    $data = file_get_contents('https://getcomposer.org/composer.phar');
    file_put_contents($composerPhar, $data);
    unset($data);
}*/

require_once 'phar://' . str_replace('\\', '/', __DIR__) . '/../composer.phar/src/bootstrap.php';

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class HtmlOutput extends \Symfony\Component\Console\Output\Output
{
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = false, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
        // tell php to automatically flush after every output
        $this->disableOb();
    }
    protected function disableOb()
    {
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);
        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);
        // Clear, and turn off output buffering
        while (ob_get_level() > 0) {
            // Get the curent level
            $level = ob_get_level();
            // End the buffering
            ob_end_clean();
            // If the current level has not changed, abort
            if (ob_get_level() == $level)
                break;
        }
        // Disable apache output buffering/compression
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
            apache_setenv('dont-vary', '1');
        }
    }
    protected function h($text)
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    public function writeln($messages, $options = 0)
    {
        $this->write($messages, true, $options);
    }
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $this->doWrite($messages, $newline);
    }
    protected function doWrite($message, $newline)
    {
        $message = str_replace("\n", "<br>\n", $message);
        echo $this->h($message);
        if ($newline) {
            echo "<br>\n";
        }
        if (ob_get_length()) {
            ob_flush();
            flush();
        }
    }
}
//exec('rm -r vendor');
// change out of the webroot so that the vendors file is not created in
// a place that will be visible to the intahwebz
chdir('../');
//
// Composer\Factory::getHomeDir() method
// needs COMPOSER_HOME environment variable set
putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');
// Improve performance when the xdebug extension is enabled
putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');
// call `composer install` command programmatically
$output = new HtmlOutput();
$output->writeln('Run: composer install');
try {
    $params = array(
        'command' => 'update',
        // '--no-dev' => true,
        '--optimize-autoloader' => true,
        '--no-suggest' => true,
        '--no-interaction' => true,
        '--no-progress' => true,
        // '--verbose' => true
    );
    $input = new ArrayInput($params);
    $application = new Application();
    $application->setAutoExit(false);
    $application->run($input, $output);
} catch (Exception $ex) {
    $output->writeln($ex->getMessage());
}
$output->writeln("Done.");