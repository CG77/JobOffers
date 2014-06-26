<?php
/**
 * Created by PhpStorm.
 * User: Amine
 * Date: 26/06/14
 * Time: 14:43
 */

namespace Job\OffersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FileExtension extends Constraint {

    public $message = 'Le fichier doit être un PDF';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
} 