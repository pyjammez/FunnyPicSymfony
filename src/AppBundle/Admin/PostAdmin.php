<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PostAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('gallery')
            ->add('slug')
            ->add('description')
            ->add('images', 'sonata_type_collection', [
                'required' => false,
                'by_reference' => false,
            ], [
                'edit' => 'inline',
                //'inline' => 'table' this doesn't show image preview
            ])
            ->add('views')
            ->add('likes')
            ->add('dislikes');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('slug');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug');
    }

    public function prePersist($page)
    {
        $this->manageEmbeddedImageAdmins($page);
    }

    public function preUpdate($page)
    {

        // Set the assoc parent ids because sonata doesn't automatically
        if (method_exists($page, 'getImages')) {
            foreach ($page->getImages() as $image) {
                $image->setPost($page);
            }
        }

        $this->manageEmbeddedImageAdmins($page);
    }

    private function manageEmbeddedImageAdmins($page)
    {
        // Cycle through each field
        //exit(dump($page));
        foreach ($this->getFormFieldDescriptions() as $fieldName => $fieldDescription) {
            // detect embedded Admins that manage Images
            if ($fieldDescription->getType() === 'sonata_type_collection' &&
                ($associationMapping = $fieldDescription->getAssociationMapping()) &&
                $associationMapping['targetEntity'] === 'AppBundle\Entity\Image'
            ) {
                $getter = 'get'.$fieldName;
                $setter = 'set'.$fieldName;

                /** @var Image $image */
                $images = $page->$getter();

                if ($images) {
                    foreach ($images as $image) {
                        if ($image->getFile()) {
                            // update the Image to trigger file management
                            //$image->setPost($page);
                            $image->refreshUpdated();
                        } elseif (!$image->getFile() && !$image->getFilename()) {
                            // prevent Sf/Sonata trying to create and persist an empty Image
                            $page->$setter(null);
                        }
                    }
                }
            }
        }
    }
}
