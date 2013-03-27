<?php namespace MongoAuth\Reminders;

use MongoDate;
use LMongo\Connection;
use Illuminate\Auth\Reminders\ReminderRepositoryInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class MongoReminderRepository implements ReminderRepositoryInterface {

	/**
	 * The database connection instance.
	 *
	 * @var LMongo\Connection
	 */
	protected $connection;

	/**
	 * The reminder database collection.
	 *
	 * @var string
	 */
	protected $collection;

	/**
	 * The hashing key.
	 *
	 * @var string
	 */
	protected $hashKey;

	/**
	 * Create a new reminder repository instance.
	 *
	 * @var LMongo\Connection  $connection
	 * @return void
	 */
	public function __construct(Connection $connection, $collection, $hashKey)
	{
		$this->collection = $collection;
		$this->hashKey = $hashKey;
		$this->connection = $connection;
	}

	/**
	 * Create a new reminder record and token.
	 *
	 * @param  Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @return string
	 */
	public function create(RemindableInterface $user)
	{
		$email = $user->getReminderEmail();

		// We will create a new, random token for the user so that we can e-mail them
		// a safe link to the password reset form. Then we will insert a record in
		// the database so that we can verify the token within the actual reset.
		$token = $this->createNewToken($user);

		$this->getCollection()->insert($this->getPayload($email, $token));

		return $token;
	}

	/**
	 * Build the record payload for the collection.
	 *
	 * @param  string  $email
	 * @param  string  $token
	 * @return array
	 */
	protected function getPayload($email, $token)
	{
		return array('email' => $email, 'token' => $token, 'created_at' => new MongoDate);
	}

	/**
	 * Determine if a reminder record exists and is valid.
	 *
	 * @param  Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @param  string  $token
	 * @return bool
	 */
	public function exists(RemindableInterface $user, $token)
	{
		$email = $user->getReminderEmail();

		$reminder = $this->getCollection()->where('email', $email)->where('token', $token)->first();

		return $reminder and ! $this->reminderExpired((object) $reminder);
	}

	/**
	 * Determine if the reminder has expired.
	 *
	 * @param  StdClass  $reminder
	 * @return bool
	 */
	protected function reminderExpired($reminder)
	{
		$createdPlusHour = $reminder->created_at->sec + 216000;

		return $createdPlusHour < $this->getCurrentTime();
	}

	/**
	 * Get the current UNIX timestamp.
	 *
	 * @return int
	 */
	protected function getCurrentTime()
	{
		return time();
	}

	/**
	 * Delete a reminder record by token.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function delete($token)
	{
		$this->getCollection()->where('token', $token)->delete();
	}

	/**
	 * Create a new token for the user.
	 *
	 * @param  Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @return string
	 */
	public function createNewToken(RemindableInterface $user)
	{
		$email = $user->getReminderEmail();

		$value = str_shuffle(sha1($email.spl_object_hash($this).microtime(true)));

		return hash_hmac('sha512', $value, $this->hashKey);
	}

	/**
	 * Begin a new database query against the collection.
	 *
	 * @return LMongo\Query\Builder
	 */
	protected function getCollection()
	{
		return $this->connection->collection($this->collection);
	}

	/**
	 * Get the database connection instance.
	 *
	 * @return LMongo\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

}