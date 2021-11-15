<?php

namespace MD\Bundle\UserBundle\Controller\Administration;

use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \MD\Bundle\UserBundle\Entity\Account;
use MD\Bundle\UserBundle\Entity\Role;
use MD\Utils\Validate as V;
use MD\Utils\OAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use MD\Bundle\UserBundle\Entity\Person;
use MD\Bundle\UserBundle\Form\PersonType;

/**
 * Person controller.
 *
 * @Route("/person")
 */
class PersonController extends Controller {

    /**
     * Lists all Person entities.
     
     * @Route("/", name="person")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UserBundle:Person')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Create a new Person and his account
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @Route("/new", name="person_create")
     * @Method("POST")
     */
    public function createAction() {
        $errorMessage = "";
        $personPostData = $this->collectPOST();
        $validatePersonResponse = $this->addPerson($personPostData);
        $responseData = json_decode($validatePersonResponse->getContent());
        if ((int) $responseData->valid == -1) {
            $errorMessage = "Sorry the Email you entered is already used ";
        } else if ((int) $responseData->valid == -2) {
            $errorMessage = "Sorry please review the data you have enter and fill all the required fields ";
        } else {
            $person = unserialize($responseData->person);
            $this->addAccount($personPostData, $person);
            return $this->redirect($this->generateUrl('person'));
        }
        return
                $this->render("UserBundle:Administration/Person:new.html.twig", array(
                    "errorMessage" => $errorMessage
        ));
    }

    protected function collectPOST() {
        $user = new \stdClass();
        $user->username = $this->getRequest()->get('username');
        $user->password = $this->getRequest()->get('password');
        $user->email = $this->getRequest()->get('email');
        $user->phone = $this->getRequest()->get('phone');
        $user->gender = $this->getRequest()->get('gender');
        $user->state = $this->getRequest()->get('active');
        $day = $this->getRequest()->get('day');
        $month = $this->getRequest()->get('month');
        $year = $this->getRequest()->get('year');
        $birthDateFormat = $day . "-" . $month . "-" . $year;
        $user->birthdate = $birthDateFormat;
        return $user;
    }

    /**
     * Validate Person entity
     * -1 => the email is used in the database
     * -2 => the required data is not complete
     */
    public function validatePerson($personPostData) {
        $person = new Person();
        $person->setUsername($personPostData->username);
        $person->setEmail($personPostData->email);
        $person->setGender($personPostData->gender);
        $person->setPhone($personPostData->phone);
        $validator = $this->get('validator');
        $errors = $validator->validate($person);
        $em = $this->getDoctrine()->getManager();
        $validateEmail = $em->getRepository('UserBundle:Account')->getUserByEmail($personPostData->email);
        if ($validateEmail) {
            $returnArray = array("person" => null, 'valid' => -1);
            $returnData = json_encode($returnArray);
            return new Response($returnData);
        }
        if (count($errors) > 0) {
            $returnArray = array("person" => null, 'valid' => -2);
            $returnData = json_encode($returnArray);
            return new Response($returnData);
        }
        $returnArray = array("person" => serialize($person), 'valid' => 1);
        $returnData = json_encode($returnArray);
        return new Response($returnData);
    }

    /**
     * Add New Person
     */
    public function addPerson($personPostData) {
        $em = $this->getDoctrine()->getManager();
        $response = $this->validatePerson($personPostData);
        $responseData = json_decode($response->getContent());
        if ((int) $responseData->valid < 0) {
            return new Response(json_encode($responseData));
        }
        $person = unserialize($responseData->person);

        $em->persist($person);
        $em->flush();
        $returnArray = array("person" => serialize($person), 'valid' => 1);
        $returnData = json_encode($returnArray);
        return new Response($returnData);
    }

    /**
     * Add New Account
     */
    public function addAccount($personPostData, $person) {
        $em = $this->getDoctrine()->getManager();
        $account = new Account();
        $account->setUsername($personPostData->email);
        $account->setPassword($personPostData->password);
        $account->setState($personPostData->state);
        $em->persist($account);
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($account);
        $password = $encoder->encodePassword($account->getPassword(), $account->getSalt());
        $account->setPassword($password);
        $em->persist($account);
        $em->flush();
        $person->addAccount($account);
        $em->persist($account);
        $em->flush();
        $roleUser = $em->getRepository('UserBundle:Role')->find(Role::ROLE_ADMIN);
        $roleUser->addAccount($account);
        $em->persist($roleUser);
        $em->flush();
        // creating the ACL
        $aclProvider = $this->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($account);
        $acl = $aclProvider->createAcl($objectIdentity);

        // retrieving the security identity of the currently logged-in user
        $securityIdentity = UserSecurityIdentity::fromAccount($account);

        // grant owner access
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);
    }

    /**
     * Displays a form to create a new Person entity.
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @Route("/new", name="person_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction() {
        $entity = new Person();
        $errorMessage = " ";
        return array(
            'entity' => $entity,
            'errorMessage' => $errorMessage
        );
    }

    /**
     * Displays a form to edit an existing Person entity.
     *
     * @Route("/{id}/edit", name="person_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UserBundle:Person')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Person entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Person entity.
     *
     * @param Person $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Person $entity) {
        $form = $this->createForm(new PersonType(), $entity, array(
            'action' => $this->generateUrl('person_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Person entity.
     *
     * @Route("/{id}", name="person_update")
     * @Method("PUT")
     * @Template("UserBundle:Person:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UserBundle:Person')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Person entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('person'));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Person entity.
     *
     * @Route("/{id}", name="person_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UserBundle:Person')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Person entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('person'));
    }

    /**
     * Creates a form to delete a Person entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('person_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
