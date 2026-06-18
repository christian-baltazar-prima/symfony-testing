<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Service\User\UserPasswordService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordServiceTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    #[DataProvider('provide_user_password_correct')]
    public function user_password_correct(string $email, string $plainPwd): void
    {
        $user = new User();
        $user->setEmail($email);

        $hasher = $this->prophesize(UserPasswordHasherInterface::class);
        $hasher->hashPassword($user, Argument::any())->willReturn('hashed_password')->shouldBeCalled();

        $test = new UserPasswordService($hasher->reveal());

        $this->assertSame('hashed_password', $test($user, $plainPwd));
    }

    public static function provide_user_password_correct(): array
    {
        return [
            ['test@mail.com', 'a12345'],
            ['test@mail.com', '12345x'],
            ['test@mail.com', 'abc123#'],
        ];
    }

    #[Test]
    #[DataProvider('provide_user_password_error')]
    public function user_password_error(string $email, string $plainPwd): void
    {
        $user = new User();
        $user->setEmail($email);

        $hasher = $this->prophesize(UserPasswordHasherInterface::class);
        $hasher->hashPassword($user, Argument::any())->shouldNotBeCalled();

        $this->expectException(\LogicException::class);

        $test = new UserPasswordService($hasher->reveal());

        $test($user, $plainPwd);
    }

    public static function provide_user_password_error(): array
    {
        return [
            ['test@mail.com', 'ab123'],
            ['test@mail.com', '123456'],
            ['test@mail.com', 'test123'],
            ['test@mail.com', 'qwerty'],
        ];
    }
}
