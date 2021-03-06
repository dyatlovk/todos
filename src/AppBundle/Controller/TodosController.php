<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Todo controller.
 *
 * @Route("todos", schemes={"https"})
 * @Security("has_role('ROLE_USER')")
 */
class TodosController extends Controller
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
     * Lists all todo entities.
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->isXmlHttpRequest()) {
            $todos = $em->getRepository('AppBundle:Todos')->findBy([
                'userID' => $this->user->getId(),
                'status' => 1
            ]);
            $jsonContent = $this->serialize($todos);
            return new Response($jsonContent);
        } else {
            $todos = $this->get('todos_model')->todosList($this->user->getId());
            return $this->render('@App/todos/index.html.twig', array(
                'todos' => $todos,
            ));
        }
    }

    /**
     * Creates a new todo entity.
     *
     * @Route("/new", name="todos_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $todo = new Todos();
        $form = $this->createForm('AppBundle\Form\TodosType', $todo);
        $form->handleRequest($request);

        if($request->isXmlHttpRequest() ) {
            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();
                $todo->setUserID($this->user->getId());
                $em->persist($todo);
                $em->flush();
                $jsonResponse = $this->serialize($todo);
                return new Response($jsonResponse);
            }
            return $this->render('@App/todos/_new_form.html.twig', [
                'todo' => $todo,
                'form' => $form->createView()
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $todo->setUserID($this->user->getId());
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute('todos_show', array('id' => $todo->getId()));
        }

        return $this->render('@App/todos/new.html.twig', array(
            'todo' => $todo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a todo entity.
     *
     * @Route("/{id}", name="todos_show")
     * @Method("GET")
     */
    public function showAction(Todos $todo)
    {
        $this->checkAccess($todo);

        $em = $this->getDoctrine()->getManager();

        $deleteForm = $this->createDeleteForm($todo);

        return $this->render('@App/todos/show.html.twig', array(
            'todo' => $todo,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing todo entity.
     *
     * @Route("/{id}/edit", name="todos_edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Todos $todo)
    {
        $this->checkAccess($todo);

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\TodosType', $todo);
        $editForm->handleRequest($request);

        if($request->isXmlHttpRequest() ) {
            if( $editForm->isSubmitted() ) {
                if(!$editForm->isValid()) return new JsonResponse(['error'=>$this->getErrorMessages($editForm)]);
                $this->getDoctrine()->getManager()->flush();
                $jsonResponse = $this->serialize($todo);
                return new Response($jsonResponse);
            }
            return $this->render('@App/todos/_edit_form.html.twig', [
                'todo' => $todo,
                'edit_form' => $editForm->createView()
            ]);
        }
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('todos_edit', ['id' => $todo->getId()]);
        }

        return $this->render('@App/todos/edit.html.twig', [
            'todo' => $todo,
            'edit_form' => $editForm->createView()
        ]);
    }

    /**
     * Deletes a todo entity.
     *
     * @Route("/{id}/delete", name="todos_delete")
     */
    public function deleteAction(Request $request, Todos $todo)
    {
        $this->checkAccess($todo);
        $em = $this->getDoctrine()->getManager();
        $em->remove($todo);
        $em->flush();

        return $this->redirectToRoute('todos_index');
    }

    /**
     * Close todo
     *
     * @Route("/{id}/close", name="todos_close")
     * @param  Request $request [description]
     * @param  Todos   $todo    [description]
     */
    public function closeAction(Request $request, Todos $todo)
    {
        $this->checkAccess($todo);

        $em = $this->getDoctrine()->getManager();
        $history = $request->getRequestUri();
        $todo->setStatus(0);
        $em->persist($todo);
        $em->flush();
        if($request->isXmlHttpRequest()) {
            $jsonResponse = $this->serialize($todo);
            return new Response($jsonResponse);
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates a form to delete a todo entity.
     *
     * @param Todos $todo The todo entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Todos $todo)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('todos_delete', array('id' => $todo->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    protected function checkAccess(Todos $todo)
    {
        $em = $this->getDoctrine()->getManager();

        // check user access
        $userTodos = $em->getRepository("AppBundle:Todos")->getUserTodosId($this->user->getId());
        $userAccess = in_array($todo->getId(), $userTodos);
        if(!$userAccess) throw $this->createNotFoundException('access denied');
        return true;
    }

    /**
     * Swrialize array to JSON
     * @param  array $data
     * @return json
     */
    protected function serialize($data)
    {
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(2);
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $encoders = [ new JsonEncoder() ];
        $normalizers = [ $normalizer ];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($data, 'json');
        return $jsonContent;
    }

    protected function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
