<?php

namespace MD\Bundle\UserBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * MD\Bundle\UserBundle\Entity\Account
 *
 * @ORM\Table("account")
 * @ORM\Entity(repositoryClass="MD\Bundle\UserBundle\Repository\AccountRepository")
 */
class Account implements UserInterface, AdvancedUserInterface, \Serializable {

    //gender

    const MALE = 1;
    const FEMALE = 2;
    // user states
    const EXIST = 1;
    const DELETED = -1;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255 ,unique = true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @ORM\Column(type="string" ,nullable=true , length=255)
     */
    private $password;

    /**
     * @ORM\Column(name="state", type="integer" , length = 4 )
     */
    private $state;

    /**
     * @ORM\ManyToMany(targetEntity="Role", mappedBy="accounts")
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="accounts",cascade={"persist"})
     */
    protected $person;

    public function __construct() {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->salt = md5(uniqid(null, true));
    }

    public function __toString() {

        return $this->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->roles as $r)
            $roles[] = $r->getName();
        return $roles;
        //  return array('ROLE_ADMIN');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() {

    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize() {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized) {
        list (
                $this->id,
                ) = unserialize($serialized);
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isAccountNonExpired() {
        return true;
    }

    public function isEnabled() {
        return $this->isActive();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function isActive() {
        return $this->state;
    }

    public function setActive($isActive) {
        $this->state = $isActive;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setSalt($salt) {
        $this->salt = $salt;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    public function getStringId() {
        return (string) $this->id;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return Account
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Add roles
     *
     * @param \MD\Bundle\UserBundle\Entity\Role $roles
     * @return Account
     */
    public function addRole(\MD\Bundle\UserBundle\Entity\Role $roles) {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \MD\Bundle\UserBundle\Entity\Role $roles
     */
    public function removeRole(\MD\Bundle\UserBundle\Entity\Role $roles) {
        $this->roles->removeElement($roles);
    }

    /**
     * Set person
     *
     * @param \MD\Bundle\UserBundle\Entity\Person $person
     * @return Account
     */
    public function setPerson(\MD\Bundle\UserBundle\Entity\Person $person = null) {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return \MD\Bundle\UserBundle\Entity\Person
     */
    public function getPerson() {
        return $this->person;
    }

}
