<?php

namespace Ambengers\QueryFilter\Concerns;

trait InteractsWithRequest
{
    /**
     * Request instance.
     *
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Get all the query filters from the request.
     *
     * @return array
     */
    public function all()
    {
        return $this->request->all();
    }

    /**
     * Check if a key is present in request.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return $this->request->has($key);
    }

    /**
     * Check if a key is filled with value in request.
     *
     * @param  string  $key
     * @return bool
     */
    public function filled($key)
    {
        return $this->request->filled($key);
    }

    /**
     * Retrieve a value from request.
     *
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return $this->request->input($key, $default);
    }
}
