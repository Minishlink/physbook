<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersHM.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class UsersHM
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="PJM\AppBundle\Entity\User")
     * @ORM\OrderBy({"bucque" = "ASC"})
     **/
    private $users;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add users.
     *
     * @param \PJM\AppBundle\Entity\User $users
     *
     * @return UsersHM
     */
    public function addUser(\PJM\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users.
     *
     * @param \PJM\AppBundle\Entity\User $users
     */
    public function removeUser(\PJM\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
