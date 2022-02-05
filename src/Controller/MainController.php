<?php



namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\CategoryRepository;
use App\Repository\ForumRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use App\Form\CreateCommentFormType;

use App\Form\EditDescriptionType;
use App\Form\EditPasswordType;
use App\Form\EditPhotoType;
use App\Form\EditEmailType;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MainController extends AbstractController
{
    /**
     * Page d'accueil
     * @Route("/", name="home")
     */
    public function index(CategoryRepository $categories, UserRepository $userRepo): Response
    {
        // Requête du flux RSS des actualités dans un try & catch si jamais le lien n'est plus valide
        try {
            $rss = simplexml_load_file('https://www.actugaming.net/feed/');

        } catch(\Exception $e) {
           $rss = ['channel' => [
                'item' => [
                    ''
                ]
            ]];
        }
        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);
        $lastComments = $commentRepo->findBy([], ['publicationDate' => 'DESC'], 5);
        $userRepo->findConnectedAdmins();

        // Récupération des 2 derniers Event
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->findBy([], ['publicationDate' => 'DESC'], 2);


        return $this->render('main/index.html.twig', [
            'categories' => $categories->findAll(),
            'rss' => $rss,
            'events' => $events,
            'comments' => $lastComments
        ]);
    }


    /**
     * @Route("/mon-profil/", name="main_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(): Response
    {
        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);

        $comments = $commentRepo->findBy([], ['publicationDate' => 'DESC']);

        $test = $this->getUser();
        dump($test);
        return $this->render('main/profil.html.twig', [
            'comments' => $test->getComments(),
        ]);

    }


    /**
     * @Route("/edit-profil/", name="edit_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function editProfil(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(editPhotoType::class);

        $form->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($form->isSubmitted() && $form->isValid()){

            $avatar = $form->get('avatar')->getData();

            // Récupération de l'emplacement du dossier des photos de profils
            $profilAvatarDirectory = $this->getParameter('users_uploaded_avatar_directory');

            // Récupération de l'utilisateur connecté
            $connectedUser = $this->getUser();

            // Si l'utilistateur à déjà un avatar, l'ancienne est supprimé
            if($connectedUser->getAvatar() != null){
                unlink( $profilAvatarDirectory . $connectedUser->getAvatar() );
            }

            //dump($avatar);

            do{

                $newFileName = md5( $connectedUser->getId() . random_bytes(100) ) . '.' . $avatar->guessExtension();

            //dump($newFileName);

            } while( file_exists( $profilAvatarDirectory . $newFileName ) );

            // Mise à jour de l'avatar de l'utilisateur dans la bdd
            $connectedUser->setAvatar($newFileName);

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            $avatar->move(
                $profilAvatarDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Avatar créé avec succès !');

            return $this->redirectToRoute('main_profil');
        }


        //Modification du mot de passe
        $formPass = $this->createForm(editPasswordType::class);

        $formPass->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formPass->isSubmitted() && $formPass->isValid()){

            $connectedUser = $this->getUser();

            // Si les deux champs sont correct
            if($formPass->get('pass')->getData() == $formPass->get('confirm-pass')->getData()){

                $hashOfNewPassword = $encoder->encodePassword($connectedUser, $formPass->get('pass')->getData());

                $connectedUser->setPassword($hashOfNewPassword);

                $em = $this->getDoctrine()->getManager();

                $em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succès !');

                return $this->redirectToRoute('main_profil');

            } else {

                $this->addFlash('error', 'Les mots de passe ne sont pas identiques, veuillez réessayer.');
            }
        }


        //Modification de l'adresse mail
        $formEmail = $this->createForm(editEmailType::class);

        $formEmail->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formEmail->isSubmitted() && $formEmail->isValid()){

            // Récupérer toute les adresses mail de la bdd
            $allEmail = $this->getDoctrine()->getRepository(User::class);

            $entityManager = $this->getDoctrine()->getManager();

            $query = $entityManager->createQuery(
                'SELECT u.email
                FROM App\Entity\User u
                WHERE u.email = :email'
            )->setParameter('email', $formEmail->get('mail')->getData());

            dump($formEmail->get('mail')->getData());

            if( !empty($query->getResult()) ) {

                $this->addFlash('error', 'L\'adresse mail est déjà utilisée , veuillez réessayer.');

            } else {

                if($formEmail->get('mail')->getData() == $formEmail->get('confirm-mail')->getData()){

                    $em = $this->getDoctrine()->getManager();

                    $connectedUser = $this->getUser();

                    $connectedUser->setEmail($formEmail->get('mail')->getData());

                    //$connectedUser->setIsVerified()

                    $em->flush();

                    $this->addFlash('success', 'Mail modifié avec succès !');

                    return $this->redirectToRoute('main_profil');


                } else {

                    $this->addFlash('error', 'Les emails ne sont pas identiques, veuillez réessayer.');
                }


            }


        }

        //Modification de la description
        $formDesc = $this->createForm(editDescriptionType::class);

        $formDesc->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formDesc->isSubmitted() && $formDesc->isValid()){

            dump($formDesc->get('description')->getData());

            if($formDesc->get('description')->getData()){

                $em = $this->getDoctrine()->getManager();

                $connectedUser = $this->getUser();

                $connectedUser->setDescription($formDesc->get('description')->getData());

                $em->flush();

                $this->addFlash('success', 'Description modifiée avec succès !');

                return $this->redirectToRoute('main_profil');


            }
        }


        return $this->render('main/editProfil.html.twig', [
            'form' => $form->CreateView(),
            'formPass' => $formPass->CreateView(),
            'formEmail' => $formEmail->CreateView(),
            'formDesc' => $formDesc->CreateView(),
        ]);
    }

    /**
     * Page permettant de supprimer un la description
     *
     * @Route("/description/suppression/", name="description_delete")
     * @Security("is_granted('ROLE_USER')")
     */
    public function descriptionDelete(Request $request): Response
    {

            // Suppression de la description
            $em = $this->getDoctrine()->getManager();

            $connectedUser = $this->getUser();

            $connectedUser->setDescription(null);

            $em->flush();

            $this->addFlash('success', 'La description a été supprimé avec succès !');

        return $this->redirectToRoute('main_profil');
    }


    /**
     * @Route("/creer-une-annonce", name="create_event")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function createEvent(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $event->setPublicationDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Annonce créée avec succès !');
            return $this->redirectToRoute('home');
        }


        return $this->render('main/newEvent.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annonces/", name="view_event")
     */
    public function viewEvent(EventRepository $eventRepo): Response
    {
        return $this->render('main/viewEvent.html.twig', [
           'events' => $eventRepo->findBy([], ['publicationDate' => 'DESC'])
        ]);
    }

    /**
     * @Route("modifier-annonce/{id}", name="edit_event")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function editEvent(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Annonce modifiée avec succès !');

            return $this->redirectToRoute('view_event');
        }


        return $this->render('main/editEvent.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("supprimer-l-annonce/{id}", name="delete_event", methods={"POST"})
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function deleteEvent(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Annonce supprimée !');
        }
        else {
            $this->addFlash('error', 'Token invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('view_event');
    }


        /**
     * Page moderation permettant de modifier un commentaire existant
     *
     * @Route("/profil/modifier-commentaire/{id}/", name="comment_edit_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function commentEdit(Comment $comment, Request $request): Response
    {

        $user = $this->getUser();

        if($user == $comment->getAuthor()){

            // Création du formulaire de modification
            $form = $this->createForm(CreateCommentFormType::class, $comment);

            // Liaison des données POST avec le formulaire
            $form->handleRequest($request);

                // Si le formulaire est envoyé et n'a pas d'erreur
                if ($form->isSubmitted() && $form->isValid()) {

                    // Sauvegarde des changements dans la BDD
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();

                    // Message flash de succès
                    $this->addFlash('success', 'Sujet modifié avec succès !');

                    // Redirection vers la page de l'article modifié
                    return $this->redirectToRoute('main_profil', [
                        'slug' => $comment->getForum()->getSlug(),
                    ]);

                }

        } else {

            throw new AccessDeniedHttpException();
        }

        // Appel de la vue en envoyant le formulaire à afficher
        return $this->render('forum/commentEdit.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * Page moderation permettant de supprimer un commentaire
     *
     * @Route("/forum/suppression-commentaire/{id}/", name="comment_delete_profil")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        // Récupération du token csrf dans l'url
        $tokenCSRF = $request->query->get('csrf_token');

        // Vérification que le token est valide
        if (!$this->isCsrfTokenValid('comment_delete' . $comment->getId(), $tokenCSRF)) {
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        } else {
            dump('test');
            // Suppression du commentaire
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }
        return $this->redirectToRoute('main_profil', [
            'slug' => $comment->getForum()->getSlug(),
        ]);
    }


    /**
     * @Route("/classement/", name="rank")
     */
    public function rank(Request $request): Response
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findBy([], ['message' => 'DESC'], 100);
        return $this->render('main/rank.html.twig', [
            'users' => $users,
        ]);
    }
}
