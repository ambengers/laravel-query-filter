<?php

namespace Ambengers\QueryFilter\Concerns;

use Illuminate\Support\Arr;

trait HasParameters
{
    /**
     * Request instance.
     *
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Array of parameters
     *
     * @var array|null
     */
    protected $parameters;

    /**
     * Set the parameters.
     *
     * @param  array|null $parameters
     * @return static
     */
    public function parameters(array $parameters = null)
    {
        $this->parameters = collect($parameters ?? $this->request->all());

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters ?? $this->request->all();
    }

    /**
     * Get all the query filters from the parameters.
     *
     * @return array
     */
    public function all()
    {
        return $this->getParameters();
    }

    /**
     * Check if a key is present in parameters.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $parameters = $this->getParameters();

        foreach ($keys as $value) {
            if (! Arr::has($parameters, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a key is filled with value in parameters.
     *
     * @param  string $key
     * @return bool
     */
    public function filled($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve a value from parameters.
     *
     * @param  string $key
     * @param  string|null $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return data_get(
            $this->getParameters(),
            $key,
            $default
        );
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->input($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }
}
