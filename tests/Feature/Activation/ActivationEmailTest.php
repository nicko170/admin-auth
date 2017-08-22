<?php

namespace Brackets\AdminAuth\Tests\Auth;

use Brackets\AdminAuth\Notifications\ActivationNotification;
use Brackets\AdminAuth\Tests\TestBracketsCase;
use Brackets\AdminAuth\Tests\TestBracketsUserModel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;

class ActivationEmailTest extends TestBracketsCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app['config']->set('admin-auth.activations.enabled', true);
        $this->disableExceptionHandling();
    }

    protected function createTestUser($activated = true, $forbidden = false)
    {
        $user = TestBracketsUserModel::create([
            'email' => 'john@example.com',
            'password' => bcrypt('testpass123'),
            'activated' => $activated,
            'forbidden' => $forbidden,
        ]);

        $this->assertDatabaseHas('test_brackets_user_models', [
            'email' => 'john@example.com',
            'activated' => $activated,
            'forbidden' => $forbidden,
        ]);

        return $user;
    }

    /** @test */
    public function can_see_activation_form()
    {
        $response = $this->get(route('brackets/admin-auth:admin/activation/showLinkRequestForm'));
        $response->assertStatus(200);
    }

    /** @test */
    public function send_activation_email_after_user_created()
    {
        Notification::fake();

        $user = $this->createTestUser(false);

        Notification::assertSentTo(
            $user,
            ActivationNotification::class
        );
    }

    /** @test */
    public function send_activation_email_after_user_not_activated_and_form_filled()
    {
        Notification::fake();

        $user = $this->createTestUser(false);

        $response = $this->post(route('brackets/admin-auth:admin/activation/sendActivationEmail'), ['email' => 'john@example.com']);
        $response->assertStatus(302);

        Notification::assertSentTo(
            $user,
            ActivationNotification::class
        );
    }

    /** @test */
    public function do_not_send_activation_email_if_email_not_found()
    {
        Notification::fake();

        $response = $this->post(route('brackets/admin-auth:admin/activation/sendActivationEmail'), ['email' => 'user@example.com']);
        $response->assertStatus(302);

        $user = new TestBracketsUserModel([
            'email' => 'user@example.com',
            'password' => bcrypt('testpass123'),
            'activated' => false,
            'forbidden' => false,
        ]);

        Notification::assertNotSentTo(
            $user,
            ActivationNotification::class
        );
    }

    /** @test */
    public function do_not_send_activation_email_if_user_already_activated()
    {
        Notification::fake();

        $user = $this->createTestUser(true);

        $response = $this->post(route('brackets/admin-auth:admin/activation/sendActivationEmail'), ['email' => 'john@example.com']);
        $response->assertStatus(302);

        Notification::assertNotSentTo(
            $user,
            ActivationNotification::class
        );
    }
}