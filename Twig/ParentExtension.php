<?php

namespace Job\OffersBundle\Twig;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Twig_Extension;

class ParentExtension extends Twig_Extension
{

    protected $repository;
    protected $configResolver;

    public function __construct(Repository $repository, ConfigResolverInterface $configResolver)
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }

    public function getFunctions()
    {
        return array(
            'get_parent_n_1' => new \Twig_Function_Method($this, 'parentByContentId'),
        );
    }


    public function parentByContentId($locationId){
        $currentLocation = $this->repository->getLocationService()->loadLocation($locationId);
        $aIdLocations = explode('/',$currentLocation->pathString);
        foreach($aIdLocations as $id){
            if($id != '' && $id != 0 && $id != 1 && $id != 2 ){
                $location = $this->repository->getLocationService()->loadLocation($id);
                $classId = $this->repository->getContentTypeService()->loadContentType($location->getContentInfo()->contentTypeId)->identifier;
                if($classId === 'n_1_page'){
                    $contentInfo = $location->getContentInfo();
                    break;
                }
            }
        }
        $parentContent =  $this->repository->getContentService()->loadContentByContentInfo($contentInfo);
        return $parentContent;
    }



    public function getName()
    {
        return 'parent_extension';
    }

}