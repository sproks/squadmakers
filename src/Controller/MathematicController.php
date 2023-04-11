<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;


/**
 * @Route("/api", name="api_")
 */

 

class MathematicController extends AbstractController
{

    /**
     * Returns de LCM between numbers prvided in query param 
     * 
     * @Route("/mathematic/lcm", name="mathematic_lcm", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="lcm",type="integer",format="int32")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *    
     * )
     * @OA\Parameter(
     *     name="numbers",
     *     in="query",
     *     description="The list of the numbers to process separated by coma",
     *     @OA\Schema(type="string")
     * )
    */
    public function index(Request $request): Response
    {
        if(!$request->get('numbers')){
            return $this->json([
                'message' => 'Please provide a list of numbers',
                'status'  => 500
            ]); 
        }

        $numbers = $request->get("numbers");
        $nums_array = explode(",", $numbers);


        $lcm = $this->lcm_arr($nums_array);

        return $this->json([
            "lcm" => $lcm,
        ]);
    }

     /**
     * Returns the Number provided +1 
     * 
     * @Route("/mathematic/plus_one", name="mathematic_po", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="number",type="integer",format="int32")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Return error message",
     *    
     * )
     * @OA\Parameter(
     *     name="number",
     *     in="query",
     *     description="The number to process",
     *     @OA\Schema(type="string")
     * )
    */

    public function plusOne(Request $request): Response
    {
        if(!$request->get('number')){
            return $this->json([
                'message' => 'Please provide a number',
                'status'  => 500
            ]); 
        }

        $number = $request->get('number');

        if(!is_numeric($number)){
            return $this->json([
                'message' => 'Please provide a correct number',
                'status'  => 500
            ]); 
        }

        $num = intval($number);

        return $this->json([
            "number" => ++$num,
        ]);
    }

    public function lcm_arr($items){
        //Input: An Array of numbers
        //Output: The LCM of the numbers
        while(2 <= count($items)){
            array_push($items, $this->lcm(array_shift($items), array_shift($items)));
        }
        return reset($items);
    }


    public function lcm($n, $m) {
        return $m * ($n/$this->gcd($n,$m));
    }

    public function gcd($n, $m) {
        $n=abs($n); $m=abs($m);
        if ($n==0 and $m==0)
            return 1; //avoid infinite recursion
        if ($n==$m and $n>=1)
            return $n;
        return $m<$n?$this->gcd($n-$m,$n):$this->gcd($n,$m-$n);
     }
    
}
