<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\UserController;
use App\Entity\User;
use App\Service\User\CreateUserAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function new_user_created(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->handleRequest(Argument::any())->willReturn($form)->shouldBeCalled();
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);
        $form->getData()->willReturn(new User());

        $test = $this->createPartialMock(UserController::class, ['createForm', 'redirectToRoute']);
        $test->expects($this->once())->method('createForm')->willReturn($form->reveal());
        $test->expects($this->once())->method('redirectToRoute')->willReturn(new RedirectResponse('app_user_index'));

        $userAction = $this->prophesize(CreateUserAction::class);
        $userAction->__invoke(Argument::any())->shouldBeCalled();

        $result = $test->new(new Request(), $userAction->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}