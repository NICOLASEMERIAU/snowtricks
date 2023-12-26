<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[UniqueEntity('video_link')]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $video_link = null;

    #[ORM\ManyToOne(inversedBy: 'video')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $mytrick = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoLink(): ?string
    {
        return $this->video_link;
    }

    public function setVideoLink(string $video_link): static
    {
        $this->video_link = $video_link;

        return $this;
    }

    public function getMytrick(): ?Trick
    {
        return $this->mytrick;
    }

    public function setMytrick(?Trick $mytrick): static
    {
        $this->mytrick = $mytrick;

        return $this;
    }
}
