<?php
/**
 * Created by PhpStorm.
 * User: a.haddad
 * Date: 22/04/14
 * Time: 10:12
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
use Symfony\Component\HttpFoundation\Request;


class JobSearchController extends BaseController {

    public function viewLocationAction( $locationId, $viewType , $layout = false, array $params = array() ){
        $locationService = $this->getRepository()->getLocationService();
        $contentService = $this->getRepository()->getContentService();
        $location = $locationService->loadLocation($locationId);
        $content = $contentService->loadContentByContentInfo($location->getContentInfo());
        $defaultData = array();

        $limit = 10;
        $offset = 0;
        $page = 1;

        if($this->getRequest()->get('page')!=""){
            $page = $this->getRequest()->get('page');
            $offset = ( $page - 1 ) * $limit;
        }

        $jobContentType = $this->getRepository()->getContentTypeService()->loadContentTypeByIdentifier('job');
        $aTmpType = $jobContentType->getFieldDefinition('job_type')->getFieldSettings()['options'];
        $aTypeEmploi = array();
        foreach($aTmpType as $type){
            $aTypeEmploi[$type] = $type;
        }
        $aTmpDomains = $jobContentType->getFieldDefinition('activity_field')->getFieldSettings()['options'];
        $aDomains = array();
        foreach($aTmpDomains as $domain){
            $aDomains[$domain] = $domain;
        }

        $request = $this->getRequest();

        $options = array('csrf_protection' => false);
        $branchSearch = array();
        $aRequestForm = $this->getRequest()->get('form');

        $aTmpTags = $content->getFieldValue('tags')->tags;
        foreach($aTmpTags as $oTag){
            $branchSearch[$oTag->id] = $oTag->keyword;
            $defaultData['branch'][] = $oTag->id;
        }

        $defaultData['keyword'] = $aRequestForm['keyword'];
        if(isset($aRequestForm['type'])){
            $defaultData['type'] = $aRequestForm['type'];
        }else{
            $defaultData['type'] = $aTypeEmploi;
        }
        if(isset($aRequestForm['branch'])){
            $defaultData['branch'] = $aRequestForm['branch'];
        }
        $defaultData['domain'] = $aRequestForm['domain'];
        $defaultData['place'] = $aRequestForm['place'];

        $keyword = $defaultData['keyword'];
        $aTags = $defaultData['branch'];
        $aTypes = $defaultData['type'];
        $place = $defaultData['place'];
        $domain = $defaultData['domain'];

        // Formulaire formBuilder
        $form = $this->createFormBuilder($defaultData, $options)->setMethod('GET')
            ->add( 'keyword', 'text', array('label_attr'=>array('class'=>'lbTitle'),'label' => 'Mots-clés','required' => false ))
            ->add('type', 'choice',array('data'=>$defaultData['type'],'choices' => $aTypeEmploi,'multiple' => true,'expanded' => true,
                        'required' => false))
            ->add('branch', 'choice',array('data'=>$defaultData['branch'],'choices' => $branchSearch,'multiple' => true,'expanded' => true,
                        'required' => false))
            ->add( 'place', 'choice', array('choices' => $this->getSites(),'empty_value' => "Lieu",'multiple' => false,
                        'expanded' => false,'required' => false ))
            ->add('domain', 'choice',array('choices' => $aDomains,'empty_value' => "Domaine d'activité",
                        'multiple' => false,'expanded' => false,'required' => false )
            )->getForm();

        //Handle request
        if($aRequestForm){
            $form->handleRequest($request);
            $data = $form->getData();
            $keyword = $data['keyword'];
            $aTags = $data['branch'];
            $aTypes = $data['type'];
            $place = $data['place'];
            $domain = $data['domain'];

        }

        $params = array(
            'keyword' => $keyword,
            'branchs' => $aTags,
            'types' => $aTypes,
            'place' => $place,
            'domain' => $domain,
            'limit' => $limit,
            'offset' => $offset
        );

        $aSearchResult = $this->getResultSearch($params,$locationId);
        $nbPage = ceil( $aSearchResult["SearchCount"] / $limit );
        $aGetParams = $this->getRequest()->query->all();
        if($aGetParams['page']){
            unset($aGetParams['page']);
        }
        $hash_bundle_name = $this->container->getParameter('hash_bundle_name');
        return $this->render(
            $hash_bundle_name[$this->getRequest()->attributes->get('siteaccess')->name].':Full:job_search.html.twig', array(
                'search' => $this->getResultSearch($params,$locationId),
                'form' => $form->createView(),
                'location' => $location,
                'nbPage' => $nbPage,
                'currentPage' => $page,
                'params' => http_build_query($aGetParams),
                'content' =>$content
            )
        );
    }

    protected function getSites(){
        $result = array();
        $searchService = $this->getRepository()->getSearchService();
        $content = $searchService->findSingle(new Criterion\ContentTypeIdentifier(array('structure_list')));
        $structures = $this->getContentByParent($searchService,'structure',$content->getVersionInfo()->getContentInfo()->mainLocationId);
        foreach($structures as $structure){
            $sites = $this->getContentByParent($searchService,'site',$structure->getVersionInfo()->getContentInfo()->mainLocationId);
            foreach($sites as $site){
                $result[$site->getFieldValue('city')->text] = $site->getFieldValue('city')->text;
            }
        }
        return $result;
    }

    protected function getContentByParent($searchService,$classIdentifier,$locationId){
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\ParentLocationId( $locationId ),
                new Criterion\ContentTypeIdentifier( array( $classIdentifier ) ),
                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
            )
        );
        $query->sortClauses = array( new SortClause\LocationPriority() );
        $aTmpResult = $searchService->findContent( $query );
        $aResult = array();
        foreach ( $aTmpResult->searchHits as $oResult ) {
            $aResult[] = $oResult->valueObject;
        }
        return $aResult;
    }

    protected function getResultSearch($params,$rootLocationId){
        $searchService = $this->get('eznovaextra.helper.search');
        // Keyword to search
        $query = $params['keyword'];
        //Get all elements under NodeID '2'
        $subtree = array($rootLocationId);

        // Add filter who apply to search
        $filters = array();
        if($params["types"]){
            $filters[] = 'job/job_type:("'.implode('" OR "',$params["types"]).'")';
        }
        if($params["place"]){
            $filters[] = 'job/place:("'.$params["place"].'")';
        }
        if($params["domain"]){
            $filters[] = 'job/activity_field:("'.$params["domain"].'")';
        }
        if ( $params["branchs"] ) {
            $tags = array();
            $tagsService = $this->container->get('ezpublish.api.service.tags');
            foreach($params['branchs'] as $tag){
                $parentTag = $tagsService->loadTag($tag);
                $children = $tagsService->loadTagChildren($parentTag);
                foreach($children as $child){
                    $tags[] = $child->keyword;
                }
            }
            $filters[] = 'job/branch:("'.implode('" OR "',$tags).'")';
        }
        // Add filter by class_identifier
        $class_id = array( 'job' );

        // Add sort by published
        //$sort = array('published' => 'desc');
        $sort = null;
        // Add limit to return result
        $limit = $params['limit'];
        $offset = $params['offset'];
        $contentResults  = $searchService->search($query, $subtree,  $filters, $class_id, $sort, $limit, $offset);
        return array('SearchCount' => $contentResults->getResultTotalCount(), 'SearchResult' => $contentResults->getResults());
    }

} 