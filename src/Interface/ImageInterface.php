<?php

namespace ApertureLabo\CoreBundle\Interface;

use Doctrine\Common\Collections\Collection;

/**
 * Interface sollicitée dans le cas de la gestion des images par le CoreBundle
 */
interface ImageInterface
{
    public function getId(): ?int;
    public function getUploadDate(): ?\DateTimeInterface;
    public function setUploadDate(\DateTimeInterface $upload_date): static;
    public function getOriginalSize(): ?int;
    public function setOriginalSize(int $original_size): static;
    public function getOriginalPrettySize(): ?string;
    public function setOriginalPrettySize(string $original_pretty_size): static;
    public function getOriginalWidth(): ?int;
    public function setOriginalWidth(int $original_width): static;
    public function getOriginalHeight(): ?int;
    public function setOriginalHeight(int $original_height): static;
    public function getCloudFilename(): ?string;
    public function setCloudFilename(string $cloud_filename): static;
    public function getExtension(): ?string;
    public function setExtension(string $extension): static;
    public function getMimeType(): ?string;
    public function setMimeType(string $mime_type): static;
    public function getDescription(): ?string;
    public function setDescription(?string $description): static;
    public function getAltText(): ?string;
    public function setAltText(?string $alt_text): static;
    public function getFileHash(): ?string;
    public function setFileHash(string $file_hash): static;
    public function getOrigineReference(): ?string;
    public function setOrigineReference(string $origine_reference): static;
    public function getThumbnailMode(): ?string;
    public function setThumbnailMode(string $thumbnail_mode): static;
    public function getEntityClass(): ?string;
    public function setEntityClass(string $entity_class): static;
    public function getNature(): ?string;
    public function setNature(string $nature): static;
    public function getImageThumbnails(): Collection;
    /* public function addImageThumbnail(ImageThumbnail $imageThumbnail): static;
    public function removeImageThumbnail(ImageThumbnail $imageThumbnail): static; */
}
