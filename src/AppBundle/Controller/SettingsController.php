<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Settings Controller
 *
 * @Security("has_role('ROLE_USER')")
 */
class SettingsController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('AppBundle:Settings')->findAll();

        return $this->render('@App/settings/index.html.twig', [
            'data' => $settings
        ]);
    }

    public function newAction(Request $request)
    {
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        $settings = new Settings();
        $form = $this->createForm('AppBundle\Form\SettingsType', $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $settings->setUserId($user->getId());
            $em->persist($settings);
            $em->flush();

            return $this->redirectToRoute('settings_index');
        }

        return $this->render('@App/settings/new.html.twig', [
            'data' => $settings,
            'form' => $form->createView()
        ]);
    }

    public function editAction(Request $request, Settings $settings)
    {
        $this->checkAccess($settings);

        $container  = $this->container->getParameter('app');

        // user
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        $form = $this->createForm('AppBundle\Form\SettingsType', $settings, [
            'container' => $container
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $settings->setUserId($user->getId());
            $em->persist($settings);
            $em->flush();
            return $this->redirectToRoute('settings_index');
        }

        return $this->render('@App/settings/edit.html.twig', [
            'data' => $settings,
            'form' => $form->createView()
        ]);
    }

    protected function checkAccess(Settings $settings)
    {
        $em = $this->getDoctrine()->getManager();

        // get user
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        // check user access
        $userSettings = $em->getRepository("AppBundle:Settings")->getUserSettingsId($user->getId());
        $userAccess = in_array($settings->getId(), $userSettings);
        if(!$userAccess) throw $this->createNotFoundException('access denied');
        return true;
    }

}
