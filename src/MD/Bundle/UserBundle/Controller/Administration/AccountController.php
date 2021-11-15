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
use MD\Bundle\UserBundle\Entity\Person;
use MD\Utils\Validate as V;
use MD\Utils\OAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Account controller.
 *
 * @Route("/")
 */
class AccountController extends Controller {

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template("UserBundle:Administration/Account:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('UserBundle:Account')->findAll();
        return array(
            'users' => $users
        );
    }

    /**
     * Deletes a Supplier entity.
     *
     * @Route("/user-block", name="user_block")
     * @Method("POST")
     */
    public function blockAction() {
        $id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Supplier entity.');
        }
        $entity->setState(0);
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Deletes a Supplier entity.
     *
     * @Route("/user-active", name="user_active")
     * @Method("POST")
     */
    public function activeAction() {
        $id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Supplier entity.');
        }
        $entity->setState(1);
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @Method("GET")
     * @Template("UserBundle:Administration/Account:new.html.twig")
     */
    public function newAction() {
        $em = $this->getDoctrine()->getManager();
        $entity = new Account();
        $error = "";
        return array(
            'entity' => $entity,
            'errorMessage' => $error
        );
    }

    /**
     * Create a new Account using Facebook entity.
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @Route("/new", name="account_create")
     */
    public function createAction() {
        $em = $this->getDoctrine()->getManager();

        $accountPostData = $this->collectPOST();
        $validateEmail = $em->getRepository('UserBundle:Account')->getUserByEmail($accountPostData->email);
        if ($validateEmail) {
            return $this->render("UserBundle:Administration/Account:new.html.twig", array(
                        "errorMessage" => "sorry u can't use this email beacuse it's already taken  "
            ));
        } else {
            $person = new Person();
            $person->setUsername($accountPostData->email);
            $person->setFamilyname($accountPostData->username);
            $person->setEmail($accountPostData->email);
            $person->setGender($accountPostData->gender);
            $em->persist($person);
            $em->flush();
            $account = new Account();
            $account->setPerson($person);
            $account->setId($person->getId());
            $account->setUsername($accountPostData->email);
            $account->setPassword($accountPostData->password);
            $account->setState($accountPostData->state);
            $em->persist($account);
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($account);
            $password = $encoder->encodePassword($account->getPassword(), $account->getSalt());
            $account->setPassword($password);
            $em->persist($account);
            $em->flush();

            $roleUser = $em->getRepository('UserBundle:Role')->findOneBy(array("name" => "ROLE_ADMIN"));
            $roleUser->addAccount($account);
            $em->persist($roleUser);
            $em->flush();
//            // creating the ACL
//            $aclProvider = $this->get('security.acl.provider');
//            $objectIdentity = ObjectIdentity::fromDomainObject($account);
//            $acl = $aclProvider->createAcl($objectIdentity);
//
//            // retrieving the security identity of the currently logged-in user
//            $securityIdentity = UserSecurityIdentity::fromAccount($account);
//
//            // grant owner access
//            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
//            $aclProvider->updateAcl($acl);
            return $this->redirect($this->generateUrl('user'));
        }
    }

    protected function collectPOST() {
        $user = new \stdClass();
        $user->username = $this->getRequest()->get('username');
        $user->password = $this->getRequest()->get('password');
        $user->email = $this->getRequest()->get('email');
        $user->phone = $this->getRequest()->get('phone');
        $user->gender = $this->getRequest()->get('gender');
        $user->state = $this->getRequest()->get('active');
        return $user;
    }

}
