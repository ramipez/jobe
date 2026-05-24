<?php

/* ==============================================================
 *
 * SWI-Prolog
 *
 * ============================================================== 
 *
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Jobe;

class PrologTask extends LanguageTask
{
    public function __construct($filename, $input, $params)
    {
        parent::__construct($filename, $input, $params);
        $this->default_params['memorylimit'] = 1200;
        $this->default_params['cputime'] = 10;
    }

    public static function getVersionCommand()
    {
        $swipl = self::swiplExecutable();
        return [$swipl . ' --version', '/SWI-Prolog version ([0-9._]+)/'];
    }

    public function compile()
    {
        $this->executableFileName = $this->sourceFileName;

        if (!self::commandExists(self::swiplExecutable())) {
            $this->cmpinfo = 'SWI-Prolog executable not found';
            return;
        }

        $cmd = self::swiplExecutable() . ' -q -s ' . escapeshellarg(basename($this->sourceFileName)) . ' -g halt';
        list($output, $this->cmpinfo) = $this->runInSandbox($cmd);

        if (!empty($output) && !empty($this->cmpinfo)) {
            $this->cmpinfo = $output . "\n" . $this->cmpinfo;
        }
    }

    public function defaultFileName($sourcecode)
    {
        return 'prog.pl';
    }

    public function getExecutablePath()
    {
        return self::swiplExecutable();
    }

    public function getTargetFile()
    {
        return $this->sourceFileName;
    }

    public function getRunCommand()
    {
        $cmd = [
            self::swiplExecutable(),
            '-q',
            '-s',
            $this->getTargetFile(),
            '-g',
            'main',
            '-t',
            'halt'
        ];
        return array_merge($cmd, $this->getParam('runargs'));
    }

    protected static function swiplExecutable()
    {
        $configured = config('Jobe')->prolog_swipl;
        if (file_exists($configured)) {
            return $configured;
        }
        return '/usr/bin/' . $configured;
    }

    protected static function commandExists($command)
    {
        return file_exists($command) || is_executable($command);
    }
}