<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ImageAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $image = $this->getSubject();

        // You can then do things with the $image, like show a thumbnail in the help:
        $fileFieldOptions = array('required' => false);

        if ($image) {// && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '<img style="width:200px;height:200px;" src="/images/'.$image->getFilename().'" class="admin-preview" />';
        }

        $formMapper
            //->add('filename')
            ->add('file', 'file', $fileFieldOptions)

            // ...
        ;
    }

    public function prePersist($image)
    {
        $this->manageFileUpload($image);
    }

    public function preUpdate($image)
    {
        $this->manageFileUpload($image);
    }

    private function manageFileUpload($image)
    {
        if ($image->getFile()) {
            $image->refreshUpdated();
        }
    }

    // ...
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('filename')
            ->add('updated')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }
}
