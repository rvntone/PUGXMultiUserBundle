<?php

namespace Rvntone\MultiUserBundle\Tests\Doctrine;

use Rvntone\MultiUserBundle\Doctrine\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
  private $passwordUpdater;
  private $canonicalFieldsUpdater;
  private $om;
  private $userDiscriminator;
  private $class;
  private $repo;
  private $metaData;
  private $userManager;

  public function setUp()
    {
        $this->passwordUpdater = $this->getMockBuilder('FOS\UserBundle\Util\PasswordUpdaterInterface')
                                      ->disableOriginalConstructor()->getMock();
        $this->canonicalFieldsUpdater = $this->getMockBuilder('FOS\UserBundle\Util\CanonicalFieldsUpdater')
                                             ->disableOriginalConstructor()->getMock();
        $this->om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()->getMock();
        $this->userDiscriminator = $this->getMockBuilder('Rvntone\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();
        $this->class = 'Rvntone\MultiUserBundle\Tests\Stub\User';
        
        $this->repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
                ->disableOriginalConstructor()->getMock();
        
        //parent
        $this->metaData = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
                ->disableOriginalConstructor()->getMock();
        
        $this->om->expects($this->exactly(1))->method('getClassMetadata')
                ->with($this->class)
                ->will($this->returnValue($this->metaData));
        
        $this->metaData->expects($this->exactly(1))->method('getName')
                ->will($this->returnValue($this->class));
        //end parent

        $this->userManager = new UserManager($this->passwordUpdater, $this->canonicalFieldsUpdater, $this->om, $this->class, $this->userDiscriminator);
    }
        
    public function testGetClass()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('getClass')->will($this->returnValue('Acme\UserBundle\MyUser'));        
        $result = $this->userManager->getClass();
        $this->assertEquals('Acme\UserBundle\MyUser', $result);
    }
    
    public function testCreateUser()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('createUser')->will($this->onConsecutiveCalls(null));        
        $this->userManager->createUser();
    }
    
    public function testFindUserBy()
    {
        $this->userDiscriminator
            ->expects($this->exactly(1))
            ->method('getClasses')
            ->will($this->onConsecutiveCalls(array('Rvntone\MultiUserBundle\Tests\Stub\User')));

        $this->om->expects($this->exactly(1))
            ->method('getRepository')
            ->will($this->returnValue($this->repo));

        $this->repo->expects($this->exactly(1))
            ->method('findOneBy')
            ->with(array('criteria' => 'dummy'))->will($this->onConsecutiveCalls(true));

        $this->userDiscriminator
            ->expects($this->exactly(1))
            ->method('setClass')
            ->will($this->onConsecutiveCalls(array('Rvntone\MultiUserBundle\Tests\Stub\User')));

        $this->userManager->findUserBy(array('criteria' => 'dummy'));
    }
    
    public function testFindUsers()
    {                
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(array('Rvntone\MultiUserBundle\Tests\Stub\User')));
        $this->om->expects($this->exactly(1))->method('getRepository')->with('Rvntone\MultiUserBundle\Tests\Stub\User')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findAll')->will($this->onConsecutiveCalls(array()));
        $this->userManager->findUsers();
    }
    
    public function testFindUserByUserNotFound()
    {                
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(array('Rvntone\MultiUserBundle\Tests\Stub\User')));
        $this->om->expects($this->exactly(1))->method('getRepository')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findOneBy')->with(array('criteria' => 'dummy'))->will($this->onConsecutiveCalls(null));        
        $this->userDiscriminator->expects($this->exactly(0))->method('setClass');                
        $user = $this->userManager->findUserBy(array('criteria' => 'dummy'));        
        $this->assertEquals(null, $user);
    }
}
