<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Users Controller
 *
 * @Route("/users", schemes={"https"})
 * @Security("has_role('ROLE_SUPER')")
 */
class UsersController extends Controller
{
    /**
     * [indexAction description]
     *
     * @Route("/", name="users_index")
     * @param  Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();
        return $this->render('@App/users/view.html.twig',[
            'data' => $users
        ]);
    }

    /**
     * Edit user
     *
     * @Route("/{id}/edit", name="user_edit", requirements={"id": "\d+"})
     * @param  Request $request [description]
     * @param  User   $user    [description]
     * @return [type]           [description]
     */
    public function editAction(Request $request, User $users)
    {
        $form = $this->createForm('AppBundle\Form\UserType', $users);
        $form->handleRequest($request);
        return $this->render('@App/users/edit.html.twig', [
            'users' => $users,
            'form' => $form->createView()
        ]);
    }
}
