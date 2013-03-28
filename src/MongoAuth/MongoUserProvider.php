<?php namespace MongoAuth;

use Illuminate\Auth\GenericUser;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Hashing\HasherInterface;
use LMongo\Connection;

class MongoUserProvider implements UserProviderInterface {

    /**
     * The database connection instance.
     *
     * @var LMongo\Connection
     */
    protected $connection;

    /**
     * The collection containing the users
     *
     * @var string
     */
    protected $collection;

    /**
     * The hasher implementation.
     *
     * @var Illuminate\Hashing\HasherInterface
     */
    protected $hasher;

    /**
     * Create a new database user provider.
     *
     * @param  LMongo\Connection  $connection
     * @param  Illuminate\Hashing\HasherInterface  $hasher
     * @param  string  $collection
     * @return void
     */
    public function __construct(Connection $connection, HasherInterface $hasher, $collection)
    {
        $this->connection = $connection;
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
        $user = $this->connection->collection($this->collection)->find($identifier);

        if ( ! is_null($user))
        {
            $user['id'] = (string) $user['_id'];

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
        $query = $this->connection->collection($this->collection);

        foreach ($credentials as $key => $value)
        {
            if ( ! str_contains($key, 'password'))
            {
                $query->where($key, $value);
            }
        }

        $user = $query->first();

        if ( ! is_null($user))
        {
            $user['id'] = (string) $user['_id'];

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