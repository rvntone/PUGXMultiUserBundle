<?php

namespace Rvntone\MultiUserBundle\Tests\Controller;

use Rvntone\MultiUserBundle\Listener\AuthenticationListener;
use Rvntone\MultiUserBundle\Tests\Stub\Proxy\__CG__\Rvntone\MultiUserBundle\Tests\Stub\User as UserProxy;
use Rvntone\MultiUserBundle\Tests\Stub\User;

class AuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->userDiscriminator = $this->getMockBuilder('Rvntone\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();
        $this->interactiveLoginEvent = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
                ->disableOriginalConstructor()->getMock();
        $this->implicitLoginEvent = $this->getMockBuilder('FOS\UserBundle\Event\UserEvent')
                ->disableOriginalConstructor()->getMock();
		$this->switchUserEvent = $this->getMockBuilder('Symfony\Component\Security\Http\Event\SwitchUserEvent')
				->disableOriginalConstructor()->getMock();
        $this->token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')
                ->disableOriginalConstructor()->getMock();
        $this->user = new User();

        $this->listener = new AuthenticationListener($this->userDiscriminator);
    }

    public function testOnSecurityInteractiveLogin()
    {
        $this->interactiveLoginEvent->expects($this->once())->method('getAuthenticationToken')->will($this->returnValue($this->token));
        $this->token->expects($this->once())->method('getUser')->will($this->returnValue($this->user));
        $this->userDiscriminator->expects($this->exactly(1))->method('setClass')->with('Rvntone\MultiUserBundle\Tests\Stub\User', true);

        $this->listener->onSecurityInteractiveLogin($this->interactiveLoginEvent);
    }

    public function testOnSecurityImplicitLogin()
    {
        $this->implicitLoginEvent->expects($this->once())->method('getUser')->will($this->returnValue($this->user));
        $this->userDiscriminator->expects($this->exactly(1))->method('setClass')->with('Rvntone\MultiUserBundle\Tests\Stub\User', true);

        $this->listener->onSecurityImplicitLogin($this->implicitLoginEvent);
    }

	public function testOnSecuritySwitchUser()
	{
		$this->switchUserEvent->expects($this->once())->method('getTargetUser')->will($this->returnValue($this->user));
		$this->userDiscriminator->expects($this->exactly(1))->method('setClass')->with('Rvntone\MultiUserBundle\Tests\Stub\User', true);

		$this->listener->onSecuritySwitchUser($this->switchUserEvent);
	}

    public function testDiscriminateNormalizedProxyClasses()
    {
        $this->switchUserEvent->expects($this->once())
            ->method('getTargetUser')
            ->will($this->returnValue(new UserProxy()));

        $this->userDiscriminator->expects($this->once())
            ->method('setClass')
            ->with('Rvntone\MultiUserBundle\Tests\Stub\User', true);

        $this->listener->onSecuritySwitchUser($this->switchUserEvent);
    }
}
