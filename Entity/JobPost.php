<?php
/**
 * Created by PhpStorm.
 * User: a.haddad
 * Date: 09/04/14
 * Time: 14:27
 */

namespace Job\OffersBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Job\OffersBundle\Validator\Constraints as JobAssert;

class JobPost {
    /**
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     */
    private $gender;
    /**
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     */
    private $firstName;
    /**
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     */
    private $lastName;
    /**
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     * @Assert\Email(message="'{{ value }}' n'est pas un email valide")
     */
    private $email;
    /**
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     * @JobAssert\FileExtension
     * @Assert\File(
     *     maxSize = "10000000",
     *     mimeTypes = {"application/pdf","application/x-pdf"},
     *     mimeTypesMessage = "Le fichier doit être un PDF"
     * )
     */
    private $cv;
    /**
     * @JobAssert\FileExtension
     * @Assert\File(
     *     maxSize = "10000000",
     *     mimeTypes = {"application/pdf","application/x-pdf"},
     *     mimeTypesMessage = "Le fichier doit être un PDF"
     * )
     */
    private $motivation;
    /**
     * @param mixed $cv
     */
    public function setCv($cv) {
        $this->cv = $cv;
    }

    /**
     * @return mixed
     */
    public function getCv() {
        return $this->cv;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $motivation
     */
    public function setMotivation($motivation) {
        $this->motivation = $motivation;
    }

    /**
     * @return mixed
     */
    public function getMotivation() {
        return $this->motivation;
    }
} 