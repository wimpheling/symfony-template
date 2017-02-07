<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormError;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        // replace this example code with whatever you need
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAction($this->generateUrl('login'))
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Votre identifiant']
            ])
            ->add('motDePasse', PasswordType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Votre mot de passe']
            ])
            ->add('meConnecter', SubmitType::class, [
                'label' => 'Me connecter',
                'attr' => ['class' => 'button']
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $email = $form->getData()['email'];
            $motDePasse = $form->getData()['motDePasse'];
            /** @var User $user */
            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneByEmail($email);

            if (!$user) {
                $form->addError(new FormError('Utilisateur non trouvé ou mot de passe incorrect'));
            } else {
                // Get the encoder for the users password
                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($user);
                if ($encoder->isPasswordValid($user->getMotDePasse(), $motDePasse, $user->getSalt())) {
                    $request->getSession()->set('user_id', $user->getId());
                    return $this->redirectToRoute('homepage');
                } else {
                    $form->addError(new FormError('Utilisateur non trouvé ou mot de passe incorrect'));
                }
            }
        }

        return $this->render('@App/Login/login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $request->getSession()->remove('user_id');
        return $this->redirectToRoute('login');
    }
}
