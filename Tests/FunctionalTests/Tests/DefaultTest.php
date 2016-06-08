<?php

namespace Earls\EarlsOxPeckerDataBundle\Tests\FunctionalTests\Tests\DefaultTest;

use Doctrine\Common\Collections\Collection;
use Earls\OxPeckerDataBundle\Tests\FunctionalTests\Model\FixtureAwareTestCase;

/**
 * Default test
 */
class DefaultTest extends FixtureAwareTestCase
{
  public function setup() 
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();

    $this->initTestDatabase();
  }
  
  // Used only to build kernel
  public function testBuild()
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    
    $this->assertEquals('test', 'test');
    
  }
}