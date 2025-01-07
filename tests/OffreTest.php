<?php

namespace App\Tests\Entity;

use App\Entity\Offre;
use PHPUnit\Framework\TestCase;

class OffreTest extends TestCase
{
    public function testSetQuantity()
    {
        $offre = new Offre();
        $offre->setQuantity(10);

        $this->assertEquals(10, $offre->getQuantity(), "La quantité devrait être 10.");
        
        $offre->setQuantity(20);
        $this->assertEquals(20, $offre->getQuantity(), "La quantité devrait être mise à jour à 20.");
    }
}

