<?php

namespace Sae\Tests;

use PHPUnit\Framework\TestCase;

class PassWordTest extends TestCase
{

    public function testPasswordHash()
    {
        $password = 'mot_de_passe_ultra_securise';
        $hash = hash('sha256', $password);
        $this->assertNotEmpty($hash); // Être sûr que le hash n'est pas vide
        $this->assertEquals(hash('sha256', $password), $hash);
    }

}