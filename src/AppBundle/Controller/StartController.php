<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StartController extends Controller
{
    /**
     * @Route("/", name="homepage")
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
            $cats = [];
            $categories = $em
            ->getRepository('AppBundle:Category')
            ->findBy(['userID' => $user->getId(), 'status' => 1]);
            foreach ($categories as $key => $value) {
                $cats['data'][] = $value;
                $cats['count'][] = count($value->getTodos());
            }
            $todos = $em
            ->getRepository('AppBundle:Todos')
            ->findBy(['userID' => $user->getId(), 'status' => 1]);
            return $this->render('@App/start/index.html.twig', [
                'cats' => $cats,
                'todos' => $todos
            ]);
        } else {
            return $this->forward('FOSUserBundle:Security:login');
        }
    }
}
