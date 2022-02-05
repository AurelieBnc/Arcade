<?php

namespace App\Controller;


use App\Controller\MainController;
use App\Form\CategoryEditType;
use App\Form\EditCategoryType;
use App\Form\EditSubCategoryType;
use App\Form\ForumFormType;
use App\Form\MoveForumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\ForumRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Form\CreateCommentFormType;
use App\Form\SubCategoryFormType;
use App\Form\CategoryFormType;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Forum;
use App\Entity\Comment;
use App\Entity\User;

use \DateTime;

/**
 * Contrôleurs de la page qui liste les categories du site
 *
 *
 */
class ForumController extends AbstractController
{

    /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-categorie/", name="new_category")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function newCategory(Request $request): Response
    {

        $newCategory = new Category();
        $form = $this->createForm(CategoryFormType::class, $newCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            $imageDirectory = $this->getParameter('app_category_image_directory');

            $connectedUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();


            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();


            } while (file_exists($imageDirectory . $newFileName));

            dump($newFileName);

            // Mise à jour de l'image de la catégorie dans la BDD
            $newCategory->setImage($newFileName);
            $em->persist($newCategory);
            $em->flush();

            $image->move(
                $imageDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('home');
        }

        return $this->render('forum/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("categorie/{slug}/", name="category")
     */
    public function category(SubCategoryRepository $subCategory ,Category $category, Request $request): Response
    {

        return $this->render('forum/category/category.html.twig',[
            'category' => $category,
        ]);

    }


    /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-souscategorie/{slug}", name="new_subcategory")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function newSubCategory(Request $request, Category $category): Response
    {


        $newSubCategory = new SubCategory();
        $form = $this->createForm(SubCategoryFormType::class, $newSubCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            $imageDirectory = $this->getParameter('app_subcategory_image_directory');
            $connectedUser = $this->getUser();

            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();

                dump($newFileName);

            } while (file_exists($imageDirectory . $newFileName));

            // Mise à jour du nom de la photo de la sous catégorie
            $newSubCategory->setImage($newFileName);
            $newSubCategory->setCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newSubCategory);
            $em->flush();

            $image->move(
                $imageDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Sous-Catégorie créée avec succès !');
            return $this->redirectToRoute('category',[
                'slug'=> $category->getSlug()
            ]);
        }

        return $this->render('forum/category/newSubCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forumlist/{slug}", name="forumlist")
     */
    public function forumList(Request $request, SubCategory $subCategory, PaginatorInterface $paginator): Response
    {

        //Paginator pour les forums
        $requestedPage = $request->query->getInt('page', 1);


        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $forumList = $subCategory->getForums();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT c FROM App\Entity\Forum c ORDER BY c.publicationDate DESC');

        $pagination = $paginator->paginate(
            $forumList,
            $requestedPage,     // Numéro de la page actuelle
            5              // Nombre de forums par page
        );

        return $this->render('forum/forumList.html.twig',[
            'forums' => $pagination,
            'subCategory' => $subCategory,
        ]);

    }


    /**
     * Contrôleur de la page permettant de créer un nouveau forum
     *
     * @Route("/nouveau-forum/{slug}", name="new_forum")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function newForum(Request $request, SubCategory $subCategory): Response
    {
        $newForum = new Forum();
        $form = $this->createForm(ForumFormType::class, $newForum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $connectedUser = $this->getUser();
            $newForum
            ->setSubCategory($subCategory)
            ->setAuthor($connectedUser)
            ->setPublicationDate(new DateTime())
            ->setView(0)
            ;


            $em = $this->getDoctrine()->getManager();
            $em->persist($newForum);
            $em->flush();

            $this->addFlash('success', 'Forum créée avec succès !');
            return $this->redirectToRoute('forumlist',[
                'slug'=> $subCategory->getSlug()
            ]);
        }

        return $this->render('forum/newForum.html.twig', [
            'form' => $form->createView(),
            'forumlist' => $newForum,
        ]);
    }

    /**
     * @Route("/forum/{slug}", name="forum")
     */
    public function forum(Forum $forum, Request $request, PaginatorInterface $paginator): Response
    {
        //todo Utilisation du CommentRepository pour la requete DQL


        // On ajoute +1 aux views du forum
        $forum->setView(($forum->getView() + 1));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        //Paginator pour les commentaires de la page forum
        $requestedPage = $request->query->getInt('page', 1);

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }



        $query = $em->createQuery('SELECT c FROM App\Entity\Comment c ORDER BY c.publicationDate DESC');

        $comments = $paginator->paginate(
            $forum->getComments(),             // Requête de selection
            $requestedPage,     // Numéro de la page actuelle
            5              // Nombre d'articles par page
        );


        // Si l'utilisateur n'est pas connecté, on appel directement la vue sans traiter le formulaire en dessous
        if(!$this->getUser()){
            return $this->render('forum\forum.html.twig', [
                'forum' => $forum,
                'comments'=>$comments,
            ]);
        }

        // Création d'un nouveau Comment
        $newComment = new Comment();

        // Création d'un nouveau formulaire
        $form = $this->createForm(CreateCommentFormType::class, $newComment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // Récupération de la personne connectée
            $connectedUser = $this->getUser();

            // Hydratation du comment avec la date et l'auteur
            $newComment
                ->setPublicationDate( new DateTime() )
                ->setAuthor($connectedUser)
                ->setForum($forum)
            ;

            $connectedUser->setMessage($connectedUser->getMessage() + 1);
            // Récupération du manager général pour sauvegarder l'article en BDD
            $em = $this->getDoctrine()->getManager();

            $em->persist($newComment);

            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Le commentaire a été publié avec succès !');

            // supression des deux variables
            unset($newComment);
            unset($form);

            $newComment = new Comment();
            $form = $this->createForm(CreateCommentFormType::class, $newComment);
            // Redirection vers la page de l'article modifié
            return $this->redirectToRoute('forum', [
                'slug' => $forum->getSlug(),
            ]);
        }

        return $this->render('forum/forum.html.twig',[
            'forum'=>$forum,
            'comments'=>$comments,
            'form' =>$form->createView(),
        ]);
    }
    /**
     * Page moderation permettant de supprimer un commentaire
     *
     * @Route("/forum/suppression-commentaire/{id}/", name="comment_delete")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        // Récupération du token csrf dans l'url
        $tokenCSRF = $request->query->get('csrf_token');

        // Vérification que le token est valide
        if(!$this->isCsrfTokenValid('comment_delete' . $comment->getId(), $tokenCSRF ))
        {
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        } else {

            // Suppression du commentaire
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);

            //On retire un message a l'utilisateur
            $user = $comment->getAuthor();
            $user->setMessage(($user->getMessage() - 1 ));

            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }
        return $this->redirectToRoute('forum', [
            'slug' => $comment->getForum()->getSlug(),
        ]);
    }

    /**
     * Page moderation permettant de modifier un commentaire existant
     *
     * @Route("/forum/modifier-commentaire/{id}/", name="comment_edit")
     * @Security("is_granted('ROLE_USER')")
     */
    public function commentEdit( Comment $comment, Request $request): Response
    {

        // Création du formulaire de modification
        $form = $this->createForm(CreateCommentFormType::class, $comment);

        // Liaison des données POST avec le formulaire
        $form->handleRequest($request);

        $user = $this->getUser();

        if($user == $comment->getAuthor() || $user->getRoles() ){

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
                    return $this->redirectToRoute('forum', [
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
     * @Route("/modifier-categorie/{id}", name="edit_category")
     * @Security("is_granted('ROLE_MODERATOR')")
     * Methode permettant de modfier une catégorie
     */
    public function categoryEdit(Request $request, Category $category): Response
    {

        $form = $this->createForm(EditCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération du champ image
            $image = $form->get('image')->getData();

            // Récupération de l'emplacement du dossier des images de catégories
            $categoryImageDirectory = $this->getParameter('app_category_image_directory');

            // Récupération de l'utilisateur connecté
            $connectedUser = $this->getUser();


            // Si la catégorie a déjà une image, on la supprime
            if($category->getImage() != null){
                unlink( $categoryImageDirectory . $category->getImage() );
            }

            // Génération d'un nom de fichier jusqu'à en trouver un qui soit dispo
            do{

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();

                dump($newFileName);

            } while( file_exists( $categoryImageDirectory . $newFileName ) );

            // Mise à jour du nom de l'image dans la BDD
            $category->setImage($newFileName);
            $category->setTitle($form->get('title')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $image->move(
                $categoryImageDirectory,
                $newFileName
            );

            // Message flash de succès et redirection de l'utilisateur
            $this->addFlash('success', 'Catégorie modifiée avec succès !');

            return $this->redirectToRoute('home');

        }

        return $this->render('forum/category/editCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * Page moderation permettant de modifier un topic existant
     *
     * @Route("/forum/modifier-sujet/{id}/", name="forum_edit")
     * @Security("is_granted('ROLE_MODERATOR')")
     */


    public function publicationEdit(Forum $forum, Request $request): Response
    {

        // Création du formulaire de modification
        $form = $this->createForm(ForumFormType::class, $forum);

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
            return $this->redirectToRoute('forum', [
                'slug' => $forum->getSlug(),
            ]);

        }


        // Appel de la vue en envoyant le formulaire à afficher
        return $this->render('forum/topicEdit.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/forum/profil/{id}/", name="main_profil_forum")
     * @Security("is_granted('ROLE_USER')")
     */


    public function profil(User $user, Request $request): Response
    {
        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);

        $comments = $user->getComments();

        return $this->render('forum/profilForum.html.twig', [
            'comments' => $comments,
            'userForum' => $user,
        ]);
    }

    /**
     * @Route("supprimer-categorie/{id}", name="delete_category", methods={"POST"})
     * @Security("is_granted('ROLE_MODERATOR')")
     * Methode permettant de supprimer une catégorie
     */
    public function deleteCategory(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // On parcourt toute ses sous-catégories et tout leurs contenus pour les supprimer sinon erreur
            $subCategories = $category->getSubCategories();
            foreach($subCategories as $subCategory){
                $forums = $subCategory->getForums();
                foreach ($forums as $forum){
                    $comments = $forum->getComments();
                    foreach ($comments as $comment){
                        $entityManager->remove($comment);
                    }
                    $entityManager->remove($forum);
                }
                $entityManager->remove($subCategory);
            }

            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/modifier-sous-categorie/{id}", name="edit_sub_category")
     * @Security("is_granted('ROLE_MODERATOR')")
     * Methode permettant de modifier une sous-catégorie
     */
    public function subCategoryEdit(Request $request, SubCategory $subCategory): Response
    {
        $form = $this->createForm(EditCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération du champ image
            $image = $form->get('image')->getData();

            // Récupération de l'emplacement du dossier des images de catégories
            $subCategoryImageDirectory = $this->getParameter('app_subcategory_image_directory');

            // Récupération de l'utilisateur connecté
            $connectedUser = $this->getUser();


            // Si la catégorie a déjà une image, on la supprime
            if($subCategory->getImage() != null){
                unlink( $subCategoryImageDirectory . $subCategory->getImage() );
            }

            // Génération d'un nom de fichier jusqu'à en trouver un qui soit dispo
            do{

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();

                dump($newFileName);

            } while( file_exists( $subCategoryImageDirectory . $newFileName ) );

            // Mise à jour du nom de l'image dans la BDD
            $subCategory->setImage($newFileName);
            $subCategory->setTitle($form->get('title')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $image->move(
                $subCategoryImageDirectory,
                $newFileName
            );


            // Message flash de succès et redirection de l'utilisateur
            $this->addFlash('success', 'Sous-catégorie modifiée avec succès !');
            return $this->redirectToRoute('category', [
                'slug' => $subCategory->getCategory()->getSlug(),
            ]);

        }

        return $this->render('forum/editSubCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("supprimer-sous-categorie/{id}", name="delete_sub_category", methods={"POST"})
     * @Security("is_granted('ROLE_MODERATOR')")
     * Methode permettant de supprimer une sous-catégorie
     */
    public function deleteSubCategory(Request $request, SubCategory $subCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // On parcourt toute les forums et tout leurs contenus pour les supprimer sinon erreur (EntityManager is closed)
            $forums = $subCategory->getForums();

            foreach ($forums as $forum){
                $comments = $forum->getComments();
                foreach ($comments as $comment){
                    $entityManager->remove($comment);
                }
                $entityManager->remove($forum);
            }
            $entityManager->remove($subCategory);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("supprimer-forum/{id}", name="delete_forum", methods={"POST"})
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function deleteForum(Request $request, Forum $forum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // On parcourt toute les forums et tout leurs contenus pour les supprimer sinon erreur (EntityManager is closed)
            $comments = $forum->getComments();

                foreach ($comments as $comment){
                    $entityManager->remove($comment);
                }

            $entityManager->remove($forum);
            $entityManager->flush();

            $this->addFlash('success', 'Forum supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('forumList.html.twig');
    }

    /**
     * @Route("deplacer-forum/{id}", name="move_forum")
     * @Security("is_granted('ROLE_MODERATOR')")
     * Methode permettant de déplacer un forum dans une autre sous-catégorie
     */
    public function moveForum(Request $request, Forum $forum): Response
    {
        $form = $this->createForm(MoveForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();


            // Message flash de succès et redirection de l'utilisateur
            $this->addFlash('success', 'Sous-catégorie déplacée avec succès !');
            return $this->redirectToRoute('forumlist', [
                'slug' => $forum->getSubCategory()->getSlug(),
            ]);

        }

        return $this->render('forum/moveForum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }
}

