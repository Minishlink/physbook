<?php

namespace PJM\AppBundle\Services;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use PJM\AppBundle\Entity\Boquette;
use PJM\AppBundle\Entity\Historique;
use PJM\AppBundle\Entity\Compte;
use PJM\UserBundle\Entity\User;

/**
 * Ce fichier rassemble l'ensemble des fonctions relatives à la connexion et au dialogue
 * avec la base de données de gestion des comptes du R&z@l à Bordel's
 *
 * Versions :
 * - 0.1 par Sarkal 122 pour les zi R&z@l 213
 * - 1.0 par Afo²'1 40bo213 pour Phy'sbook (cleaning, fix, utilisation de PDO et jointures de tables)
 */

class Rezal
{
    protected $em;
    protected $dbh;
    private $db_host;
    private $db_user;
    private $db_pass;

    public function __construct(EntityManager $em, $db_host, $db_user, $db_pass)
    {
        $this->em = $em;
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
    }

    /**
     * Permet la connexion à la base de donnée
     */
    public function connexion()
    {
        if(isset($this->dbh)) {
            return true;
        }

        try {
            $this->dbh = new \PDO(
                'mysql:host='.$this->db_host.';dbname=rezal213',
                $this->db_user,
                $this->db_pass,
                array(
                    \PDO::ATTR_TIMEOUT => 15,
                )
            );
        } catch (\PDOException $e) {
            return $e;
        }

        return true;
    }

    /**
     * Permet la déconnexion du lien Mysql
     */
    public function deconnexion($sth = null)
    {
        if(isset($sth)) {
            $sth = null;
        }

        $this->dbh = null;
    }

    /**
     * Va chercher l'id de l'utilisateur
     * @param array  $user Tableau ayant comme clés fams, tabagns et proms
     */
    public function getUser($user)
    {
        // connexion à la BDD
        $e = $this->connexion();
        if($e === true) {
            // requête à la BDD
            $sth = $this->dbh->prepare(
                'SELECT p.idPg
                FROM pg AS p
                WHERE p.fams = :fams
                AND p.tbk = :tbk
                AND p.proms = :proms'
            );
            $sth->bindParam(':fams', $user['fams'], \PDO::PARAM_STR);
            $sth->bindParam(':tbk', $user['tabagns'], \PDO::PARAM_STR, 3);
            $sth->bindParam(':proms', $user['proms'], \PDO::PARAM_STR, 3);
            $sth->execute();
            $res = $sth->fetch();

            $this->deconnexion($sth);

            if (isset($res["idPg"])) {
                return array(
                    'success' => true,
                    'pg' => $res["idPg"]
                );
            }

            return array(
                'success' => false,
                'error' => 'REZAL_GETUSER_NOT_FOUND ('.$user['fams'].$user['tabagns'].$user['proms'].')'
            );
        }

        $this->deconnexion();
        return array(
            'success' => false,
            'error' => 'REZAL_GETUSER_MYSQL_'.$e->getCode()
        );
    }

    /**
     * Lit le solde d'un PG
     * @param array  $user Tableau ayant comme clés fams, tabagns et proms
     * @return $solde solde du pg ou null si échec
     *
     */
    public function getSolde($user)
    {
        // connexion à la BDD
        $e = $this->connexion();
        if($e === true) {
            // requête à la BDD
            $sth = $this->dbh->prepare(
                'SELECT c.montant
                FROM compte AS c
                INNER JOIN pg AS p ON p.compte = c.idCompte
                WHERE p.fams = :fams
                AND p.tbk = :tbk
                AND p.proms = :proms'
            );
            $sth->bindParam(':fams', $user['fams'], \PDO::PARAM_STR);
            $sth->bindParam(':tbk', $user['tabagns'], \PDO::PARAM_STR, 3);
            $sth->bindParam(':proms', $user['proms'], \PDO::PARAM_STR, 3);
            $sth->execute();
            $res = $sth->fetch();

            $this->deconnexion($sth);
            return $res["montant"];
        }

        $this->deconnexion();
        return null;
    }

    /**
     * Edite le montant du solde d’un pg
     * @param array  $user Tableau ayant comme clés fams, tabagns et proms
     * @param $montant montant à ajouter ou soustraire
     * @param $add si addition
     * @param $date date de l'opération
     * @return true si l'opération s'est bien déroulée, false sinon
     */
    public function editSolde($user, $montant, $date, $add = true) {
        if ($montant >= 0) {
            // on va chercher l'utilisateur
            $resIdPg = $this->getUser($user);

            if ($resIdPg['success'] === true) {
                $idPg = $resIdPg['pg'];
                // connexion à la BDD
                $e = $this->connexion();
                if($e === true) {
                    // TODO PDO beginTransaction etc... là il détecte pas s'il y a une erreur à la deuxième requête
                    // requête à la BDD pour modifier le compte
                    $sqlCompte = 'UPDATE compte AS c
                        INNER JOIN pg AS p ON p.compte = c.idCompte
                        SET c.montant = c.montant '.($add ? '+' : '-').' :montant,
                        c.estMazoute = IF(c.montant '.($add ? '+' : '-').' :montant >= 0, 0, 1)
                        WHERE p.idPg = :idPg;';

                    // requête à la BDD pour modifier l'historique ?
                    /*$sqlHist = 'INSERT INTO historique
                        SET
                        idEmetteur = :idPg,
                        estEmetteurBoquette = false,
                        idReceveur = 0,
                        estReceveurBoquette = true,
                        montant = :montant,
                        type = 1,
                        date = :date;';
                    $sth = $this->dbh->prepare(
                        $sqlCompte.$sqlHist
                    );*/

                    $sth = $this->dbh->prepare($sqlCompte);

                    $sth->bindParam(':idPg', $idPg, \PDO::PARAM_INT);
                    $sth->bindParam(':montant', $montant, \PDO::PARAM_INT);
                    //$sth->bindParam(':date', $date, \PDO::PARAM_STR);

                    if($sth->execute()) {
                        $this->deconnexion();
                        return true;
                    }

                    $this->deconnexion();
                    return 'REZAL_UPDATE_COMPTE_HIST';
                } else {
                    return 'REZAL_MYSQL_'.$e->getCode();
                }
            }

            $this->deconnexion();
            return $resIdPg['error'];
        }

        $this->deconnexion();
        return false;
    }

