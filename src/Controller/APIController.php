<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\APIService;

/**
 * Class APIController
 * Contrôleur qui renverra des données aux requête Ajax
 * @Route("/api", name="api_")
 */
class APIController extends AbstractController
{
    /**
     * @Route("/liste-utilisateurs-connectes", name="users_list")
     * Methode permettant de récupérer tout les utilisateurs connectés
     */
    public function usersList(UserRepository $userRepo, APIService $APIServices)
    {

        // Récupération des utilisateurs
        $response = $APIServices->getApi($userRepo->findConnectedUsers());

        return $response;
    }

    /**
     * @Route("/liste-admins-connectes", name="admins_list")
     * Méthode permettant de récupérer tout les admins connectés
     */
    public function adminsList(UserRepository $userRepo, APIService $APIService){

        $response = $APIService->getApi($userRepo->findConnectedAdmins());

        return $response;
    }

    /**
     * @Route("/nombre-forums", name="forum_number")
     * Méthode permettant de récupérer le nombre de forum
     */
    public function countForums(ForumRepository $forumRepo, APIService $APIService){

        $response = $APIService->getApi($forumRepo->countForums());

        return $response;
    }

    /**
     * @Route("/nombre-messages", name="message_number")
     * Méthode permettant de récupérer le nombre de messages
     */
    public function countMessages(CommentRepository $commentRepo, APIService $APIService){

        $response = $APIService->getApi($commentRepo->countMessages());

        return $response;
    }

    /**
     * @Route("/nombre-utlisateurs", name="user_number")
     * Méthode permettant de récupérer le nombre d'utilisateurs
     */
    public function countUsers(UserRepository $userRepo, APIService $APIService){

        $response = $APIService->getApi($userRepo->countUsers());

        return $response;
    }

    /**
     * @Route("/derniers-messages", name="last_forums")
     * Méthode permettant de récupérer les 5 derniers messages
     */
    public function getLastForum(ForumRepository $forumRepo, APIService $APIService)
    {
        $response = $APIService->getApi($forumRepo->findBy([], ['publicationDate' => 'DESC'], 5));

        return $response;
    }
}
