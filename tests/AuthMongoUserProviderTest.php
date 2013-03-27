<?php

use Mockery as m;

class AuthMongoUserProviderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testRetrieveByIDReturnsUserWhenUserIsFound()
	{
		$conn = m::mock('LMongo\Connection');
		$conn->shouldReceive('collection')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('find')->once()->with(1)->andReturn(array('_id' => 1, 'name' => 'Dayle'));
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new MongoAuth\MongoUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByID(1);

		$this->assertInstanceOf('Illuminate\Auth\GenericUser', $user);
		$this->assertEquals(1, $user->getAuthIdentifier());
		$this->assertEquals('Dayle', $user->name);
	}


	public function testRetrieveByIDReturnsNullWhenUserIsNotFound()
	{
		$conn = m::mock('LMongo\Connection');
		$conn->shouldReceive('collection')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('find')->once()->with(1)->andReturn(null);
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new MongoAuth\MongoUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByID(1);

		$this->assertNull($user);
	}


	public function testRetrieveByCredentialsReturnsUserWhenUserIsFound()
	{
		$conn = m::mock('LMongo\Connection');
		$conn->shouldReceive('collection')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('where')->once()->with('username', 'dayle');
		$conn->shouldReceive('first')->once()->andReturn(array('_id' => 1, 'name' => 'taylor'));
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new MongoAuth\MongoUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByCredentials(array('username' => 'dayle', 'password' => 'foo'));

		$this->assertInstanceOf('Illuminate\Auth\GenericUser', $user);
		$this->assertEquals(1, $user->getAuthIdentifier());
		$this->assertEquals('taylor', $user->name);
	}


	public function testRetrieveByCredentialsReturnsNullWhenUserIsFound()
	{
		$conn = m::mock('LMongo\Connection');
		$conn->shouldReceive('collection')->once()->with('foo')->andReturn($conn);
		$conn->shouldReceive('where')->once()->with('username', 'dayle');
		$conn->shouldReceive('first')->once()->andReturn(null);
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new MongoAuth\MongoUserProvider($conn, $hasher, 'foo');
		$user = $provider->retrieveByCredentials(array('username' => 'dayle'));

		$this->assertNull($user);
	}


	public function testCredentialValidation()
	{
		$conn = m::mock('LMongo\Connection');
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$hasher->shouldReceive('check')->once()->with('plain', 'hash')->andReturn(true);
		$provider = new MongoAuth\MongoUserProvider($conn, $hasher, 'foo');
		$user = m::mock('Illuminate\Auth\UserInterface');
		$user->shouldReceive('getAuthPassword')->once()->andReturn('hash');
		$result = $provider->validateCredentials($user, array('password' => 'plain'));

		$this->assertTrue($result);
	}

}