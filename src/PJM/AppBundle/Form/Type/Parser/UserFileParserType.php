<?php

namespace PJM\AppBundle\Form\Type\Parser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PJM\AppBundle\Form\DataTransformer\UsersToFileTransformer;
use PJM\AppBundle\Services\UserManager;
use PJM\AppBundle\Services\FileManager;
use PJM\AppBundle\Services\Excel;

class UserFileParserType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new UsersToFileTransformer($this->userManager, $this->fileManager, $this->excel);
        $builder->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => "Le fichier n'est pas valide. Il manque peut-être des colonnes.",
        ));
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'userFileParser';
    }
}
