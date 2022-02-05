<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;


class ActivitySubscriber implements EventSubscriberInterface {

    private $security;
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
           KernelEvents::CONTROLLER => [
               ['updateLastVisitController', 0],
           ],
        ];
    }

    public function updateLastVisitController()
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!empty($user)) {
            $now = new \DateTime();
            $user->setLastVisit($now);
            $this->em->flush();
        }
    }
}