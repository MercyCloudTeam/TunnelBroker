<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * A test class for the `UserController` class,
 * specifically for the `apiCreateUser` method.
 *
 * @see \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * Test the successful creation of a User.
     * @return void
     */
    public function testApiCreateUserSuccessful()
    {
        $userController = new UserController();
        $userAttrs = ['name' => 'John Doe', 'email' => 'john.doe@test.com'];

        $request = new \Illuminate\Http\Request();
        $request->replace($userAttrs);

        $userController->apiCreateUser($request);

        $this->assertCount(1, User::all());

        $user = User::first();
        $this->assertEquals($userAttrs['name'], $user->name);
        $this->assertEquals($userAttrs['email'], $user->email);
    }

    /**
     * Test validation rules for creating a User.
     * @return void
     */
    public function testApiCreateUserValidationFailure()
    {
        $this->expectException(ValidationException::class);

        $userController = new UserController();

        $emptyRequest = new \Illuminate\Http\Request();
        $emptyRequest->replace([]);

        $userController->apiCreateUser($emptyRequest);
    }

    /**
     * Test password hashing when creating a User.
     * @return void
     */
    public function testApiCreateUserPasswordHash()
    {
        $userController = new UserController();
        $userAttrs = ['name' => 'John Doe', 'email' => 'john.doe@test.com', 'password' => 'plaintextpass'];

        $request = new \Illuminate\Http\Request();
        $request->replace($userAttrs);

        $userController->apiCreateUser($request);

        $user = User::first();
        $this->assertTrue(Hash::check($userAttrs['password'], $user->password));
    }
}
