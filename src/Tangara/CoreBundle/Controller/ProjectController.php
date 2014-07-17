<?php

namespace Tangara\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Tangara\CoreBundle\Entity\Document;
use Tangara\CoreBundle\Entity\Project;
use Tangara\CoreBundle\Entity\User;
use Tangara\CoreBundle\Entity\Group;
use Tangara\CoreBundle\Form\ProjectType;

class ProjectController extends Controller {

    public function indexAction() {
        //return $this->redirect($this->generateUrl('tangara_tangara_homepage'));
    }

    /*
     * Give all informations about the project
     */

    public function showAction(Project $project, $cat) {
        $contributors = array("user1", "user2", "user6");
        $manager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $files = $manager->getRepository('TangaraCoreBundle:Document')->findBy(array('ownerProject' => $project->getId()));

        if ($cat == 1) {//view for user project
            return $this->render('TangaraCoreBundle:Project:show_user_project.html.twig', array(
                        'project' => $project,
                        'contributors' => $contributors,
                        'files' => $files
            ));
        } else if ($cat == 2) {//view for group project
            return $this->render('TangaraCoreBundle:Project:show_group_project.html.twig', array('project' => $project));
        }
    }

    public function listAction() {

        $user = $this->get('security.context')->getToken()->getUser();
        $manager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $projects = $manager->getRepository('TangaraCoreBundle:Project')->findAll();


        //echo $this->get('tangara_project.uploader')->getUploadDirectory();

        $repository = $manager->getRepository('TangaraCoreBundle:Project');
        $admin = '"admin"';

        //$conn = $this->get('database_connection');
        //$different = $conn->fetchAll('SELECT ProjectManager FROM project WHERE ProjectManager != '.$admin);

        $query = $repository->createQueryBuilder('project')
                ->where('project.projectManager != :ProjectManager')
                ->setParameter('ProjectManager', 'admin')
                ->getQuery();

        $different = $query->getResult();

        return $this->render('TangaraCoreBundle:Project:list.html.twig', array(
                    'projects' => $projects,
                    'different' => $different
        ));
    }

    public function editAction(Project $project) {
        $user = $this->get('security.context')->getToken()->getUser();

        //$fs->copy($originFile, $targetFile)
        //$fs->mkdir('C:\tangara\\' . $id);
        //
        //$fs->mkdir('C:\tangara\\' . $id); // projects
        //$fs->mkdir('C:\tangara\user'.$id); // perso projects
        //
        //if ($fs->exists('/home/tangara/'..)){
        //}

        $manager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $form = $this->createForm(new ProjectType(), $project);

        //$request->query->get('page'); // retourne un paramètre $_GET
        //$request->request->get('page'); // retourne un paramètre $_POST

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid())
                $p = $form->getData();

            $manager->persist($project);
            $manager->flush();

