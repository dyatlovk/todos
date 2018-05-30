<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Category controller.
 *
 * @Security("has_role('ROLE_USER')")
 */
class CategoryController extends Controller
{
    /**
     * $user
     */
    public $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * Lists all category entities.
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());
        $categories = $em->getRepository('AppBundle:Category')->findBy(['userID' => $user->getId()], ['title' => 'asc']);
        return $this->render('@App/category/index.html.twig', array(
            'categories' => $categories,
        ));
    }

    /**
     * Creates a new category entity.
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm('AppBundle\Form\CategoryType', $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $category->setUserID($this->user->getId());
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_show', array('id' => $category->getId()));
        }

        return $this->render('@App/category/new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a category entity.
     */
    public function showAction(Category $category)
    {
        $this->checkAccess($category);
        $deleteForm = $this->createDeleteForm($category);
        return $this->render('@App/category/show.html.twig', array(
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing category entity.
     */
    public function editAction(Request $request, Category $category)
    {
        $this->checkAccess($category);
        $deleteForm = $this->createDeleteForm($category);
        $editForm = $this->createForm('AppBundle\Form\CategoryType', $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $category->setUserID($this->user->getId());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_edit', array('id' => $category->getId()));
        }

        return $this->render('@App/category/edit.html.twig', [
            'category'    => $category,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a category entity.
     */
    public function deleteAction(Request $request, Category $category)
    {
        $this->checkAccess($category);
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('category_index');
    }

    /**
     * Get todos by cat
     */
    public function todosAction(Request $request, Category $category)
    {
        $this->checkAccess($category);

        $catId = $request->get('id');
        $order = ($request->get('order'))?$request->get('order'):'title';
        $sort  = ($request->get('sort'))?$request->get('sort'):'asc';

        $em = $this->getDoctrine()->getManager();
        $data = $this->get('todos_model')->getTodos( 1, $this->user->getId(), $catId,$order,$sort, 20 );

        if($request->isXmlHttpRequest()) {
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceLimit(2);
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $encoders = [ new JsonEncoder() ];
            $normalizers = [ $normalizer ];
            $serializer = new Serializer($normalizers, $encoders);
            $jsonContent = $serializer->serialize($data, 'json');

            return new Response($jsonContent);
        } else {
            return $this->render('@App/category/todos.html.twig',[
                'data' => $data
            ]);
        }
    }

    /**
     * Creates a form to delete a category entity.
     *
     * @param Category $category The category entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    protected function checkAccess(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        // check user access
        $userCats = $em->getRepository("AppBundle:Category")->getUserCatsId($this->user->getId());
        $userAccess = in_array($category->getId(), $userCats);
        if(!$userAccess) throw $this->createNotFoundException('access denied');
        return true;
    }
}
