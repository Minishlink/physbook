<?php

// src/PJM/AppBundle/Entity/Commentaires/Vote.php


namespace PJM\AppBundle\Entity\Commentaires;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Vote as BaseVote;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Vote extends BaseVote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Comment of this vote.
     *
     * @var Comment
     * @ORM\ManyToOne(targetEntity="PJM\AppBundle\Entity\Commentaires\Comment")
     */
    protected $comment;
}
