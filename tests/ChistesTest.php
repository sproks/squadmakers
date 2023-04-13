<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChistesTest extends WebTestCase
{
    public function testGet(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/chistes');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testPut(): void 
    {
        $client = static::createClient();

        $crawler = $client->request('PUT', '/api/chistes', [
            "number"=> 1,
            "text"  => "Chiste updated test",
            "origin"=> "test"
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testPost(): void 
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/api/chistes', [
            "text"  => "Chiste updated test",
            "origin"=> "test"
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testDelete(): void 
    {
        $client = static::createClient();

        $crawler = $client->request('DELETE', '/api/chistes', [
            "number"=>2
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testMathLcm(): void 
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/mathematic/lcm?numbers=34,2,134,2,4');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testMathPO(): void 
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/mathematic/plus_one?number=34');

        $this->assertResponseStatusCodeSame(200);
    }

}
