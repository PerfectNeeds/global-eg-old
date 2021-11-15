<?php

namespace MD\Bundle\CMSBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Extension;
use Symfony\Component\HttpFoundation\Session\Session;

class VarsExtension extends Twig_Extension {

    private $container;
    private $em;
    private $conn;

    public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container) {
        $this->em = $em;
        $this->conn = $em->getConnection();
        $this->container = $container;
    }

    public function getName() {
        return 'some.extension';
    }

    public function getFilters() {
        return array(
            'locale' => new \Twig_Filter_Method($this, 'locale'),
            'currencyWithFormat' => new \Twig_Filter_Method($this, 'currencyWithFormat'),
            'init' => new \Twig_Filter_Method($this, 'init'),
            new \Twig_SimpleFilter('localizeddate', array($this, 'twigLocalizedDateFilter'), array('needs_environment' => true)),
        );
    }

    public function locale($locale) {
        if (empty($locale)) {
            $locale = 'en';
        }
        $session = new Session();
        $session->set('_locale', $locale);
    }

    public function currencyWithFormat($price) {
        return \MD\Utils\Number::currencyWithFormat($price);
    }

    public function init() {
        $session = new Session();
        if (!$session->has('init')) {
//            $ipLocation = \MD\Utils\IPService::getIPLocation();
            $ipLocation = \MD\Utils\IPService::getIPLocation('197.38.125.164');
            $session->set('userLocation', $ipLocation->country);
            $zone = $this->em->getRepository('ECommerceBundle:Zone')->findOneByCountryCode($ipLocation->country);

            if (!$zone) {
                $zone = $this->em->getRepository('ECommerceBundle:Zone')->findOneByCountryCode('OTH');
            }
            $session->set('userLocation', $ipLocation->country);
            $session->set('userZone', $zone);

            if (!$session->has('currency')) {
                $currency = $this->em->getRepository('ECommerceBundle:Currency')->find(1);
                $session->set('currency', $currency);
            }
            $session->set('init', 1);
        }
    }

}
