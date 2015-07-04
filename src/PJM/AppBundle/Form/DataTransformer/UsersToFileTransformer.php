<?php

namespace PJM\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use PJM\AppBundle\Services\UserManager;
use PJM\AppBundle\Services\FileManager;
use PJM\AppBundle\Services\Excel;

class UsersToFileTransformer implements DataTransformerInterface
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Excel
     */
    private $excel;

    /**
     * @param UserManager $userManager
     * @param FileManager $fileManager
     * @param Excel $excel
     */
    public function __construct(UserManager $userManager, FileManager $fileManager, Excel $excel)
    {
        $this->userManager = $userManager;
        $this->fileManager = $fileManager;
        $this->excel = $excel;
    }

    /**
     * Transforms an array of users (users) to a UploadedFile (file). (not used)
     *
     * @param  array|null $users
     * @return UploadedFile
     */
    public function transform($users)
    {
        return null;
    }

    /**
     * Transforms a UploadedFile (file) to an array of users (users).
     *
     * @param  UploadedFile $file
     * @return array|null
     */
    public function reverseTransform($file)
    {
        if (empty($file)) {
            return null;
        }

        // on upload temporairement le fichier
        $filePath = $this->fileManager->upload($file, 'registrationUsers', false, 'excel');

        // on va chercher le tableau correspondant à l'Excel
        $usersExcel = $this->excel->parse($filePath);

        // on supprime le fichier temporaire
        $this->fileManager->remove($filePath);

        foreach ($usersExcel as $k => $row) {
            if (count($row) >= 8) {
                // si il y a au moins le nombre de paramètres requis
                $user = $this->userManager->createUser();

                $user->setFams($row['A']);
                $user->setTabagns(strtolower($row['B']));
                $user->setProms($row['C']);
                $user->setEmail(strtolower($row['D']));
                $user->setBucque($row['E']);
                $user->setPlainPassword($row['F']);
                $user->setPrenom($row['G']);
                $user->setNom($row['H']);

                if (!empty($row['I'])) {
                    $user->setGenre($row['I'] == 'F');
                }

                if (!empty($row['J'])) {
                    $tel = (strlen($row['J']) == 9) ? '0'.$row['J'] : $row['J'];
                    $user->setTelephone($tel);
                }

                if (!empty($row['K'])) {
                    $user->setAppartement(strtoupper($row['K']));
                }

                if (!empty($row['L'])) {
                    $user->setClasse(strtoupper($row['L']));
                }

                if (!empty($row['M'])) {
                    $user->setAnniversaire(\DateTime::createFromFormat('m-d-y', $row['M']));
                }

                $user->setEnabled(true);

                $this->userManager->configure($user);

                $users[] = $user;
            } else {
                throw new TransformationFailedException(sprintf(
                    'La ligne numéro %s ne possède pas assez de colonnes. Il en faut 8 et elle en a %s.',
                    $k,
                    count($row)
                ));
            }
        }

        return $users;
    }
}
