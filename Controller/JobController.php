<?php
/**
 * Created by PhpStorm.
 * User: a.haddad
 * Date: 08/04/14
 * Time: 13:54
 */

namespace Job\OffersBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\REST\Common\RequestParser\eZPublish;
use Novactive\eZNovaExtraBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Job\OffersBundle\Entity\JobPost;
use Job\OffersBundle\Form\JobPostType;


class JobController extends BaseController {

    public function viewLocationAction( $locationId, $viewType , $layout = false){
        $locationService = $this->getRepository()->getLocationService();
        $contentService = $this->getRepository()->getContentService();
        $location = $locationService->loadLocation($locationId);
        $content = $contentService->loadContentByContentInfo($location->getContentInfo());
        $branchs = array();
        foreach($content->getFieldValue('branch')->tags as $index => $tag){
            $branchs[$index] = $this->getParentTag($tag->parentTagId,$tag);
        }
        return $this->render( 'JobOffersBundle:Full:offre_emploi.html.twig', array(
                'location' => $location,
                'content' =>$content,
                'branchs' =>$branchs
            )
        );
    }

    /**
     * @Route("/job/post", name="job_post")
     */
    public function sendAction() {
        $jobPost = new JobPost();
        $form = $this->createForm(new JobPostType, $jobPost);
        $successArray = array();
        if($this->getRequest()->isMethod('POST')){
            $form->submit($this->getRequest());
            $response = array();
            $errorsArray = array();
            $inputs = $form->all();
            foreach($inputs as $index=>$input){
                if($input->isValid()){
                    $successArray[] = array('elementId' => $index);
                }
            }
            if($form->isValid()){
                $sendService = $this->get('job.sendservice');
                $uploadDir = 'uploads';
                //Récupération de mail receiver
                $contactId = $this->get('request')->request->get('contactID');
                $contact = $this->getRepository()->getContentService()->loadContent($contactId);
				$title = $this->get('request')->request->get('title');
                $message = \Swift_Message::newInstance()
                        ->setSubject("Candidature à l'offre [".$title."] ".$jobPost->getFirstName().' '.$jobPost->getLastName())
                        ->setFrom($jobPost->getEmail())
                        ->setTo($contact->getFieldValue('email')->text)
                        ->setContentType('text/html')
                        ->setBody($this->renderView('JobOffersBundle:Job:mail.html.twig',
                                array(
                                    'gender'=>$jobPost->getGender(),
                                    'firstName'=>$jobPost->getFirstName(),
                                    'lastName'=>$jobPost->getLastName(),
                                    'email'=>$jobPost->getEmail()
                                )
                          ));
                $cvName = $sendService->upload($jobPost->getCv(),$uploadDir,'CV-'.$jobPost->getFirstName(),$jobPost->getCv()->getClientOriginalExtension());
                $message->attach(\Swift_Attachment::fromPath($uploadDir.'/'.$cvName)
                    ->setFilename('CV-'.$jobPost->getFirstName().'.'.$jobPost->getCv()->getClientOriginalExtension())
                );
                if($jobPost->getMotivation() != null){
                    $lmName = $sendService->upload($jobPost->getMotivation(),$uploadDir,'LM-'.$jobPost->getFirstName(),$jobPost->getCv()->getClientOriginalExtension());
                    $message->attach(\Swift_Attachment::fromPath($uploadDir.'/'.$lmName)
                        ->setFilename('LM-'.$jobPost->getFirstName().'.'.$jobPost->getCv()->getClientOriginalExtension())
                    );

                }
                $this->get('mailer')->send($message);
                $this->get('session')->getFlashBag()->add('notice','success');
                $response['success'] = true;
                $response['message'] = 'Votre demande est envoyée';
                $response['successInputs'] = $successArray;
               }else{
                $errors = $this->get('validator')->validate($form);
                foreach ($errors as $error){
                    $errorsArray[] = array(
                        'elementId' => str_replace('data.', '', $error->getPropertyPath()),
                        'errorMessage' => $error->getMessage()
                    );
                }

                if(count($errors) <=0){
                    $errorsArray[] = array('elementId' => 'recaptcha','errorMessage' => 'Captcha non valide');
                }
                $response['success'] = false;
                $response['errors'] = $errorsArray;
                $response['successInputs'] = $successArray;
            }
            return new JsonResponse($response);
        }
        return $this->render('JobOffersBundle:Job:send.html.twig',array('form'=>$form->createView()));
    }

    public function getParentTag($parentTagId,$tag){
        $result = array();
        $tagsService = $this->container->get('ezpublish.api.service.tags');
        $parentTag = $tagsService->loadTag($parentTagId);
        $result['parentTag'] = $parentTag->keyword;
        $result['tag'] = $tag->keyword;
        return $result;

    }
} 