<?php

namespace MD\Bundle\UserBundle\Controller\FrontEnd;

use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \MD\Bundle\UserBundle\Entity\Account;
use \MD\Bundle\UserBundle\Entity\Person;
use MD\Bundle\UserBundle\Entity\Role;
use MD\Bundle\ServicesBundle\Entity\AlertMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use MD\Utils\Validate as V;
use MD\Utils\OAuth;
use \MD\Bundle\MediaBundle\Utils\SimpleImage;
use \MD\Bundle\MediaBundle\Entity\Image;

/**
 * Account controller.
 * @Route("/user")
 */
class AccountController extends Controller {

    public function loginAction() {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                    SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
                        'AdminTemplateBundle::login.html.twig', array(
                    // last username entered by the user
                    'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
                        )
        );
    }

    /**
     * displayes user bookings
     * @Secure(roles="ROLE_USER")
     * @Route("/my-bookings", name="fe_my_bookings")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Usr:myBookings.html.twig")
     */
    public function myBookingsAction() {
        $em = $this->getDoctrine()->getManager();
        $person = $this->getUser()->getPerson();
        $allbookingUsers = $em->getRepository('TravelBundle:BookingPerson')->findBy(array('user' => $person, 'type' => Person::MAIN));
        $expenseTypes = $em->getRepository('TravelBundle:ExpenseType')->findAll();

        return array(
            'allBookingUsers' => $allbookingUsers,
            'expenseTypes' => $expenseTypes
        );
    }

    /**
     * Displays a form to create a new User entity.
     * @Secure(roles="ROLE_USER")
     * @Route("/my-favourite", name="fe_my_favorite_supplier")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Account:favorite.html.twig")
     */
    public function favouriteAction() {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUser()->getId();
        $suppliers = $em->getRepository('UserBundle:Account')->getFavouriteSuppliers($userId);
        $qualifiedBadgeId = \MD\Bundle\ServicesBundle\Entity\Badge::Qualified;
        $badgeQuilified = $em->find('ServicesBundle:Badge', $qualifiedBadgeId);
        if (count($suppliers) > 0) {
            foreach ($suppliers as $supplier) {
                if ($supplier != null) {
                    $this->updateBadge($supplier, $badgeQuilified);
                }
            }
        } else
            $suppliers = array();
        return array(
            'suppliers' => $suppliers,
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/change-password", name="fe_change_password")
     * @Method("POST")
     * @Template("UserBundle:FrontEnd/Usr:changePassword.html.twig")
     */
    public function changePasswordAction() {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $Message = null;
        $nPassword = $this->getRequest()->get('n_password');
        $rPassword = $this->getRequest()->get('r_password');
        if ($rPassword == $nPassword) {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($nPassword, $user->getSalt());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();
            $token = new UsernamePasswordToken(
                    $user, null, 'members_secured_area', array('ROLE_USER')
            );
            $securityContext = $this->container->get('security.context');
            $securityContext->setToken($token);
            $session = $this->get('session');
            $session->set('_security_' . 'members_secured_area', serialize($token));

            $Message = "لقد تم تغيير كلمة المرور بنجاح";
        } else {
            $Message = "fail";
        }

        return array(
            'message' => $Message
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/change-password", name="fe_pre_change_password")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Usr:changePassword.html.twig")
     */
    public function preChangePasswordAction() {

        return array(
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/forgot-password", name="fe_pre_forgot_password")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Usr:forgot.html.twig")
     */
    public function preForgotPasswordAction() {
        $failMessage = NULL;
        $successMessage = NULL;
        return array(
            'failMessage' => $failMessage,
            'successMessage' => $successMessage
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/forgot-password", name="fe_forgot_password")
     * @Method("POST")
     * @Template("UserBundle:FrontEnd/Usr:forgot.html.twig")
     */
    public function forgotPasswordAction() {
        $em = $this->getDoctrine()->getManager();
        $email = $this->getRequest()->get('email');
        $user = $em->getRepository('UserBundle:Account')->getAccountByUSername($email);
        if ($user) {
            $code = md5($user->getPerson()->getId());
            $message = \Swift_Message::newInstance()
                    ->setSubject('Forgot the password')
                    ->setFrom('support@theholidayers.com')
                    ->setTo($email)
                    ->setBody(
                    $this->renderView(
                            'UserBundle:FrontEnd/Usr:forgotEmail.html.twig', array(
                        'verifyCode' => $code
                            )
                    )
                    , 'text/html');
            $this->get('mailer')->send($message);
            $successMessage = " تفقد بريدك الاكترونى ";
            $failMessage = NULL;
        } else {
            $failMessage = "عفوا هذا البريد الإلكتروني قد تم إدخاله من قبل.";
            $successMessage = NULL;
        }
        return array(
            'failMessage' => $failMessage,
            'successMessage' => $successMessage
        );
    }

    /**
     * @Route("/verify-booking/{code}/{bookingId}", name="fe_verify_user_booking")
     */
    public function verifyUserBookingAction($code, $bookingId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:Account")->getSelectedAccount($code, Account::BLOCKED);
        if ($user) {
            $user->setState(Account::VERIFIED);
            $em->persist($user);
            $em->flush();
            $token = new UsernamePasswordToken(
                    $user, null, 'members_secured_area', array('ROLE_USER')
            );
            $securityContext = $this->container->get('security.context');
            $securityContext->setToken($token);
            $session = $this->get('session');
            $session->set('_security_' . 'members_secured_area', serialize($token));
            return $this->redirect($this->generateUrl('fe_my_booking_details', array("bookingId" => $bookingId)));
        } else {
            exit("here blocked ");
        }
    }

    /**
     * @Route("/verify-selected-booking/{code}/{bookingId}", name="fe_verify_selected_user_booking")
     */
    public function verifyExtraUserBookingAction($code, $bookingId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:Account")->getSelectedPerson($code);
        if ($user) {
            return $this->redirect($this->generateUrl('fe_my_booking_details', array("bookingId" => $bookingId, "userId" => $user->getId())));
        } else {
            exit("You are blocked  ");
        }
    }

    /**
     *  Loged the user in
     */
    public function doLogin($user, $route = null) {
        $token = new UsernamePasswordToken(
                $user, null, 'members_secured_area', array('ROLE_USER')
        );
        $securityContext = $this->container->get('security.context');
        $securityContext->setToken($token);
        $session = $this->get('session');
        $session->set('_security_' . 'members_secured_area', serialize($token));
//        var_dump($session->get('_security_' . 'members_secured_area'));
        if ($route != null && $route != "http://theholidayers_prod_2.mdapp.me/app_dev.php/login") {
            $newResponse = new RedirectResponse($route);
            return $newResponse;
        }
        return $this->redirect($this->generateUrl('fe_usr_edit', array(
                            "id" => $user->getId()
        )));
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="fe_user_new")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Usr:new.html.twig")
     */
    public function newAction() {
        $em = $this->getDoctrine()->getManager();
        $entity = new Account();
        return array(
            'entity' => $entity,
        );
    }

    /**
     * Create a new Account using Facebook entity.
     * @Route("/new", name="fe_usr_create")
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
            $code = md5($person->getId());
            $message = \Swift_Message::newInstance()
                    ->setSubject('User Verfication')
                    ->setFrom('Support@theholidayers.com')
                    ->setTo($person->getEmail())
                    ->setBody(
                    $this->renderView(
                            'UserBundle:FrontEnd/Usr:VerifyEmail.html.twig', array(
                        'usr' => $person,
                        'verifyCode' => $code
                            )
                    )
                    , 'text/html');
            $this->get('mailer')->send($message);
            return $this->render(
                            'UserBundle:FrontEnd/Usr:thank.html.twig', array(
                        'verifyCode' => $code,
                        'userId' => $person->getId(),
                            )
            );
        }
        return
                $this->render("UserBundle:FrontEnd/Usr:new.html.twig", array(
                    "errorMessage" => $errorMessage
        ));
    }

    /**
     * Validate Person entity
     * -1 => the email is used in the database
     * -2 => the required data is not complete
     */
    public function validatePerson($personPostData) {
        $person = new Person();
        $person->setUsername($personPostData->username);
        $person->setUsername($personPostData->familyname);
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
        $account->setState(Account::NOT_VERIFIED);
        $em->persist($account);
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($account);
        $password = $encoder->encodePassword($account->getPassword(), $account->getSalt());
        $account->setPassword($password);
        $account->setPerson($person);
        $em->persist($account);
        $em->flush();
        $roleUser = $em->getRepository('UserBundle:Role')->find(Role::ROLE_USER);
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
        return $account;
    }

    /**
     * Create a new Account entity.
     *
     * @Route("/resend-email", name="fe_user_resend_email")
     */
    public function sendEmailAction() {
        $code = $this->getRequest()->get('code');
        $userId = $this->getRequest()->get('userId');
        $em = $this->getDoctrine()->getManager();
        $usr = $em->getRepository('UserBundle:Account')->find($userId);
        $message = \Swift_Message::newInstance()
                ->setSubject('User Verfication')
                ->setFrom('server@alista.com')
                ->setTo($usr->getEmail())
                ->setBody(
                $this->renderView(
                        'UserBundle:FrontEnd/Usr:VerifyEmail.html.twig', array(
                    'usr' => $usr,
                    'verifyCode' => $code
                        )
                )
                , 'text/html');
        $this->get('mailer')->send($message);
        return $this->render(
                        'UserBundle:FrontEnd/Usr:thank.html.twig', array(
                    'verifyCode' => $code,
                    'userId' => $userId,
                        )
        );
    }

    /**
     * Create a new Account entity.
     * @Secure(roles="ROLE_USER")
     * @Route("/edit/{id}", name="fe_usr_edit")
     * @Method("GET")
     * @Template("UserBundle:FrontEnd/Usr:edit.html.twig")
     */
    public function editAction($id) {
        $message = null;

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:Account')->find($id);
        if ($user != null) {
            $securityContext = $this->get('security.context');
// check for edit access
            if (false === $securityContext->isGranted('EDIT', $user)) {
                throw new AccessDeniedException();
            }
            if ($user->getPerson()->getVisaStartDate() != null) {
                $visaStartDate = date_format($user->getPerson()->getVisaStartDate(), 'Y-m-d');
                $visaStartDateUnits = explode("-", $visaStartDate);
                $vsYear = $visaStartDateUnits[0];
                $vsMonth = $visaStartDateUnits[1];
                $vsDay = $visaStartDateUnits[2];
            } else {
                $vsYear = null;
                $vsMonth = null;
                $vsDay = null;
            }
            if ($user->getPerson()->getVisaEndDate() != null) {
                $visaEndDate = date_format($user->getPerson()->getVisaEndDate(), 'Y-m-d');
                $visaEndDateUnits = explode("-", $visaEndDate);
                $veYear = $visaEndDateUnits[0];
                $veMonth = $visaEndDateUnits[1];
                $veDay = $visaEndDateUnits[2];
            } else {
                $veYear = null;
                $veMonth = null;
                $veDay = null;
            }
        } else {
            return $this->redirect($this->generateUrl('user_login'));
        }
        return array(
            'user' => $user,
            'teacher' => Person::TEACHER,
            'doctor' => Person::DOCTOR,
            'error' => $this->getRequest()->get('error'),
            'vsDay' => $vsDay,
            'vsMonth' => $vsMonth,
            'vsYear' => $vsYear,
            'veDay' => $veDay,
            'veMonth' => $veMonth,
            'veYear' => $veYear,
            'logoType' => \MD\Bundle\MediaBundle\Entity\Image::TYPE_LOGO,
            'passportType' => \MD\Bundle\MediaBundle\Entity\Image::TYPE_PASSPORT,
        );
    }

    /**
     * Create a new Account entity.
     * @Route("/edit/{id}", name="fe_usr_update")
     * @Method("POST")
     * @Template("UserBundle:FrontEnd/Usr:edit.html.twig")
     */
    public function updateAction($id) {
        $em = $this->getDoctrine()->getManager();
        $usr = $em->getRepository('UserBundle:Account')->find($id);

        if (!$usr) {
            throw $this->createNotFoundException('Unable to find this user ');
        }
        $usrPostData = $this->collectPOST();
        $usr->getPerson()->setEmail($usrPostData->email);
        $usr->getPerson()->setUsername($usrPostData->username);
        $usr->getPerson()->setFamilyname($usrPostData->familyname);
        $usr->getPerson()->setGender($this->getRequest()->get('gender'));
        $usr->getPerson()->setPhone($usrPostData->phone);
        $usr->getPerson()->setPassportNumber($usrPostData->passportNumber);
        $usr->getPerson()->setCitizenship($usrPostData->citizenship);
        $usr->getPerson()->setBirthPlace($usrPostData->birthPlace);
        $usr->getPerson()->setMetonymy($usrPostData->metonymy);
        $usr->getPerson()->setVisaRelasePlace($usrPostData->visaRelasePlace);
        if ($usrPostData->visaStartDate)
            $usr->getPerson()->setVisaStartDate($usrPostData->visaStartDate);
        if ($usrPostData->visaEndDate)
            $usr->getPerson()->setVisaEndDate($usrPostData->visaEndDate);
        $em->persist($usr->getPerson());
        $em->flush();

        if ($usr->getPerson()->getVisaStartDate() != null) {
            $visaStartDate = date_format($usr->getPerson()->getVisaStartDate(), 'Y-m-d');
            $visaStartDateUnits = explode("-", $visaStartDate);
            $vsYear = $visaStartDateUnits[0];
            $vsMonth = $visaStartDateUnits[1];
            $vsDay = $visaStartDateUnits[2];
        } else {
            $vsYear = null;
            $vsMonth = null;
            $vsDay = null;
        }
        if ($usr->getPerson()->getVisaEndDate() != null) {
            $visaEndDate = date_format($usr->getPerson()->getVisaEndDate(), 'Y-m-d');
            $visaEndDateUnits = explode("-", $visaEndDate);
            $veYear = $visaEndDateUnits[0];
            $veMonth = $visaEndDateUnits[1];
            $veDay = $visaEndDateUnits[2];
        } else {
            $veYear = null;
            $veMonth = null;
            $veDay = null;
        }
        return array(
            'user' => $usr,
            'teacher' => Person::TEACHER,
            'doctor' => Person::DOCTOR,
            'error' => $this->getRequest()->get('error'),
            'vsDay' => $vsDay,
            'vsMonth' => $vsMonth,
            'vsYear' => $vsYear,
            'veDay' => $veDay,
            'veMonth' => $veMonth,
            'veYear' => $veYear,
            'valid' => 1,
            'logoType' => \MD\Bundle\MediaBundle\Entity\Image::TYPE_LOGO,
        );
    }

    protected function collectPOST() {
        $request = $this->getRequest()->request;
        $user = new \stdClass();
        $user->username = $request->get('username');
        $user->familyname = $request->get('familyname');
        $user->password = $request->get('password');
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        $user->gender = $request->get('gender');
        $user->passportNumber = $request->get('passportNumber');
        $user->citizenship = $request->get('citizenship');
        $user->birthPlace = $request->get('birthPlace');
        $user->metonymy = $request->get('metonymy');
        $user->visaRelasePlace = $request->get('visaRPlace');
        $birthDateFormat = $this->mangeDateFormatAndValidate($request->get('bDay'), $request->get('bMonth'), $request->get('bYear'));
        if ($birthDateFormat)
            $user->birthdate = new \DateTime($birthDateFormat);
        else
            $user->birthdate = NULL;
        $visaStartDateFormat = $this->mangeDateFormatAndValidate($request->get('vsDay'), $request->get('vsMonth'), $request->get('vsYear'));
        if ($visaStartDateFormat)
            $user->visaStartDate = new \DateTime($visaStartDateFormat);
        else
            $user->visaStartDate = NULL;
        $visaEndDateFormat = $this->mangeDateFormatAndValidate($request->get('veDay'), $request->get('veMonth'), $request->get('veYear'));
        if ($visaEndDateFormat)
            $user->visaEndDate = new \DateTime($visaEndDateFormat);
        else
            $user->visaEndDate = NULL;
        return $user;
    }

    /**
     *  manage date to insert
     * @param type $day
     * @param type $month
     * @param type $year
     * @return boolean
     */
    public function mangeDateFormatAndValidate($day, $month, $year) {
        if ($day != NULL && $month != NULL && $year != NULL)
            return $birthDateFormat = $day . "-" . $month . "-" . $year;
        else
            return FALSE;
    }

    /**
     * @Route("/email/verify/{code}", name="fe_email_verify")
     */
    public function emailVerifyAction($code) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:Account")->getSelectedAccount($code, Account::BLOCKED);
        if ($user) {
            $user->setState(Account::VERIFIED);
            $em->persist($user);
            $em->flush();
            return $this->doLogin($user);
        } else {
            exit("You are blocked ");
        }
    }

    /**
     * Creates a new person image.
     *
     * @Route("/gallery/{id}" , name="person_create_images")
     * @Method("POST")
     * @Template("UserBundle:Person:GetImages.html.twig")
     */
    public function SetImagesAction(Request $request, $id) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $form->bind($request);

        $data = $form->getData();
        $files = $data["files"];

        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('UserBundle:Person')->find($id);
        if (!$person) {
            throw $this->createNotFoundException('Unable to find package entity.');
        }
        $imageType = $request->get("type");
        foreach ($files as $file) {
            if ($file != NULL) {
                $image = new Image();
                $em->persist($image);
                $em->flush();
                $image->setFile($file);
                $logoImages = $person->getImages(array(\MD\Bundle\MediaBundle\Entity\Image::TYPE_LOGO));
                $passportImages = $person->getImages(array(\MD\Bundle\MediaBundle\Entity\Image::TYPE_PASSPORT));
                if ($imageType == Image::TYPE_LOGO && count($logoImages) > 0) {
                    foreach ($logoImages As $logo) {
                        $this->deleteOldImage($logo, $person, $image);
                        $image->setImageType(Image::TYPE_LOGO);
                    }
                } else if ($imageType == \MD\Bundle\MediaBundle\Entity\Image::TYPE_LOGO && count($logoImages) == 0) {
                    $image->setImageType(Image::TYPE_LOGO);
                } else if ($imageType == Image::TYPE_PASSPORT && count($passportImages) > 0) {
                    foreach ($passportImages As $logo) {
                        $this->deleteOldImage($logo, $person, $image);
                        $image->setImageType(Image::TYPE_PASSPORT);
                    }
                } else if ($imageType == \MD\Bundle\MediaBundle\Entity\Image::TYPE_PASSPORT && count($passportImages) == 0) {
                    $image->setImageType(Image::TYPE_PASSPORT);
                } else {
                    $image->setImageType(Image::TYPE_GALLERY);
                }
                $image->preUpload();
                $image->upload("persons/" . $id);
                if (!$image->getImageType() == Image::TYPE_PASSPORT) {
                    $oPath = $image->getUploadDirForResize("persons/" . $id) . "/" . $image->getId();
                    $resize_1 = $image->getUploadDirForResize("persons/" . $id) . "/" . $image->getId();
                    SimpleImage::saveNewResizedImage($oPath, $resize_1, 140, 140);
                }
                $person->addImage($image);
                $imageUrl = "/uploads/persons/" . $id . "/" . $image->getId();
                $imageId = $image->getId();
            }

            $em->persist($person);
            $em->flush();
        }
        $files = '{"files":[{"url":"' . $imageUrl . '","thumbnailUrl":"http://lh6.ggpht.com/0GmazPJ8DqFO09TGp-OVK_LUKtQh0BQnTFXNdqN-5bCeVSULfEkCAifm6p9V_FXyYHgmQvkJoeONZmuxkTBqZANbc94xp-Av=s80","name":"test","id":"' . $imageId . '","type":"' . $image->getExtension() . '","size":620888,"deleteUrl":"http://localhost/packagat/web/uploads/packages/1/th71?delete=true","deleteType":"DELETE"}]}';
        $response = new Response();
        $response->setContent($files);
        $response->setStatusCode(200);
        return $response;
    }

    public function deleteOldImage($logo, $person, $image) {
        $em = $this->getDoctrine()->getManager();
        $person->removeImage($logo);
        $em->persist($person);
        $em->flush();
        $image->storeFilenameForRemove("persons/" . $person->getId());
        $image->removeUpload();
        $image->storeFilenameForResizeRemove("persons/" . $person->getId());
        $image->removeResizeUpload();
        $em->persist($logo);
        $em->flush();
        $em->remove($logo);
        $em->flush();
        return true;
    }

    /**
     * Deletes a AttractionGallery entity.
     *
     * @Route("/deleteimage/{h_id}/{redirect_id}", name="personimages_delete")
     * @Method("POST")
     */
    public function deleteImageAction($h_id, $redirect_id) {
        $image_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:Person')->find($h_id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find person entity.');
        }
        $image = $em->getRepository('MediaBundle:Image')->find($image_id);
        if (!$image) {
            throw $this->createNotFoundException('Unable to find image entity.');
        }
        $entity->removeImage($image);
        $em->persist($entity);
        $em->flush();
        $image->storeFilenameForRemove("persons/" . $h_id);
        $image->removeUpload();
        $image->storeFilenameForResizeRemove("persons/" . $h_id);
        $image->removeResizeUpload();
        $em->persist($image);
        $em->flush();
        $em->remove($image);
        $em->flush();
        return $this->redirect($this->generateUrl('fe_usr_edit', array('id' => $this->getUser()->getId())));
    }

}
