<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilType;
use App\Form\SearchType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppController extends AbstractController
{
    /**
     * @Route("/app", name="app")
     */
    public function index()
    {
        $user = $this->getUser();
        if ($user->getDetail() == null){
            return $this->redirectToRoute('form_details');
        }
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    /**
     * @Route("/app/profil", name="profil")
     */
    public function profil(Request $request, UserPasswordEncoderInterface $encoder){

        $user = new User();

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isRequired()){
            $data = $form->getData();
            $hash = $encoder->encodePassword($user, $data->getPassword());
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->find($this->getUser());
            $user->setPassword($hash);
            $user->setUsername($data->getUsername());
            $user->setEmail($data->getEmail());
            $entityManager->flush();
            $this->addFlash(
                'success',
                "Votre informations ont bien été mis à jour !"
            );
            return $this->redirectToRoute("app");
        }
        return $this->render('app/profil.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("app/recherche", name="form_recherche")
     */
    public function search(Request $request, ObjectManager $manager){

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isRequired()){
            $data = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $repository = $manager->getRepository(User::class);
            $query = $repository->createQueryBuilder('u')
                ->join('u.sports', 'c')
                ->where('u.nom = :nom')
                ->orWhere('u.prenom = :prenom')
                ->orWhere('c.nom = :sport')
                ->setParameter('nom', $data['nom'])
                ->setParameter('prenom', $data['prenom'])
                ->setParameter('sport', $data['sports']->getNom())
                ->getQuery()

                ->getResult();

            return $this->render('app/resultat.html.twig', [
                'resultat' => $query
            ]);

        }

        return $this->render('app/rechercher.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/app/mes-sports", name="messports")
     */
    public function viewSport()
    {
        return $this->render('app/mes-sport.html.twig');
    }

}
