<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StartController extends Controller
{
    /**
     * $user
     * @var [type]
     */
    public $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @Route("/", name="homepage", schemes={"https"})
     */
    public function indexAction(Request $request)
    {
        // get this user data
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        // redirect to login if user not auth
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $em = $this->getDoctrine()->getManager();
            $model = $this->get('todos_model')->findAll( 1, $user->getId() );
            return $this->render('@App/start/index.html.twig', ['model' => $model]);
        } else {
            return $this->forward('FOSUserBundle:Security:login');
        }
    }
}
