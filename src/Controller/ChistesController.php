<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Chistes;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/api", name="api_")
 */

class ChistesController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    /**
    * @Route("/chistes", name="project_index", methods={"GET"})
    */
    public function index(Request $request): JsonResponse
    {
        $origin = ($request->get("origin")) ? $request->get("origin") : null;
        $response = null;
        $chiste = null;

        if(!$origin){
            return $this->json([
                'message' => 'A origin param is required',
                'status'  => 500
            ]);
        }
        
        switch($origin){
            case 'Chuck':
                $response = $this->client->request(
                    'GET',
                    'https://api.chucknorris.io/jokes/random',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'User-Agent' => 'Squadmakers/jparra@inkubes.com'
                        ]
                    ]
                    
                );
                $content = json_decode($response->getContent());
                
                $chiste = ($content) ? $content->value : null;
                break;
            case 'Dad':
                $response = $this->client->request(
                    'GET',
                    'https://icanhazdadjoke.com/',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'User-Agent' => 'Squadmakers/jparra@inkubes.com'
                        ]
                    ]
                );
                $content = json_decode($response->getContent());

                $chiste = ($content) ? $content->joke : null;
                break;
            default:
                break;
        }

        if(!$chiste){
            return $this->json([
                'message' => 'Origin '.$origin.' is not correct, please use "Chuck" or "Dad"',
                'status'  => 500
            ]);
        }

        return $this->json([
            'chiste' => $chiste,
        ]);
    }

    /**
     * @Route("/chistes", name="chistes_new", methods={"POST"})
     */
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        if(!$request->request->get('text')){
            return $this->json([
                'message' => 'Please provide a text',
                'status'  => 500
            ]); 
        }
        $entityManager = $doctrine->getManager();
        $origin = ($request->request->get('origin')) ? $request->request->get('origin') : "Custom";

        $chiste = new Chistes();
        $chiste->setText($request->request->get('text'));
        $chiste->setOrigin($origin);
        $chiste->setDateCreated(new \DateTime());
  
        $entityManager->persist($chiste);
        $entityManager->flush();
  
        return $this->json([
            "message" => 'Created new joke successfully with id ' . $chiste->getId(),
            "status" => 200
        ]);
    }

    /**
     * @Route("/chistes", name="chistes_update", methods={"PATCH", "PUT"})
     */
    public function update(ManagerRegistry $doctrine, Request $request): Response
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        
        if(!isset($data["number"])){
            return $this->json([
                'message' => 'Please provide a joke number',
                'status'  => 500
            ]); 
        }
        if(!isset($data["text"])){
            return $this->json([
                'message' => 'Please provide a new joke in text parameter',
                'status'  => 500
            ]); 
        }

        $entityManager = $doctrine->getManager();
        $chiste = $entityManager->getRepository(Chistes::class)->find($data["number"]);

        $origin = (isset($data["origin"])) ? $data["origin"] : $chiste->getOrigin();

        $chiste->setText($data["text"]);
        $chiste->setOrigin($origin);
  
        $entityManager->persist($chiste);
        $entityManager->flush();
  
        return $this->json([
            "message" => 'Updated joke successfully with id ' . $chiste->getId() . ': ' . $chiste->getText() ,
            "status" => 200
        ]);
    }
}
