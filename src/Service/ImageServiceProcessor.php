<?php

namespace ApertureLabo\CoreBundle\Service;

use Imagine\Image\ImageInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ImageServiceProcessor
 * 
 * Processeur de la classe de service ImageService
 */
class ImageServiceProcessor {

    // Constantes
    private const SIZE_UNITS = ['o', 'Ko', 'Mo', 'Go', 'To', 'Po'];

    protected string $project_dir;
    protected array $image_configuration;

    public function __construct(KernelInterface $kernelInterface)
    {
        $this->project_dir = $kernelInterface->getProjectDir().'/';

        
    }

    /////////////////////////////////////////// CONSOLE ///////////////////////////////////////////

    /**
     * Configure les paramètres de sortie pour l'affichage d'informations dans la console
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function setConsoleIO(InputInterface $input, OutputInterface $output): void
    {
        // Définition des styles de texte coloré
        $style_green = new OutputFormatterStyle('green');
        $style_yellow = new OutputFormatterStyle('yellow');
        $style_blue = new OutputFormatterStyle('blue');

        // Ajout des styles à l'output
        $output->getFormatter()->setStyle('green', $style_green);
        $output->getFormatter()->setStyle('yellow', $style_yellow);
        $output->getFormatter()->setStyle('blue', $style_blue);
    }

    /////////////////////////////////////////// FORMATTAGE ///////////////////////////////////////////

    /**
     * Retourne une taille de fichier formattée
     * 
     * @param int $bytes Taille donnée en bits
     * 
     * @return string $pretty_size Taille formattée
     */
    protected function getPrettySize(int $bytes) {
        $size = max($bytes, 0);
        $power = floor(($size ? log($size) : 0) / log(1024));
        $power = min($power, count(self::SIZE_UNITS) - 1);
        $size_formatted = $size / pow(1024, $power);
        return number_format($size_formatted, 2).' '.self::SIZE_UNITS[$power];
    }

    /////////////////////////////////////////// IMAGINE ///////////////////////////////////////////

    /**
     * Corrige l'orientation des images
     * 
     * @param ImageInterface $imagine_image Objet ImageInterface
     * @param string $tmp_file Fichier concerné
     * 
     * @return ImageInterface $imagine_image Objet ImageInterface
     */
    protected function correctImageOrientation(ImageInterface $imagine_image, $tmp_file) {
        $exif = @exif_read_data($tmp_file);
        if ($exif && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            switch ($orientation) {
                case 3:
                    $imagine_image->rotate(180);
                    break;
                case 6:
                    $imagine_image->rotate(90);
                    break;
                case 8:
                    $imagine_image->rotate(-90);
                    break;
            }
        }
        return $imagine_image;
    }

    /**
     * Return un mode de miniaturisation pris en charge par la librairie Imagine
     * 
     * @param string $thumbnail_mode Mode de miniaturisation "brut"
     * 
     * @return object $imagine_thumbnail_mode Mode de miniaturisation Imagine
     */
    public function getImagineThumbnailMode(string $thumbnail_mode){
        switch($thumbnail_mode)
        {
            case 'INSET':
                $imagine_thumbnail_mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                break;
            case 'OUTBOUND':
                $imagine_thumbnail_mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                break;
            default:
                $imagine_thumbnail_mode = null;
        }
        return $imagine_thumbnail_mode;
    }

    /////////////////////////////////////////// TREE STRUCTURE ///////////////////////////////////////////

    /**
     * Crée un répertoire de manière récursive s'il n'existe pas
     * 
     * @param string $directory_path Chemin absolu du répertoire
     */
    public function createDirectoryIfNecessary(string $path){
        if(!is_dir($path)) mkdir($path, 0777, true);
    }

    /**
     * Supprime un fichier s'il existe
     * 
     * @param string $file_path Chemin absolu du fichier
     */
    public function removeFileIfExists(string $file_path){
        if(file_exists($file_path)) unlink($file_path);
    }
}