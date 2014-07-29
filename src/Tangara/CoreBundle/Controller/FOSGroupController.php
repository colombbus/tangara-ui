<?php

namespace Tangara\CoreBundle\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\GroupController as BaseController;

class FOSGroupController extends BaseController
{
    /*
     * Si on doit rajouter des instructions en plus,
     * alors on creer les actions
     * puis on fait les instruction du parent()
     * et on fait une redirection vers le controller TangaraCoreBundle:Profile
     * et dans le controller TangaraCoreBundle:Profile on rajoute les instructions
     * et toujours dans le controller TangaraCoreBundle:Profile on fait un render le template
     */
    
    
    
    public function listAction() {
        $response = parent::registerAction();

        //puis redirection vers le controller de tangara-ui
        return $this->redirect($this->generateUrl('tangara_core_group_list'));
    }

    public function newAction(Request $request) {
        $response = parent::newAction($request);

        return $response;
    }

    public function editAction(Request $request, $groupName) {
        $response = parent::editAction($request, $groupName);

        return $response;
    }

}
