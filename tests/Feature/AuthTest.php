<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_successful_register()
    {
        $response = $this->post('/api/register', [
            'email' => 'ab@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'name' => 'test',
            'phone' => '09135553233',
        ]);

        $response->assertStatus(200);
    }

    public function test_email_repeat_error()
    {
        $repeatingEmail = User::first()->email;
        $response = $this->json('POST', '/api/register', [
            'email' => $repeatingEmail,
            'password' => '123456',
            'password_confirmation' => '123456',
            'name' => 'test',
            'phone' => '09135553233',
        ]);

        $response->assertStatus(422);
    }

    public function test_successful_login()
    {
        User::create([
            'email' => 'testing-login@gmail.com' ,
            'password' => '123456',
            'phone' => '09913845777'
        ]);
        $response = $this->json('POST', '/api/login', [
            'email' => 'testing-login@gmail.com',
            'password' => '123456'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                [
                    'token',
                    'tokenType',
                    'user'
                ]
            ],
            'statusCode'
        ]);
    }

    public function test_empty_function_on_null()
    {
        $this->assertEmpty(null);
        return true;
    }

    /**
     * @depends test_empty_function_on_null
     */
    public function test_false_is_equal_to_zero(bool $a)
    {
        $this->assertEquals(1,(int)$a);
    }

    public function CasesForRoot()
    {
        return[
            [8,64,2],
            'forth root of 64' => [4,256,4],
            [3,9,2]
        ];
    }

    /**
     * @dataProvider CasesForRoot
     */
    public function test_for_the_root_of_numbers($res , $num , $rootNum)
    {
        if ($rootNum == 2)
            $this->assertEquals($res,sqrt($num));
        elseif ($rootNum == 4)
            $this->assertEquals($res,sqrt(sqrt($num)));
    }

    public function test_expect_exception()
    {
        $this->expectException(NotAcceptableHttpException::class);
         throw new NotAcceptableHttpException();
    }
}


