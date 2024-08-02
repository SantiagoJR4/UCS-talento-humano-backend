<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Ordinary9843\Ghostscript;
use setasign\Fpdi\Fpdi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        $userId = $request->query->get('userId');
        $user = $doctrine->getRepository(User::class)->find($userId);

        $entities = [
            'PersonalData' => [
                'entity' => 'App\Entity\PersonalData',
                'alias' => 'pd',
                'fields' => [
                    'identificationPdf', 'epsPdf', 'pensionPdf',
                    'bankAccountPdf', 'rutPdf', 'severanceFundPdf'
                ],
            ],
            'AcademicTraining' => [
                'entity' => 'App\Entity\AcademicTraining',
                'alias' => 'at',
                'fields' => [
                    'degreePdf', 'diplomaPdf', 'certifiedTitlePdf'
                ],
            ],
            'FurtherTraining' => [
                'entity' => 'App\Entity\FurtherTraining',
                'alias' => 'ft',
                'fields' => ['certifiedPdf'],
            ],
            'Language' => [
                'entity' => 'App\Entity\Language',
                'alias' => 'lg',
                'fields' => ['certifiedPdf'],
            ],
            'WorkExperience' => [
                'entity' => 'App\Entity\WorkExperience',
                'alias' => 'we',
                'fields' => ['certifiedPdf'],
            ],
            'TeachingExperience' => [
                'entity' => 'App\Entity\TeachingExperience',
                'alias' => 'te',
                'fields' => ['certifiedPdf'],
            ],
            'Record' => [
                'entity' => 'App\Entity\Record',
                'alias' => 'r',
                'fields' => [
                    'taxRecordPdf', 'judicialRecordPdf', 'disciplinaryRecordPdf', 'correctiveMeasuresPdf'
                ],
            ],
        ];

        $results = [];

        foreach ($entities as $key => $entityData) {
            $query = $doctrine->getManager()->createQueryBuilder();
            $query->select(implode(', ', array_map(fn($field) => "{$entityData['alias']}.$field", $entityData['fields'])))
                ->from($entityData['entity'], $entityData['alias'])
                ->where("{$entityData['alias']}.user = :user")
                ->setParameter('user', $user);
            
            $results[$key] = $query->getQuery()->getArrayResult();
        }
        
        $flattenAndFilterArray = flattenArray($results);
        array_walk_recursive($flattenAndFilterArray, function (&$value) {
            if ($value) {
                $value = $this->getParameter('hv') . '/' . $value;
            }
        });
        $binPath = $_ENV['GS_BIN_PATH'];
        if(!file_exists($binPath)){
            throw new FileException('Ghostscript not found');
        }
        $tmpPath = sys_get_temp_dir();
        try {
            $ghostscript = new Ghostscript($binPath, $tmpPath);
            $ghostscript->merge(
                'C:\\Users\\DesarrolladorOasic1\\proyectos\\symfony\\pdfs-creados',
                'hoja de vida de ' . $user->getNames() . ' ' . $user->getLastNames() . '.pdf',
                $flattenAndFilterArray
            );
        } catch (\Throwable $th) {
            return new JsonResponse(['error' => $th->getMessage()], 500);
        }
        return new JsonResponse($flattenAndFilterArray);
        // // return new JsonResponse($flattenAndFilterArray);
        // $pdf = new Fpdi();
        // foreach ($flattenAndFilterArray as $file) {
        //     $pageCount = $pdf->setSourceFile($file);
        //     for($i=0; $i < $pageCount; $i++) {
        //         $pdf->addPage();
        //         $tplId = $pdf->importPage($i+1);
        //         $pdf->useTemplate($tplId);
        //     }
        // }
        // $pdfContent = $pdf->Output('', 'S');

        // // Create a Symfony Response
        // $response = new Response($pdfContent);

        // // Set the appropriate headers to make it downloadable
        // $response->headers->set('Content-Type', 'application/pdf');
        // $response->headers->set('Content-Disposition', 'attachment; filename="merged.pdf"'); // You can set the desired filename

        // return $response;
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
