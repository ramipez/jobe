<?php

use CodeIgniter\Test\CIUnitTestCase;
use Jobe\HaskellTask;
use Jobe\WinhugsTask;

/**
 * @internal
 */
final class HaskellTaskTest extends CIUnitTestCase
{
    public function testHaskellDefaultFileNameIsProgHs(): void
    {
        $task = new TestableHaskellTask('Main.hs', '', ['haskell_mode' => 'interpreted']);

        $this->assertSame('prog.hs', $task->defaultFileName('main = putStrLn "hi"'));
    }

    public function testVersionCommandUsesConfiguredGhcPath(): void
    {
        [$command, $pattern] = HaskellTask::getVersionCommand();
        $configured = config('Jobe')->haskell_ghc;
        $expectedExecutable = file_exists($configured) ? $configured : '/usr/bin/' . $configured;

        $this->assertSame($expectedExecutable . ' --version', $command);
        $this->assertSame('/version ([0-9._]*)/', $pattern);
    }

    public function testInterpretedModeUsesRunghcAndTargetsSourceFile(): void
    {
        $task = new TestableHaskellTask('Main.hs', '', ['haskell_mode' => 'interpreted']);
        $task->setInterpretedMode(true);

        $configured = config('Jobe')->haskell_runghc;
        $expectedExecutable = file_exists($configured) ? $configured : '/usr/bin/' . $configured;

        $this->assertSame($expectedExecutable, $task->getExecutablePath());
        $this->assertSame('Main.hs', $task->getTargetFile());
    }

    public function testCompiledModeUsesBuiltBinaryAndNoTargetFile(): void
    {
        $task = new TestableHaskellTask('Main.hs', '', ['haskell_mode' => 'compiled']);
        $task->setInterpretedMode(false);
        $task->executableFileName = 'prog.hs.exe';

        $this->assertSame('./prog.hs.exe', $task->getExecutablePath());
        $this->assertSame('', $task->getTargetFile());
    }

    public function testWinhugsDefaultsToMainFileNameAndInterpretedExecution(): void
    {
        $task = new TestableWinhugsTask('Main.hs', '', []);

        $this->assertSame('Main.hs', $task->defaultFileName('main = putStrLn "hi"'));
        $this->assertSame('Main.hs', $task->getTargetFile());
    }
}

class TestableHaskellTask extends HaskellTask
{
    public function setInterpretedMode(bool $value): void
    {
        $this->interpretedMode = $value;
    }

    public function compile()
    {
    }
}

class TestableWinhugsTask extends WinhugsTask
{
    public function compile()
    {
    }
}
