<?php namespace MongoAuth;

use Illuminate\Auth as Auth;
use Illuminate\Hashing\HasherInterface;

class MongoUserProvider implements Auth\UserProviderInterface {
    
    protected $conn;

    protected $hasher;

    protected $collection;
        
    public function __construct(\LMongo\Database $conn, HasherInterface $hasher, $collection)
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
        $user = $this->conn->{$this->collection}->findOne(array('_id' => new \MongoID($identifier)));

        if ( ! is_null($user))
        {
            $user['id'] = (string) $user['_id'];
            return new Auth\GenericUser((array) $user);
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
        $query = array();

        foreach ($credentials as $key => $value)
        {
            if ( ! str_contains($key, 'password'))
            {
                $query[$key] = $value;
            }
        }
        
        $user = $this->conn->{$this->collection}->findOne($query);

        if ( ! is_null($user))
        {
            $user['id'] = (string) $user['_id'];
            return new Auth\GenericUser((array) $user);
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Auth\UserInterface $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

}