<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Méthode pour récupérer la liste des utlisateurs connectés
     */
    public function findConnectedUsers(): array
    {
        $role = 'ROLE_USER';
        $date = new \DateTime('-15 mins');
        $qb = $this->createQueryBuilder('u')
            ->select( 'u.pseudonym, u.id')
            ->where('u.lastVisit > :date')
            ->andWhere('u.roles LIKE :user' )
            ->setParameter('date', $date)
            ->setParameter('user', '%"' . $role . '"%');

        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * Méthode pour compter le nombre d'utilisateur dans le site
     */
    public function countUsers(){
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)');

        $query = $qb->getQuery();
        return $query->execute();
    }


    /**
     * Méthode pour récupérer la liste des admins connectés
     */
    public function findConnectedAdmins(){
        $role = 'ROLE_USER';
        $date = new \DateTime('-15 mins');
        $qb = $this->createQueryBuilder('u')
            ->select( 'u.pseudonym, u.id')
            ->where('u.lastVisit > :date')
            ->andWhere('u.roles NOT LIKE :role')
            ->setParameter('date', $date)
            ->setParameter('role', '%"' . $role . '"%');

        $query = $qb->getQuery();
        return $query->execute();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
