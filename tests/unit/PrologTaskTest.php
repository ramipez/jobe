<?php

use CodeIgniter\Test\CIUnitTestCase;
use Jobe\PrologTask;

/**
 * @internal
 */
final class PrologTaskTest extends CIUnitTestCase
{
    public function testPrologDefaultFileNameIsProgPl(): void
    {
        $task = new TestablePrologTask('Main.pl', '', []);

        $this->assertSame('prog.pl', $task->defaultFileName('main :- true.'));
    }

    public function testVersionCommandUsesConfiguredSwiplPath(): void
    {
        [$command, $pattern] = PrologTask::getVersionCommand();
        $configured = config('Jobe')->prolog_swipl;
        $expectedExecutable = file_exists($configured) ? $configured : '/usr/bin/' . $configured;

        $this->assertSame($expectedExecutable . ' --version', $command);
        $this->assertSame('/SWI-Prolog version ([0-9._]+)/', $pattern);
    }

    public function testRunCommandTargetsMainGoalAndSourceFile(): void
    {
        $task = new TestablePrologTask('Main.pl', '', []);

        $this->assertSame('main', $task->getRunCommand()[5]);
        $this->assertSame('Main.pl', $task->getTargetFile());
    }
}

class TestablePrologTask extends PrologTask
{
    public function compile()
    {
    }
}