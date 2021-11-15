<?php

namespace MD\Bundle\CMSBundle\Controller\FrontEnd;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Utils\Validate;
use Symfony\Component\HttpFoundation\Session\Session;
use MD\Bundle\CMSBundle\Form\CaptchaType;
use Symfony\Component\HttpFoundation\Response;

/**
 * contactus controller.
 *
 * @Route("contactus")
 */
class ContactusController extends Controller {

    /**
     * Contactus form.
     *
     * @Route("/", name="fe_contact")
     * @Method("GET")
     * @Template()
     */
    public function contactAction() {
        $em = $this->getDoctrine()->getManager();
        $contacts = $em->getRepository('CMSBundle:DynamicPage')->find(8);

        return array(
            'contacts' => $contacts,
        );
    }

    /**
     * Lists all Package entities.
     *
     * @Route("/thanks", name="fe_contact_submit")
     * @Method("POST")
     * @Template()
     */
    public function thankAction() {


        $name = $this->getRequest()->get('name');
        $email = $this->getRequest()->get('email');
//        $mobile = $this->getRequest()->get('mobile');
        $subject = $this->getRequest()->get('subject');
//        $company = $this->getRequest()->get('company');
//        $country = $this->getRequest()->get('country');
        $msg = $this->getRequest()->get('message');

        $return = TRUE;
        $error = array();

   		$reCaptcha = new \MD\Utils\ReCaptcha();
        $reCaptchaValidate = $reCaptcha->verifyResponse();
        
        if ($reCaptchaValidate->success == False) {
			array_push($error, 'Invalid Captcha');
            $return = FALSE;
        }
        if (!Validate::not_null($name)) {
            array_push($error, 'Name');
            $return = FALSE;
        }
        if (!Validate::not_null($subject)) {
            array_push($error, 'Subject');
            $return = FALSE;
        }
        if (!Validate::not_null($email)) {
            array_push($error, 'Email');
            $return = FALSE;
        }
        if (Validate::not_null($email) AND ! Validate::email($email)) {
            array_push($error, 'Valid Email');
            $return = FALSE;
        }
//        if (!Validate::not_null($mobile)) {
//            array_push($error, 'Phone Number');
//            $return = FALSE;
//        }
//        if (!Validate::not_null($country)) {
//            array_push($error, 'Country');
//            $return = FALSE;
//        }
        if (!Validate::not_null($msg)) {
            array_push($error, 'Message');
            $return = FALSE;
        }

        if (count($error) > 0) {
            $return = 'You must enter ';
            for ($i = 0; $i < count($error); $i++) {
                if (count($error) == $i + 1) {
                    $return .= $error[$i];
                } else {
                    if (count($error) == $i + 2) {
                        $return .= $error[$i] . ' and ';
                    } else {
                        $return .= $error[$i] . ', ';
                    }
                }
            }
            $session = new Session();
            $session->getFlashBag()->add('error', $return);

            return $this->redirect($this->generateUrl('fe_contact'));
        }



        // to user
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: Global Engineering & Supply Team <supply@global-eg.com>' . "\r\n";

        $message = $this->renderView(
                'CMSBundle:FrontEnd/Contactus:thankEmail.html.twig', array(
            'name' => $name
                )
        );
        mail($email, 'Thank You', $message, $headers);


        // Admin
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: ' . $name . ' <' . $email . '>' . "\r\n";

        $message = $this->renderView(
                'CMSBundle:FrontEnd/Contactus:adminEmail.html.twig', array(
            'name' => $name,
            'subject' => $subject,
            'email' => $email,
            'message' => $msg,
                )
        );
        mail('supply@global-eg.com', 'Message to Global from ' . $name, $message, $headers);


        return array(
            'name' => $name,
            'email' => $email
        );
    }

}
