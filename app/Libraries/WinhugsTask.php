<?php

/* ==============================================================
 *
 * WinHugs compatibility mode (Hugs 98 style)
 *
 * ============================================================== 
 *
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Jobe;

class WinhugsTask extends HaskellTask
{
    public function __construct($filename, $input, $params)
    {
        if (!isset($params['haskell_mode'])) {
            $params['haskell_mode'] = 'interpreted';
        }
        parent::__construct($filename, $input, $params);
    }

    public function defaultFileName($sourcecode)
    {
        return 'Main.hs';
    }

    public static function getVersionCommand()
    {
        $ghc = self::ghcExecutable();
        return [$ghc . ' --version', '/version ([0-9._]*)/'];
    }
}
