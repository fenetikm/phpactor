<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Editor\OpenFileResponse;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;
use Phpactor\Application\ClassMover;
use Phpactor\Rpc\Editor\CollectionResponse;
use Phpactor\Rpc\Editor\EchoResponse;
use Phpactor\Rpc\Editor\CloseFileResponse;
use Phpactor\Rpc\Editor\Input\ConfirmInput;

class ClassMoveHandler extends AbstractHandler
{
    const PARAM_SOURCE_PATH = 'source_path';
    const PARAM_DEST_PATH = 'dest_path';
    const PARAM_CONFIRMED = 'confirmed';

    /**
     * @var ClassMover
     */
    private $classMove;

    /**
     * @var string
     */
    private $defaultFilesystem;

    public function __construct(ClassMover $classMove, string $defaultFilesystem)
    {
        $this->classMove = $classMove;
        $this->defaultFilesystem = $defaultFilesystem;
    }

    public function name(): string
    {
        return 'move_class';
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_SOURCE_PATH => null,
            self::PARAM_DEST_PATH => null,
            self::PARAM_CONFIRMED => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (false === $arguments[self::PARAM_CONFIRMED]) {
            return EchoResponse::fromMessage('Cancelled');
        }

        $this->requireArgument(self::PARAM_DEST_PATH, TextInput::fromNameLabelAndDefault(
            self::PARAM_DEST_PATH,
            'Move to: ',
            $arguments[self::PARAM_SOURCE_PATH]
        ));

        if (null !== $arguments[self::PARAM_DEST_PATH] && null === $arguments[self::PARAM_CONFIRMED]) {
            $this->requireArgument(self::PARAM_CONFIRMED, ConfirmInput::fromNameAndLabel(
                self::PARAM_CONFIRMED,
                'WARNING: This command will move the class and update ALL references in the git tree.' . PHP_EOL .
                '         It is not guaranteed to succeed. COMMIT YOUR WORK FIRST!' . PHP_EOL .
                'Are you sure? :'
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $this->classMove->move(
            new NullLogger(),
            $this->defaultFilesystem,
            $arguments[self::PARAM_SOURCE_PATH],
            $arguments[self::PARAM_DEST_PATH]
        );

        return CollectionResponse::fromActions([
            CloseFileResponse::fromPath($arguments[self::PARAM_SOURCE_PATH]),
            OpenFileResponse::fromPath($arguments[self::PARAM_DEST_PATH])
        ]);
    }
}
