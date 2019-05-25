<?php

namespace App\Controller;

use App\Entity\Detail;
use App\Entity\Sport;
use App\Form\DetailType;
use App\Form\SportType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{
    /**
     * @Route("/app/ajouter-details", name="form_details")
     */
    public function form_details(Request $request, ObjectManager $manager)
    {
        if ($this->getUser()->getDetail() !== null){
            return $this->redirectToRoute("app");
        }
        $detail = new Detail();


        $form = $this->createForm(DetailType::class,$detail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser();
            $user->setDetail($detail);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                "Vos informations ont bien été enregistrées"
            );
            return $this->redirectToRoute('form_sport');
        }

        return $this->render('app/form_details.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/app/ajouter-sport", name="form_sport")
     */
    public function form_sport(Request $request, ObjectManager $manager){
        $sport = new Sport();

        $form = $this->createForm(SportType::class, $sport);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $user = $this->getUser();

            $data = $form->getData();
            foreach ($data->getUsers() as $game){
                $user->addSport($game);
                $manager->persist($user);
                $manager->flush();
            }
            $this->addFlash(
                'success',
                "Vos sports ont bien été prise en compte !"
            );
            return $this->redirectToRoute('app');
        }

        return $this->render('app/form_sport.twig', [
            'form' => $form->createView()
        ]);

    }
}
