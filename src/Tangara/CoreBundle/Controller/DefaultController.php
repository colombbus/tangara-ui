<?php

namespace Tangara\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Tangara\CoreBundle\Entity\Project;

class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('TangaraCoreBundle:Default:homepage.html.twig');
    }

    public function ajaxAction(){
        return $this->render('TangaraCoreBundle:Default:ajax_symfony.html.twig');
    }

}
