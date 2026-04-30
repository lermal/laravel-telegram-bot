<?php

namespace Lermal\LaravelTelegram\Dispatching;

use Illuminate\Contracts\Container\Container;
use Lermal\LaravelTelegram\Contracts\UpdateHandlerInterface;

class UpdateDispatcher
{
    /**
     * @param  array<int, class-string<UpdateHandlerInterface>>  $handlerClasses
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $handlerClasses = [],
    ) {}

    /**
     * @param  array<string, mixed>  $update
     */
    public function dispatch(array $update): void
    {
        foreach ($this->handlerClasses as $handlerClass) {
            /** @var UpdateHandlerInterface $handler */
            $handler = $this->container->make($handlerClass);

            if ($handler->supports($update)) {
                $handler->handle($update);
            }
        }
    }
}
