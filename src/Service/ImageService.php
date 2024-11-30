<?php

namespace ApertureLabo\CoreBundle\Service;

use ApertureLabo\CoreBundle\Configuration\ImageConfiguration;
use ApertureLabo\CoreBundle\Exception\CoreImageException;
use ApertureLabo\CoreBundle\Interface\ImageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ImageService
 * 
 * Utilitaire de gestion d'image d'entités
 */
class ImageService extends ImageServiceProcessor{

    private EntityManagerInterface $entityManager;
    private ImageConfiguration $imageConfiguration;
    
    private string $project_dir;
    private array $image_configuration;

    public function __construct(EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        ImageConfiguration $imageConfiguration)
    {
        $this->entityManager = $entityManager;

        $this->project_dir = $kernel->getProjectDir().'/';

        $this->imageConfiguration = $imageConfiguration;
    }

    /**
     * Upload une image
     * 
     * @param ?object $image Entité Image
     * @param UploadedFile $file Fichier contenant l'image
     * @param object $entity Entité Symfony
     * @param string $image_nature Nature de l'image
     * 
     * @return object $image Entité Image
     */
    public function uploadImage(?object $image, UploadedFile $file, object &$entity, string $image_nature){

        // Définition des variables récurrentes
        $image_mapping = $this->imageConfiguration->getImageMapping($entity::ENTITY_CLASS, $image_nature); // Récupération du mapping propre à l'image
        $absolute_image_storing_location = $this->imageConfiguration->getAbsoluteImageStoringLocation();

        // On contrôle si le fichier transmis est valide
        $file_path = $file->getRealPath();
        if(!$file_path || !file_exists($file_path)) throw new Exception('Le fichier image uploadé n\'est pas valide');

        // Si l'entité existe déjà, on supprime ses anciennes images afin de la mettre à jour
        if($image != null)
        {
            $image_absolute_path = $absolute_image_storing_location.$image_mapping['image_path'].$image->getCloudFilename(); // Image (privée)
            if(file_exists($image_absolute_path)) unlink($image_absolute_path);
            $this->removeImageThumbnails($image); // Suppression des miniatures locales
        }
        else
        {
            $image_entity_class_name = ImageConfiguration::IMAGE_ENTITY_CLASS_NAME;
            if (!class_exists($image_entity_class_name)) throw new CoreImageException('La classe '.$image_entity_class_name.' est introuvable');
            $image = new $image_entity_class_name();
        }
        
        // Définition des données de l'image originale
        if(strtolower($file->getClientOriginalExtension()) == 'svg')
        {
            $original_width = 0;
            $original_height = 0;
        }
        else list($original_width, $original_height) = getimagesize($file->getRealPath());
        
        // Définition du mode de miniaturisation
        switch($image_mapping['thumbnail_mode'])
        {
            case 'INSET':
                $thumbnail_mode_label = 'THUMBNAIL_INSET';
                break;
            case 'OUTBOUND':
                $thumbnail_mode_label = 'THUMBNAIL_OUTBOUND';
                break;
                default:
                $thumbnail_mode_label = null;
        }
        
        $file_size = $file->getSize();
        $file_extension = strtolower($file->getClientOriginalExtension());
        $file_client_mime_type = $file->getClientMimeType();

        $cloud_filename = $entity->getId().'.'.$file_extension;
        if(!is_dir($absolute_image_storing_location.$image_mapping['image_path'])) mkdir($absolute_image_storing_location.$image_mapping['image_path'], 0777, true);
        $file->move($absolute_image_storing_location.$image_mapping['image_path'], $cloud_filename); // Upload de l'image originale sur le Cloud

        // Remplissage des données de l'entité image
        $image->setUploadDate(new \DateTime())
            ->setOriginalSize($file_size)
            ->setOriginalPrettySize($this->getPrettySize($file_size))
            ->setOriginalWidth($original_width)
            ->setOriginalHeight($original_height)
            ->setCloudFilename($cloud_filename)
            ->setExtension($file_extension)
            ->setMimeType($file_client_mime_type)
            ->setDescription($image_mapping['description'])
            ->setAltText($image_mapping['alt_text'])
            ->setFileHash(hash_file($this->imageConfiguration->getFileHashAlgorithm(), $absolute_image_storing_location.$image_mapping['image_path'].$cloud_filename))
            ->setOrigineReference($image_mapping['origine_reference'])
            ->setThumbnailMode($thumbnail_mode_label)
            ->setEntityClass($entity::ENTITY_CLASS)
            ->setNature($image_nature);

        // Persistence de l'entité
        if(!$this->entityManager->contains($image)) $this->entityManager->persist($image);
        $this->entityManager->flush();
        
        $this->createImageThumbnails($image); // Création des miniatures

        return $image;
    }

