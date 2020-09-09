<?php

namespace Emberfuse\Support;

use Closure;
use Psr\Container\ContainerInterface;
use Emberfuse\Support\Contracts\PipelineInterface;

class Pipeline implements PipelineInterface
{
    /**
     * Instance of service container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The data that is to be send through the pipeline.
     *
     * @var mixed
     */
    protected $package;

    /**
     * Array of pipes the data should be sent through.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method used to handle the data being passed.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Create new instance of pipeline.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set the data that should be passed through the pipeline.
     *
     * @param mixed $package
     *
     * @return \Emberfuse\Support\Contracts\PipelineInterface
     */
    public function send($package): PipelineInterface
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Set the pipes used to pass / process the data.
     *
     * @param array $pipes
     *
     * @return \Emberfuse\Support\Contracts\PipelineInterface
     */
    public function through(array $pipes): PipelineInterface
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Set the method used to handle the data.
     *
     * @param string $method
     *
     * @return \Emberfuse\Support\Contracts\PipelineInterface
     */
    public function via(string $method): PipelineInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set what to do after the data has been passed through and processed.
     *
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function then(Closure $callback)
    {
        return call_user_func_array($callback, [$this->process()]);
    }

    /**
     * Perform the package sending process.
     *
     * @return mixed
     */
    protected function process()
    {
        foreach ($this->pipes as $pipe) {
            $this->package = call_user_func_array(
                [$this->container->make($pipe), $this->method],
                [$this->package]
            );
        }

        return $this->package;
    }
}
