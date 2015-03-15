<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersHM
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class UsersHM
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="PJM\UserBundle\Entity\User")
     **/
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add users
     *
     * @param \PJM\UserBundle\Entity\User $users
     * @return UsersHM
     */
    public function addUser(\PJM\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \PJM\UserBundle\Entity\User $users
     */
    public function removeUser(\PJM\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
