<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Todo controller.
 *
 * @Route("todos")
 * @Security("has_role('ROLE_USER')")
 */
class TodosController extends Controller
{
    /**
     * Lists all todo entities.
     *
     * @Route("/", name="todos_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $todos = $em->getRepository('AppBundle:Todos')->findAll();

        return $this->render('todos/index.html.twig', array(
            'todos' => $todos
        ));
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute('todos_show', array('id' => $todo->getId()));
        }

        return $this->render('todos/new.html.twig', array(
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
        $deleteForm = $this->createDeleteForm($todo);

        return $this->render('todos/show.html.twig', array(
            'todo' => $todo,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing todo entity.
     *
     * @Route("/{id}/edit", name="todos_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Todos $todo)
    {
        $deleteForm = $this->createDeleteForm($todo);
        $editForm = $this->createForm('AppBundle\Form\TodosType', $todo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('todos_edit', array('id' => $todo->getId()));
        }

        return $this->render('todos/edit.html.twig', array(
            'todo' => $todo,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a todo entity.
     *
     * @Route("/{id}", name="todos_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Todos $todo)
    {
        $form = $this->createDeleteForm($todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($todo);
            $em->flush();
        }

        return $this->redirectToRoute('todos_index');
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
}
