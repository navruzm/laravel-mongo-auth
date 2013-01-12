<?php namespace MongoAuth;

use Illuminate\Auth as Auth;

class MongoUserProvider implements Auth\UserProviderInterface {
    
    protected $conn;

    protected $hasher;

    protected $collection;
        
    public function __construct(MongoDB $conn, HasherInterface $hasher, $collection)
    {
        $this->conn = $conn;
        $this->collection = $collection;
        $this->hasher = $hasher;
    }
    
	/**
     * Retrieve a user by their unique idenetifier.
     *
     * @param  mixed  $identifier
     * @return Illuminate\Auth\UserInterface|null
     */
    public function retrieveByID($identifier)
    {
        $user = $this->conn->{$this->table}->findOne(array('_id' => new \MongoID($identifier)));

        if ( ! is_null($user))
        {
            return new GenericUser((array) $user);
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        unset($credentials['password']);
        
        $user = $this->conn->{$this->table}->findOne(array('_id' => new \MongoID($identifier)));

        if ( ! is_null($user))
        {
            return new GenericUser((array) $user);
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

}