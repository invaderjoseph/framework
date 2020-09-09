<?php

namespace Emberfuse\Support\Contracts;

use Closure;

interface PipelineInterface
{
    /**
     * Set the data that should be passed through the pipeline.
     *
     * @param mixed $package
     *
     * @return \Emberfuse\Support\Contracts\PipelineInterface
     */
    public function send($package): PipelineInterface;

    /**
     * Set the pipes used to pass / process the data.
     *
     * @param array $pipes
     *
     * @return \Emberfuse\Support\Contracts\PipelineInterface
     */
    public function through(array $pipes): PipelineInterface;

    /**
     * Set what to do after the data has been passed through and processed.
     *
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function then(Closure $callback);
}
