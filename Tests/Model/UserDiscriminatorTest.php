<?php

namespace Rvntone\MultiUserBundle\Tests\Model;

use Rvntone\MultiUserBundle\Model\UserDiscriminator;
use Rvntone\MultiUserBundle\Tests\Stub\UserRegistrationForm;
use Rvntone\MultiUserBundle\Tests\Stub\UserProfileForm;
use Rvntone\MultiUserBundle\Tests\Stub\AnotherUserRegistrationForm;
use Rvntone\MultiUserBundle\Tests\Stub\AnotherUserProfileForm;
use Rvntone\MultiUserBundle\Tests\Stub\User;
use Rvntone\MultiUserBundle\Tests\Stub\AnotherUser;
use Symfony\Component\Form\FormFactoryInterface;

class UserDiscriminatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')->disableOriginalConstructor()->getMock();
        
        $this->event = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')->disableOriginalConstructor()->getMock();       
        $this->token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')->disableOriginalConstructor()->getMock();      
        $this->user = new User();  
        $this->userInvalid = $this->getMockBuilder('InvalidUser')->disableOriginalConstructor()->getMock();  
        $this->userFactory = $this->getMockBuilder('Rvntone\MultiUserBundle\Model\UserFactoryInterface')->disableOriginalConstructor()->getMock();
                
        $userParameters = array(
            'entity' => array(
                'class' => 'Rvntone\MultiUserBundle\Tests\Stub\User',
                'factory' => 'Rvntone\MultiUserBundle\Model\UserFactory'
            ),
            'registration' => array(
                'form' => array(
                    'type' => 'Rvntone\MultiUserBundle\Tests\Stub\UserRegistrationForm',
                    'name' => 'fos_user_registration_form',
                    'validation_groups' => array('Registration', 'Default')
                ),
                'template' => 'AcmeUserBundle:Registration:user_one.form.html.twig'
            ),
            'profile' => array(
                'form' => array(
                    'type' => 'Rvntone\MultiUserBundle\Tests\Stub\UserProfileForm',
                    'name' => 'fos_user_profile_form',
                    'validation_groups' => array('Profile', 'Default')
                ),
                'template' => 'AcmeUserBundle:Profile:user_two.form.html.twig'
            )
        );

        $anotherUserParameters = array(
            'entity' => array(
                'class' => 'Rvntone\MultiUserBundle\Tests\Stub\AnotherUser',
                'factory' => 'Rvntone\MultiUserBundle\Tests\Stub\CustomUserFactory'
            ),
            'registration' => array(
                'form' => array(
                    'type' => 'Rvntone\MultiUserBundle\Tests\Stub\AnotherUserRegistrationForm',
                    'name' => 'fos_user_my_registration_form',
                    'validation_groups' => array('MyRegistration', 'Default')
                ),
                'template' => 'AcmeUserBundle:Registration:user_two.form.html.twig'
            ),
            'profile' => array(
                'form' => array(
                    'type' => 'Rvntone\MultiUserBundle\Tests\Stub\AnotherUserProfileForm',
                    'name' => 'fos_user_profile_form',
                    'validation_groups' => array('Profile', 'Default')
                ),
                'template' => 'AcmeUserBundle:Profile:user_two.form.html.twig'
            )
        );
        
        $this->parameters = array('user_one' => $userParameters, 'user_two' => $anotherUserParameters);
        
        $this->discriminator = new UserDiscriminator($this->session, $this->parameters);
    }
        
    /**
     * @expectedException \LogicException
     */
    public function testBuildException()
    {        
        $userParameters = array(
            'entity' => array(
                'class' => 'FakeUser',
                'factory' => 'Rvntone\MultiUserBundle\Model\UserFactory'
            ),
            'registration' => array(
                'form' => 'Rvntone\MultiUserBundle\Tests\Stub\UserRegistrationForm',
                'options' => array(
                    'validation_groups' => array('Registration', 'Default')
                )
            ),
            'profile' => array(
                'form' => 'Rvntone\MultiUserBundle\Tests\Stub\UserProfileForm',
                'options' => array(
                    'validation_groups' => array('Profile', 'Default')
                )
            )
        );
        
        $parameters     = array('user' => $userParameters);
        $discriminator  = new UserDiscriminator($this->session, $parameters);
    }
    
    /**
     * 
     */
    public function testGetClasses() 
    {
        $this->assertEquals(array('Rvntone\MultiUserBundle\Tests\Stub\User', 'Rvntone\MultiUserBundle\Tests\Stub\AnotherUser'), $this->discriminator->getClasses());
    }
    
    /**
     * @expectedException \LogicException
     */
    public function testSetClassException() 
    {
        $this->discriminator->setClass('ArbitaryClass');
    }
    
    public function testGetClass() 
    {  
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser');
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser', $this->discriminator->getClass());
    }
    
    public function testSetClassPersist() 
    {        
        $this->session->expects($this->exactly(1))->method('set')->with(UserDiscriminator::SESSION_NAME, 'Rvntone\MultiUserBundle\Tests\Stub\User');
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User', true);
    }
    
    public function testGetClassDefault() 
    {
        $this->session->expects($this->exactly(1))->method('get')->with(UserDiscriminator::SESSION_NAME, null)->will($this->onConsecutiveCalls(null));        
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\User', $this->discriminator->getClass());
    }
    
    public function testGetClassStored() 
    {
        $this->session->expects($this->exactly(1))->method('get')->with(UserDiscriminator::SESSION_NAME, null)->will($this->onConsecutiveCalls('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser'));
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser', $this->discriminator->getClass());
    }
    
    public function testCreateUser()
    {        
        $expected = new AnotherUser();
        $this->session->expects($this->exactly(0))->method('get');   
        
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser');
        $result = $this->discriminator->createUser();
        $this->assertEquals($expected, $result);
    }
    
    public function testGetUserFactory()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser');
        $result = $this->discriminator->getUserFactory();
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\CustomUserFactory', $result);
    }
    
    public function testGetFormTypeRegistration()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getFormType('registration');
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\UserRegistrationForm', get_class($result));
    }
    
    public function testGetFormTypeProfile()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getFormType('profile');
        $this->assertEquals('Rvntone\MultiUserBundle\Tests\Stub\UserProfileForm', get_class($result));
    }
    
    public function testGetFormNameRegistration()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser');
        $result = $this->discriminator->getFormName('registration');
        $this->assertEquals('fos_user_my_registration_form', $result);
    }
    
    public function testGetFormNameProfile()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getFormName('profile');
        $this->assertEquals('fos_user_profile_form', $result);
    }
    
    public function testGetValidationGroupsRegistration()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getFormValidationGroups('registration');
        $this->assertEquals(array('Registration', 'Default'), $result);
    }
    
    public function testGetValidationGroupsRegistrationCustom()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\AnotherUser');
        $result = $this->discriminator->getFormValidationGroups('registration');
        $this->assertEquals(array('MyRegistration', 'Default'), $result);
    }
    
    public function testGetValidationGroupsProfile()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getFormValidationGroups('profile');
        $this->assertEquals(array('Profile', 'Default'), $result);
    }
    
    public function testGetRegistrationTemplate()
    {
        $this->discriminator->setClass('Rvntone\MultiUserBundle\Tests\Stub\User');
        $result = $this->discriminator->getTemplate('registration');
        $this->assertEquals('AcmeUserBundle:Registration:user_one.form.html.twig', $result);
    }
}
