<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "images")]
class CardImage
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: 'string')]
    private $publicId;

    #[MongoDB\Field(type: 'string')]
    private $url;

    #[MongoDB\Field(type: 'string')]
    private $cardName;

    #[MongoDB\Field(type: 'string')]
    private $type; // 'pokemon' ou 'yugioh'



    // Getters et Setters
    public function getId() { return $this->id; }
    
    public function getPublicId() { return $this->publicId; }
    public function setPublicId($publicId) { 
        $this->publicId = $publicId; 
        return $this;
    }
    
    public function getUrl() { return $this->url; }
    public function setUrl($url) { 
        $this->url = $url; 
        return $this;
    }
    
    public function getCardName() { return $this->cardName; }
    public function setCardName($cardName) { 
        $this->cardName = $cardName; 
        return $this;
    }
    
    public function getType() { return $this->type; }
    public function setType($type) { 
        $this->type = $type; 
        return $this;
    }
    
   
}