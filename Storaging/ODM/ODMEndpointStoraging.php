<?php

/**
 * Description of ODMStoraging
 *
 * @author igncoto
 */

namespace Ict\StatsBundle\Storaging\ODM;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\Container;

use Ict\StatsBundle\Storaging\EndPointStoragingInterface;
use Ict\StatsBundle\SettingParametersInterface;

class ODMEndpointStoraging implements EndPointStoragingInterface,SettingParametersInterface {
    
    /**
     * Mongo ODM
     * @var object 
     */
    protected $odm;
    
    /**
     * request stack
     * @var object
     */
    protected $request;
    
    /**
     * Bag parameter
     * @var ParameterBag 
     */
    protected $bag;
    
    /**
     * Loads ODM and container service and inits bag parameter
     * @param object $odm
     * @param object $container
     */
    public function __construct($odm, $request){
        
        $this->odm = $odm;
        $this->request = $request;
    }
    
    /**
     * {@inheritDoc}
     */
    public function setParams(array $params) {
        
        $this->bag = new ParameterBag($params);
    }
    
    /**
     * {@inheritDoc}
     */
    public function hitStat($service, $operationField){
        
        $fields = $this->bag->get('db_handler.store_endpoint_fields');
        
        $this->odm->getManager()->createQueryBuilder($this->bag->get('db_handler.store_endpoint_name'))
                    ->update()
                    ->field($fields['date_field'])->equals(new \MongoDate(strtotime(date('Y-m-d'))))
                    ->field($fields['hour_field'])->equals(date('H'))
                    ->field($fields['ip_field'])->equals($this->request->getCurrentRequest()->getClientIp())
                    ->field('service')->equals($service)
                    ->field($operationField)->inc(1)
                    ->getQuery(array('upsert' => true))
                    ->execute()
            ;
    }
}
