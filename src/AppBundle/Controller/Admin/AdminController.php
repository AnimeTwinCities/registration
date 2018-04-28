<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends BaseAdminController
{
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    public function prePersistUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }

    public function preUpdateUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function makeActiveAction()
    {
        $activeEvent = $this->getDoctrine()->getRepository(Event::class)->getCurrentEvent();
        $id = $this->request->query->get('id');
        $newActiveEvent = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $activeEvent->setActive(false);
        $newActiveEvent->setActive(true);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the 'list' view of the given entity
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function makeStaffAction()
    {
        $activeEvent = $this->getDoctrine()->getRepository(BadgeType::class)->getStaffBadgeType();
        $id = $this->request->query->get('id');
        $newActiveEvent = $this->getDoctrine()->getRepository(BadgeType::class)->find($id);
        $activeEvent->setStaff(false);
        $newActiveEvent->setStaff(true);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the 'list' view of the given entity
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }
}