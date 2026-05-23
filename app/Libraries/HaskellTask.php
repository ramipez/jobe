<?php

/* ==============================================================
 *
 * Haskell (GHC / runghc)
 *
 * ============================================================== 
 *
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Jobe;

class HaskellTask extends LanguageTask
{
    protected bool $interpretedMode = false;

    public function __construct($filename, $input, $params)
    {
        parent::__construct($filename, $input, $params);
        $this->default_params['memorylimit'] = 1200;
        $this->default_params['cputime'] = 10;
        $this->default_params['compileargs'] = ['-O0'];

        $requestedMode = strtolower($params['haskell_mode'] ?? 'compiled');
        $this->interpretedMode = in_array($requestedMode, ['interpreted', 'runghc'], true);

        if (!$this->interpretedMode && !self::commandExists(self::ghcExecutable())) {
            // Fallback to interpreted mode when GHC is not available.
            $this->interpretedMode = true;
        }
    }

    public static function getVersionCommand()
    {
        $ghc = self::ghcExecutable();
        return [$ghc . ' --version', '/version ([0-9._]*)/'];
    }

    public function compile()
    {
        if ($this->interpretedMode) {
            $this->executableFileName = $this->sourceFileName;

            // If GHC is present, use syntax-only check so syntax issues map to compile errors.
            if (self::commandExists(self::ghcExecutable())) {
                $cmd = self::ghcExecutable() . ' -fno-code ' . $this->sourceFileName;
                list($output, $this->cmpinfo) = $this->runInSandbox($cmd);
                if (!empty($output) && !empty($this->cmpinfo)) {
                    $this->cmpinfo = $output . "\n" . $this->cmpinfo;
                }
            }
            return;
        }

        $src = basename($this->sourceFileName);
        $this->executableFileName = 'prog.hs.exe';
        $compileArgs = implode(' ', $this->getParam('compileargs'));
        $cmd = self::ghcExecutable() . " {$compileArgs} -o {$this->executableFileName} {$src}";
        list($output, $this->cmpinfo) = $this->runInSandbox($cmd);

        // Infrastructure fallback only (not for user compile errors).
        if (!empty($this->cmpinfo) && self::commandMissingError($this->cmpinfo) && self::commandExists(self::runghcExecutable())) {
            $this->cmpinfo = '';
            $this->interpretedMode = true;
            $this->executableFileName = $this->sourceFileName;
        }
    }

    public function defaultFileName($sourcecode)
    {
        return 'prog.hs';
    }

    public function getExecutablePath()
    {
        if ($this->interpretedMode) {
            return self::runghcExecutable();
        }
        return './' . $this->executableFileName;
    }

    public function getTargetFile()
    {
        if ($this->interpretedMode) {
            return $this->sourceFileName;
        }
        return '';
    }

    protected static function ghcExecutable()
    {
        $configured = config('Jobe')->haskell_ghc;
        if (file_exists($configured)) {
            return $configured;
        }
        return '/usr/bin/' . $configured;
    }

    protected static function runghcExecutable()
    {
        $configured = config('Jobe')->haskell_runghc;
        if (file_exists($configured)) {
            return $configured;
        }
        return '/usr/bin/' . $configured;
    }

    protected static function commandExists($command)
    {
        return file_exists($command) || is_executable($command);
    }

    protected static function commandMissingError($message)
    {
        return strpos($message, 'No such file or directory') !== false ||
            strpos($message, 'not found') !== false;
    }
}
