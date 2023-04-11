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

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;



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
     * Return a joke from origin especified in query param "origin"
     * 
     * @Route("/chistes", name="project_index", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Return a random joke from origin especified in query param",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="chiste",format="string")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *    
     * )
     * @OA\Parameter(
     *     name="origin",
     *     in="query",
     *     description="The field used to check joke source",
     *     @OA\Schema(type="string")
     * )
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
     * Creates a joke in Database
     * 
     * @Route("/chistes", name="chistes_new", methods={"POST"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Creates a new joke in Db",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="200"),
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="500"),
     *     )
     *    
     * )
     * @OA\RequestBody(
     *     description="The joke object",
     *     required=true,
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="text",format="string"),
     *          @OA\Property(property="origin",format="string"),
     *      )
     * )
     * 
     *
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
     * Update a joke in dataase
     * 
     * @Route("/chistes", name="chistes_update", methods={"PATCH", "PUT"})
     * 
     * 
     * @OA\Response(
     *     response=200,
     *     description="Updates a joke in Db",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="200"),
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="500"),
     *     )
     *    
     * )
     * @OA\RequestBody(
     *     description="The joke object",
     *     required=true,
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="number",type="integer",format="int32"),
     *          @OA\Property(property="text",format="string"),
     *          @OA\Property(property="origin",format="string"),
     *      )
     * )
     * 
     *
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

    /**
     * Deletes a joke by number 
     * 
     * @Route("/chistes", name="chiste_delete", methods={"DELETE"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Updates a joke in Db",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="200"),
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message",format="string"),
     *        @OA\Property(property="status",type="integer",format="int32",default="500"),
     *     )
     *    
     * )
     * @OA\RequestBody(
     *     description="The joke object",
     *     required=true,
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="number",type="integer",format="int32"),
     *         
     *      )
     * )
     * 
     *
     */
    public function delete(ManagerRegistry $doctrine, Request $request): Response
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        
        if(!isset($data["number"])){
            return $this->json([
                'message' => 'Please provide a joke number to be deleted',
                'status'  => 500
            ]); 
        }

        

        $entityManager = $doctrine->getManager();
        $chiste = $entityManager->getRepository(Chistes::class)->find($data["number"]);
  
        if (!$chiste) {
            return $this->json([
                "message" => 'No joke found for number ' . $data["number"],
                "status" => 404
            ], 404);
        }

        $entityManager->remove($chiste);
        $entityManager->flush();
  
        return $this->json([
            "message" => 'Deleted a joke successfully with number ' . $chiste->getId(),
            "status"  => 200
        ]);
    }
}
