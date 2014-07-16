<?php

namespace Tangara\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Tangara\CoreBundle\Entity\Group;
use Tangara\CoreBundle\Controller\ProjectController;

use FOS\UserBundle\Controller\GroupController as BaseController;


class GroupController extends BaseController
{
     /**
     * Show all groups
     */
    public function listAction()
    {
        $groups = $this->container->get('fos_user.group_manager')->findGroups();
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $user_groups = $user->getGroups();
        $strangerGroups = groupsWithoutMe($groups, $user_groups);

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Group:list.html.'.$this->getEngine(), array(
            'groups' => $groups, 
            'nogroups' => $strangerGroups));
    }
    
    public function newAction(\Symfony\Component\HttpFoundation\Request $request)
    {       
        $response = parent::newAction($request);
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $group = new Group();
        $group->setName("AdminGroup11");
        $group->addUser($user);
        
        return $response;
    }    
    
    /*
     * Give all informations about the group
     */
    public function infoGroupAction(Group $group)
    {       
        return $this->container->get('templating')->renderResponse('TangaraCoreBundle:Group:new.html.twig', array('group' => $group));
    }
    
}

/*
 * This function give all groups where i am not a member
 */

function groupsWithoutMe($allgroups, $user_groups) {

    foreach ($allgroups as $group) {
        $trigger = true;
        foreach ($user_groups as $user) {
            if ($group->getId() == $user->getId()) {
                $trigger = false;
                break;
            }
        }
        if ($trigger == true) {
            $groupsWithoutMe[] = $group;
        }
    }
    return $groupsWithoutMe;
}