    /**
     * Ajoute un montant au solde d’un pg
     * @param array  $user Tableau ayant comme clés fams, tabagns et proms
     * @param $montant montant à ajouter
     * @param $date date de l'opération
     * @return true si l'opération s'est bien déroulée, false sinon
     */
    public function crediteSolde($user, $montant, $date)
    {
        return $this->editSolde($user, $montant, $date, true);
    }

    /**
     * Soustrait un montant au solde d’un pg
     * @param array  $user Tableau ayant comme clés fams, tabagns et proms
     * @param $montant montant à soustraire
     * @param $date date de l'opération
     * @return true si l'opération s'est bien déroulée, false sinon
     */
    public function debiteSolde($user, $montant, $date)
    {
        return $this->editSolde($user, $montant, $date, false);
    }

    private function convertBoquetteSlug($boquetteSlug)
    {
        if ($boquetteSlug == "pians") {
            return 0;
        } else if ($boquetteSlug == "cvis") {
            return 1;
        }

        return null;
    }

    /**
     * Donne la liste des consos
     * @param string  $boquetteSlug Boquette = (pians|cvis)
     * @param array   $existants    Tableau d'id d'item à exclure ou garder selon le paramètre garder
     * @param boolean [$garder      = true] Exclure ou garder $existants
     */
    public function listeConsos($boquetteSlug, $existants, $garder = true)
    {
        $boquette = $this->convertBoquetteSlug($boquetteSlug);
        if (isset($boquette)) {
            // connexion à la BDD
            $e = $this->connexion();
            if($e === true) {
                $sql = "SELECT *
                    FROM objet AS o
                    INNER JOIN categorie AS c ON c.idC = o.categorie
                    WHERE c.boquette = :boquette
                ";

                if ($existants != "" || $garder) {
                    $sql.= "
                        AND o.idObjet ". ((!$garder) ? 'NOT ' : '') ."IN(".$existants.")
                    ";
                }

                $sth = $this->dbh->prepare($sql);
                $sth->bindParam(':boquette', $boquette, \PDO::PARAM_INT);

                if($sth->execute()) {
                    $consos = $sth->fetchAll();
                    $this->deconnexion();
                    return $consos;
                }

                $this->deconnexion();
                return null;
            }

            $this->deconnexion();
            return $e;
        }

        return null;
    }

    /**
     * Donne les historiques d'une boquette
     * @param string    $boquetteSlug   Boquette = (pians|cvis)
     * @param \DateTime Ne garder que les historiques après cette date
     */
    public function listeHistoriques($boquetteSlug, $date = null)
    {
        $boquette = $this->convertBoquetteSlug($boquetteSlug);
        if (isset($boquette)) {
            // connexion à la BDD
            $e = $this->connexion();
            if($e === true) {
                $sql = "SELECT h.date, h.objet, h.montant, h.qte, p.fams, p.proms, p.tbk, c.boquette
                    FROM historique AS h
                    INNER JOIN pg AS p ON p.idPg = h.idEmetteur
                    INNER JOIN objet AS o ON o.idObjet = h.objet
                    INNER JOIN categorie AS c ON c.idC = o.categorie
                    WHERE c.boquette = :boquette
                    AND h.estEmetteurBoquette = false
                    AND h.estReceveurBoquette = true
                ";

                if ($date === null) {
                    $sql .= "
                        AND p.proms >= 211
                    ";
                } else {
                    $sql .= "
                        AND h.date > :date
                    ";
                }


                $sth = $this->dbh->prepare($sql);
                $sth->bindParam(':boquette', $boquette, \PDO::PARAM_INT);

                if ($date !== null) {
                    $sth->bindParam(':date', $date, \PDO::PARAM_STR);
                }

                if($sth->execute()) {
                    $historiques = $sth->fetchAll();
                    $this->deconnexion();
                    return $historiques;
                }

                return null;
            }

            $this->deconnexion();
            return $e;
        }

        return null;
    }

    /**
     * Donne les comptes Pi
     */
    public function listeComptes($exclureFams = "")
    {
        // connexion à la BDD
        $e = $this->connexion();
        if($e === true) {
            $sql = "SELECT c.montant, p.fams, p.proms, p.tbk
                FROM compte AS c
                INNER JOIN pg AS p ON p.compte = c.idCompte
                WHERE p.proms >= 211
            ";

            if ($exclureFams != "") {
                $sql.= "
                    AND p.fams NOT IN(".$exclureFams.")
                ";
            }

            $sth = $this->dbh->prepare($sql);

            if($sth->execute()) {
                $comptes = $sth->fetchAll();
                $this->deconnexion();
                return $comptes;
            }

            return null;
        }

        $this->deconnexion();
        return $e;
    }
}