    /**
     * Crée les miniatures d'une image
     * 
     * @param object $image Entité Image
     */
    public function createImageThumbnails(object &$image): void
    {
        // Définition des variables récurrentes
        $image_mapping = $this->imageConfiguration->getImageMapping($image->getEntityClass(), $image->getNature());
        $absolute_image_storing_location = $this->imageConfiguration->getAbsoluteImageStoringLocation();
        $absolute_thumbnail_storing_location = $this->imageConfiguration->getAbsoluteThumbnailStoringLocation().$image_mapping['thumbnail_path'];

        $image_absolute_path = $absolute_image_storing_location.$image_mapping['image_path'].$image->getCloudFilename(); // Image (privée)
        
        if(file_exists($image_absolute_path))
        {
            
            foreach($image_mapping['thumbnail_keys'] as $thumbnail_key)
            {
                $thumbnail_sizes = $this->imageConfiguration->getThumbnailSizes($thumbnail_key);
                
                // Création de la miniature
                $imagine = new Imagine();
                $imagine_image = $imagine->open($image_absolute_path);
                $imagine_image = $this->correctImageOrientation($imagine_image, $image_absolute_path);
    
                $save_path = $absolute_thumbnail_storing_location.$thumbnail_key.'/';
                $filename = md5(uniqid().$thumbnail_key.$image->getId()).'.'.$image->getExtension();
    
                $this->createDirectoryIfNecessary($save_path);
    
                // Redimension de la miniature
                if($thumbnail_sizes['width'] == null && $thumbnail_sizes['height'] == null) copy($image_absolute_path, $save_path.$filename);
                else
                {
                    $size = new Box($thumbnail_sizes['width'], $thumbnail_sizes['height']);
                    $mode = $this->getImagineThumbnailMode($image_mapping['thumbnail_mode']);
                    $thumb = $imagine_image->thumbnail($size, $mode);
                    $thumb->save($save_path.$filename); // Sauvegarde de l'image
                }
    
                $size = filesize($save_path.$filename);
                list($thumbnail_width, $thumbnail_height) = getimagesize($save_path.$filename);
    
                // Création d'une entité ImageThumbnail

                $image_thumbnail_entity_class_name = ImageConfiguration::IMAGE_THUMBNAIL_ENTITY_CLASS_NAME;
                if (!class_exists($image_thumbnail_entity_class_name)) throw new CoreImageException('La classe '.$image_thumbnail_entity_class_name.' est introuvable');

                $imageThumbnail = new $image_thumbnail_entity_class_name();
                $imageThumbnail->setImage($image)
                                ->setThumbnailKey($thumbnail_key)
                                ->setSize($size)
                                ->setPrettySize($this->getPrettySize($size))
                                ->setWidth($thumbnail_width)
                                ->setHeight($thumbnail_height)
                                ->setFilename($filename);
    
                $this->entityManager->persist($imageThumbnail);
                $this->entityManager->flush();
    
                unset($imagine_image);
            }
        }
    }

    /**
     * Supprime toutes les miniatures d'une image (BDD + Miniatures)
     * 
     * @param ImageInterface $image Entité Image
     */
    public function removeImageThumbnails(ImageInterface &$image): void
    {
        $image_mapping = $this->imageConfiguration->getImageMapping($image->getEntityClass(), $image->getNature());
        foreach($image->getImageThumbnails() as $imageThumbnail)
        {
            $thumbnail_absolute_path = $this->imageConfiguration->getAbsoluteThumbnailStoringLocation().$image_mapping['thumbnail_path'].$imageThumbnail->getThumbnailKey().'/'.$imageThumbnail->getFilename(); // Chemin absolu de la miniature
            $this->removeFileIfExists($thumbnail_absolute_path); // Suppression du fichier physique
            $this->entityManager->remove($imageThumbnail);       // Suppression de la base de données
        }
        $this->entityManager->flush();
    }

    /**
     * Retourne l'URL d'une image
     * 
     * @param ?ImageInterface $image Entité Image
     * @param string $entity_class Classe de l'entité générique Symfony
     * @param string $nature Nature de l'image
     * @param string $thumbnail_key Clé de miniature
     * 
     * @return string $image_url URL de l'image (réelle ou par défaut) 
     */
    public function getImageUrl(?ImageInterface $image, string $entity_class, string $nature, string $thumbnail_key){
        $image_mapping = $this->imageConfiguration->getImageMapping($image->getEntityClass(), $image->getNature());
        $default_image = $image_mapping['default_image']; // Définition de l'image par défaut

        // Définition de l'emplacement de la miniature
        if($image != null)
        {
            foreach($image->getImageThumbnails() as $imageThumbnail)
            {
                if($imageThumbnail->getThumbnailKey() == $thumbnail_key)
                {
                    $image_url = $image_mapping['thumbnail_path'].$thumbnail_key.'/'.$imageThumbnail->getFilename();
                    break;
                }
            }
        }

        if(!isset($image_url)) return $default_image;
        return (file_exists($this->imageConfiguration->getAbsoluteThumbnailStoringLocation().$image_url)) ? 'image/'.$image_url : $default_image;
    }

}