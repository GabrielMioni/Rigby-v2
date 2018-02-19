<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User2;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserFixture extends Fixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');

        $user = new User2();
        $user->setUsername('Jimbo');
        $user->setEmail('jim@bobo.net');

        $encodedPassword = $passwordEncoder->encodePassword($user, 'blah123');
        $user->setPassword($encodedPassword);

        $manager->persist($user);
        $manager->flush();
    }
}