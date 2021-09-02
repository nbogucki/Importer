<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Products;
use App\Form\ProductsType;
use App\Service\FileUploader;

class ImporterController extends AbstractController
{
     /**
     * @Route("/", name="importer")
     */
    public function index(Request $request, FileUploader $fileUploader)
    {

        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('products')->getData();
            if ($csvFile) {
                $csvFileName = $fileUploader->upload($csvFile);
            }

            return $this->redirectToRoute('importer');
        }

        return $this->render('/importer/index.html.twig', [
            'controller_name' => 'ImporterController',
            'form' => $form->createView(),
        ]);
    }
}

?>