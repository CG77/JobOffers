<?php
/**
 * Created by PhpStorm.
 * User: Amine
 * Date: 26/06/14
 * Time: 14:48
 */

namespace Job\OffersBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class FileExtensionValidator extends ConstraintValidator {
    public function validate( $value, Constraint $constraint ) {
        if ($value instanceof UploadedFile) {
            $extension = $value->getClientOriginalExtension();
            if ($extension != 'pdf') {
                $this->context->addViolation($constraint->message, array('%string%' => $value));
            }
        }
    }
} 