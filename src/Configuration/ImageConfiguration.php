<?php

namespace ApertureLabo\CoreBundle\Configuration;

use ApertureLabo\CoreBundle\Exception\CoreImageException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ImageConfiguration
 * 
 * Configuration spécifique aux images déclarée dans le fichier corde_bundle.yaml
 */
class ImageConfiguration{

    private const YAML_CONFIGURATION_PATH = 'config/aperture_core/core_bundle.yaml';

    private const DEFAULT_FILE_HASH_ALGORITHM = 'sha256';

    public const IMAGE_ENTITY_CLASS_NAME = 'App\\Entity\\Image';
    public const IMAGE_THUMBNAIL_ENTITY_CLASS_NAME = 'App\\Entity\\ImageThumbnail';

    private $project_dir;
    private $image_configuration;

    public function __construct(KernelInterface $kernelInterface)
    {
        $this->project_dir = $kernelInterface->getProjectDir().'/';

        $absolute_yaml_configuration = $this->project_dir.self::YAML_CONFIGURATION_PATH;
        if(!file_exists($absolute_yaml_configuration)) throw new CoreImageException('La configuration YAML du CoreBundle est introuvable. Pour rappel, la configuration YAML du CoreBundle doit se déclarer dans '.self::YAML_CONFIGURATION_PATH);
        $yaml_configuration = Yaml::parseFile($absolute_yaml_configuration);

        $this->image_configuration = $yaml_configuration['core_bundle']['image_service'];

        // Définition des valeurs par défaut
        if(!isset($this->image_configuration['file_hash_algorithm'])) $this->image_configuration['file_hash_algorithm'] = self::DEFAULT_FILE_HASH_ALGORITHM;
    }

    /**
     * Retourne le mapping des entités et de leurs images
     * 
     * @return array $mapping Mapping des entités et de leurs images
     */
    public function getMapping(){
        return $this->image_configuration['mapping'];
    }

    /**
     * Retourne le mapping d'une entité - image en particulier
     * 
     * @param string $entity_class Nom de la classe de l'entité
     * @param string $image_reference Référence de l'image
     * 
     * @return array $image_mapping Mapping de l'image
     */
    public function getImageMapping(string $entity_class, string $image_reference) {
        return $this->image_configuration['mapping'][$entity_class][$image_reference];
    }

    /**
     * Retourne le chemin absolue de stockage des images (privées)
     * 
     * @return string $absolute_image_storing_location Emplacement de stockage
     */
    public function getAbsoluteImageStoringLocation(){
        return $this->project_dir.$this->image_configuration['image_storing_location'];
    }

    /**
     * Retourne le chemin absolue de stockage des miniatures (publiques)
     * 
     * @return string $absolute_thumbnail_storing_location Emplacement de stockage
     */
    public function getAbsoluteThumbnailStoringLocation(){
        return $this->project_dir.$this->image_configuration['thumbnail_storing_location'];
    }

    /**
     * Retourne l'algorithme de hashage des fichiers
     * 
     * @return string $file_hash Algorithme de hashage
     */
    public function getFileHashAlgorithm(){
        return $this->project_dir.$this->image_configuration['file_hash_algorithm'];
    }

    /**
     * Retourne les dimensions d'une clé de miniature
     * 
     * @param string $thumbnail_reference Référence de la miniature
     * 
     * @return array $thumbnail_sizes Dimensions de la miniature
     */
    public function getThumbnailSizes(string $thumbnail_reference){
        if(!isset($this->image_configuration['thumbnail_sizes'][$thumbnail_reference])) throw new CoreImageException('Les dimensions des miniatures ne sont pas renseignées dans le YAML');
        return $this->image_configuration['thumbnail_sizes'][$thumbnail_reference];
    }
}