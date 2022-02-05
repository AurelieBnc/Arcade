<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use \DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // Instanciation du faker qui nous servira à créer des fausses données aléatoires
        $faker = Faker\Factory::create('fr_FR');

        // Création du compte admin du site
        $admin = new User();

        // Hydratation du compte admin
        $admin
            ->setEmail('a@a.a')
            ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
            ->setPseudonym('Tatsu')
            ->setLastVisit(new DateTime())
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword(
                $this->encoder->encodePassword($admin, 'aaaaaaaaA7/')
            )
        ;

        $manager->persist($admin);
        $manager->flush();

    }
}
