<?php

namespace Phpactor\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Application\Status;
use Phpactor\Container\SourceCodeFilesystemExtension;

class StatusTest extends TestCase
{
    /**
     * @var FilesystemRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->registry = $this->prophesize(FilesystemRegistry::class);
        $this->status = new Status($this->registry->reveal());
    }

    public function testStatusNoComposerOrGit()
    {
        $this->registry->names()->willReturn(['simple']);
        $diagnostics = $this->status->check();
        $this->assertCount(2, $diagnostics['bad']);
    }

    public function testStatusComposerOrGit()
    {
        $this->registry->names()->willReturn([
            SourceCodeFilesystemExtension::FILESYSTEM_SIMPLE,
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER,
        ]);
        $diagnostics = $this->status->check();
        $this->assertCount(2, $diagnostics['good']);
    }
}
