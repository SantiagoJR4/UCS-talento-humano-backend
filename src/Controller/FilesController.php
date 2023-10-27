<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use setasign\Fpdi\Fpdi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

function flattenArray($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $prefix . $key . '-'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    $filteredArray = array_filter($result, function($value) {
        return $value !== null;
    });
    return $filteredArray;
}

class FilesController extends AbstractController
{
    #[Route('/files/merge-pdfs', name: 'app_files_merge_pdfs')]
    public function mergePdfs(ManagerRegistry $doctrine , Request $request): JsonResponse
    {
        // $userId = $request->query->get('userId');
        $userId = 12;
        $user = $doctrine->getRepository(User::class)->find($userId);
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select(
            'pd.identificationPdf', 'pd.epsPdf', 'pd.pensionPdf',
            'pd.bankAccountPdf', 'pd.rutPdf', 'pd.severanceFundPdf'
        )
        ->from('App\Entity\PersonalData', 'pd')
        ->where('pd.user = :user')
        ->setParameter('user', $user);
        $personalDataResults = $query->getQuery()->getArrayResult();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select(
            'at.degreePdf', 'at.diplomaPdf', 'at.certifiedTitlePdf'
        )
        ->from('App\Entity\AcademicTraining', 'at')
        ->where('at.user = :user')
        ->setParameter('user', $user);
        $academicTrainingResults = $query->getQuery()->getArrayResult();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('ft.certifiedPdf')
        ->from('App\Entity\FurtherTraining', 'ft')
        ->where('ft.user = :user')
        ->setParameter('user', $user);
        $furtherTrainingResults = $query->getQuery()->getArrayResult();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('lg.certifiedPdf')
        ->from('App\Entity\Language', 'lg')
        ->where('lg.user = :user')
        ->setParameter('user', $user);
        $languageResults = $query->getQuery()->getArrayResult();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('we.certifiedPdf')
        ->from('App\Entity\WorkExperience', 'we')
        ->where('we.user = :user')
        ->setParameter('user', $user);
        $workExperienceResults = $query->getQuery()->getArrayResult();
        $query = $doctrine->getManager()->createQueryBuilder();
        $query->select('te.certifiedPdf')
        ->from('App\Entity\TeachingExperience', 'te')
        ->where('te.user = :user')
        ->setParameter('user', $user);
        $teachingExperienceResults = $query->getQuery()->getArrayResult();

        $results = [
            "PersonalData" => $personalDataResults,
            "AcademicTraining" => $academicTrainingResults,
            "furtherTraining" => $furtherTrainingResults,
            "language" => $languageResults,
            "workExperience" => $workExperienceResults,
            "teachingExperience" => $teachingExperienceResults,
        ];
        
        $flattenAndFilterArray = flattenArray($results);
        array_walk_recursive($flattenAndFilterArray, function (&$value) {
            if ($value) {
                $value = $this->getParameter('hv') . '/' . $value;
            }
        });
        // return new JsonResponse($flattenAndFilterArray);
        $pdf = new Fpdi();
        foreach ($flattenAndFilterArray as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for($i=0; $i < $pageCount; $i++) {
                $pdf->addPage();
                $tplId = $pdf->importPage($i+1);
                $pdf->useTemplate($tplId);
            }
        }
        $pdfContent = $pdf->Output('', 'S');

        // Create a Symfony Response
        $response = new Response($pdfContent);

        // Set the appropriate headers to make it downloadable
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="merged.pdf"'); // You can set the desired filename

        return $response;
    }

    #[Route('/files/create-pdfs', name: 'app_files_create_pdfs')]
    public function createPdf(): Response
    {
        // Create a new FPDI instance
        $pdf = new Fpdi();

        // Add a page to the PDF
        $pdf->AddPage();

        // Set font and size
        $pdf->SetFont('Arial', 'B', 32);

        // Add the "Hello World!" text
        $pdf->Cell(40, 10, 'Formación Academica');

        // Output the PDF to the browser
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $pdfOutput = $pdf->Output('', 'S');
        $response->setContent($pdfOutput);

        return $response;
    }
}