            return $this->redirect($this->generateUrl('tangara_project_show', array('cat' => 1, 'id' => $project->getId())));
        }

        return $this->render('TangaraCoreBundle:Project:edit.html.twig', array(
                    'form' => $form->createView(),
                    'user' => $user,
                    'project' => $project
        ));
    }

    public function createAction() {
        //echo '**' . $this->get('kernel')->getRootDir() . '**';
        $tangarajs = $this->container->getParameter('tangara_core.settings.directory.tangarajs');
        
        return $this->render('TangaraCoreBundle:Project:create.html.twig', array(
        'tangarajs' => $tangarajs
        ));
        
    }

    /*
     * Create a new project
     */

    public function newAction($user_id = null, $group_id = null) {

        $user = $this->get('security.context')->getToken()->getUser();

        $userId = $user->getId();

        $project = new Project();
        $project->setProjectManager($user);

        $projectId = $project->getId();


        $base_path = 'C:/tangara/';
        $project_user_path = $base_path . $userId;
        $project_path = $base_path . $projectId;

        $manager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $group_member = $user->getGroups();

        $form = $this->createForm(new ProjectType(), $project);

        if ($request->isMethod('POST')) {

            $form->bind($this->getRequest());
            $em = $this->getDoctrine()->getManager();
            //$this->file->move($project_path, $this->file->getClientOriginalName());
            /*
              $allProjects = $user->getProjects();

              foreach ($allProjects as $tmp){
              echo $tmp->getName();
              }
             */
            /*
              $tmp = $em->getRepository('TangaraCoreBundle:User')
              ->getAllProcjects($project->getName());

              $tmp = $tmp->getProjects();

              foreach ($tmp as $key){
              echo $key->getName();
              }
             */


            
            
            
              if ($user_id != null) { //project of user
                
                $allProjects = $user->getProjects();
                $projectExist = isUserProjectExist($allProjects, $project->getName());
                  
                $user->addProjects($project);
                $project->setUser($user);
                $cat = 1;
                
            } elseif ($group_id != null) { //project of group
                $group = $em->getRepository('TangaraCoreBundle:Group')->find($group_id);
                
                $allProjects = $group->getProjects();
                $projectExist = isGroupProjectExist($allProjects, $project->getName());
                
                $group->addProjects($project);
                $project->setGroup($group);
                $cat = 2;
                
            }
            
            if($projectExist == false){
                $em->persist($project);
                $em->flush();
                return $this->redirect($this->generateUrl('tangara_project_show', array('id' => $project->getId(), 'cat' => $cat)
                ));
            }
            return new Response('Un projet avec me meme nom existe deja.');
            
        }

        return $this->render('TangaraCoreBundle:Project:new.html.twig', array(
                    'form' => $form->createView(),
                    'userid' => $userId,
                    'username' => $user,
                    'project' => $project,
                    'project_owner_group' => $group_member,
                    'g_id' => $group_id,
                    'u_id' => $user_id
        ));
    }

    public function uploadAction(Project $project) {
        $request = $this->getRequest();
        $user_id = $this->get('security.context')->getToken()->getUser()->getId();
        $projectId = $project->getId();

        //$base_path = $this->get('tangara_project.uploader');
        $base_path = '/home/elise/NetBeansProjects/tangara-data';
        $project_user_path = $base_path . "/" . $user_id;
        $project_path = $base_path . "/" . $projectId;
        $cat = 1;


        $document = new Document();
        $form = $this->createFormBuilder($document)
                //->add('name')
                ->add('file')
                ->getForm()
        ;

        if ($this->getRequest()->isMethod('POST')) {
            $form->bind($this->getRequest());
            $em = $this->getDoctrine()->getManager();
            $document->setOwnerProject($project);
        

            $document->upload();
            //$file_uploaded = $request->get('file');
            //$fs->copy($file_uploaded, $project_user_path);
            $em->persist($document);
            $em->flush();

            //$ret = 'done ' . $file_uploaded ; 
//            return $this->redirect('tangara_project_show', array(
//                        'cat' => $cat,
//                        'id' => $projectId
//                            )
//            );
            return new Response("ok");
        }

        return $this->render('TangaraCoreBundle:Project:upload.html.twig', 
                array(
                    'form' => $form->createView()
        ));
    }

  

}

/**
 * Request group list that user isn't member
 * 
 * @param array $allgroups 
 * @param array $user_groups
 * @return groupsWithoutMe list that user isn't member
 */
//function groupsWithoutMe($allgroups, $user_groups) {
//
//    foreach ($allgroups as $all) {
//        $trigger = true;
//        foreach ($user_groups as $ug) {
//            if ($all->getName() == $ug->getName()) {
//                $trigger = false;
//                break;
//            }
//        }
//        if ($trigger == true) {
//            $groupsWithoutMe[] = $all;
//        }
//    }
//
//    return $groupsWithoutMe;
//}

function isUserProjectExist($allProjects, $pname) {
    
    foreach ($allProjects as $p) {
        if ($p->getName() == $pname) {
            return true;
        }
    }
    return false;
}

function isGroupProjectExist($allProjects, $pname) {
    
    foreach ($allProjects as $p) {
        if ($p->getName() == $pname) {
            return true;
        }
    }
    return false;
}
