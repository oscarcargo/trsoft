<?php

namespace Armensa\ViajesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Armensa\ViajesBundle\Entity\Viaje;
use Armensa\ViajesBundle\Form\ViajeType;

/**
 * Viaje controller.
 *
 * @Route("/viaje")
 */
class ViajeController extends Controller
{

    /**
     * Lists all Viaje entities.
     *
     * @Route("/", name="viaje")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ArmensaViajesBundle:Viaje')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Lists all vxv entities.
     *
     * @Route("/vxv", name="vxv")
     * @Method("GET")
     * @Template()
     */
    public function vxvAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ArmensaViajesBundle:Viaje')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Lists all vxc entities.
     *
     * @Route("/vxc", name="vxc")
     * @Method("GET")
     * @Template()
     */
    public function vxcAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ArmensaViajesBundle:Viaje')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Lists all utilidad entities.
     *
     * @Route("/utilidad", name="utilidad")
     * @Method("GET")
     * @Template()
     */
    public function utilidadAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ArmensaViajesBundle:Viaje')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Creates a new Viaje entity.
     *
     * @Route("/", name="viaje_create")
     * @Method("POST")
     * @Template("ArmensaViajesBundle:Viaje:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Viaje();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
        	$user = $this->get('security.context')->getToken()->getUser();
        	
        	$entity->setUsuario($user);
        	$entity->setFechaCreacion(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('viaje_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Lists all Viaje entities.
     *
     * @Route("/list", name="viaje_list")
     * @Method("GET")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('ArmensaViajesBundle:Viaje')->createQueryBuilder('v')
        	   ->add('select',"v.id, v.fecha, v.origen, v.destino, v.valorCompra ,v.valorVenta, v.peso, v.cantidad, co.nombre conductor, cl.nombre cliente, ve.placa vehiculo,tp.tipo tipoPr")
        	   ->leftJoin('v.conductor','co')
                ->leftJoin('v.cliente','cl')
                ->leftJoin('v.vehiculo','ve')
                ->leftJoin('v.tipoProceso','tp')
                ->orderBy('v.id','DESC');
        $entities=$qb->getQuery()->getResult();
		$fields=array(
		      'id' => 'v.id',
            'fecha'=>'v.fecha',
            'origen'=>'v.origen',
            'destino'=>'v.destino',
            'valorCompra'=>'v.valorCompra',
            'valorVenta'=>'v.valorVenta',
            'peso' => 'v.peso',
            'cantidad'=>'v.cantidad',
            'conductor' =>'co.nombre',
            'cliente' => 'cl.cliente',
            'vehiculo' =>'ve.placa',
            'tipoPr' => 'tp.tipo'
        );

		///Aplicamos filtros
	    $request=$this->get('request');
	    if ( $request->get('_search') && $request->get('_search') == "true" && $request->get('filters') )
            {
                    $f=$request->get('filters');
                    $f=json_decode(str_replace("\\","",$f),true);
                    $rules=$f['rules'];
                    foreach($rules as $rule){
                            $searchField=$fields[$rule['field']];
                            $searchString=$rule['data'];
                            if($rule['field']=='fechaCreacion'){
                            $daterange=explode("|", $searchString);
                            if(count($daterange)==1){
                            	$dateValue="'".trim(str_replace(" ","",$daterange[0]))."'";
	                            $qb->andWhere($searchField." LIKE '".$dateValue."%'");
                            }else{
                            	$minValue="'".trim(str_replace(" ","",$daterange[0]))." 00:00:00'";
                            	$maxValue="'".trim(str_replace(" ","",$daterange[1]))." 23:59:59'";
	                            $qb->andWhere($qb->expr()->between($searchField,$minValue , $maxValue));
                            }

                            }else{
                                if("null"!=$searchString){
                                	$qb->andWhere($qb->expr()->like($searchField, $qb->expr()->literal("%".$searchString."%")));
                                }
                            }
                    }

            }


	    //Ordenamiento de columnas
	    //sidx	id
		//sord	desc
		$sidx=$this->get('request')->query->get('sidx', 'id');
		$sord=$this->get('request')->query->get('sord', 'DESC');
		$qb->orderBy($fields[$sidx],$sord);
		//die($qb->getQuery()->getSQL());

	    $query=$qb->getQuery()->getResult();
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
		    $query,
		    $this->get('request')->query->get('page', 1)/*page number*/,
		   $this->get('request')->query->get('rows', 10)/*limit per page*/
		);
        /*return array(
            'entities' => $entities,
            'pagination'=>$pagination
        );*/
        $response= new Response();
        $pdata=$pagination->getPaginationData();
        $r=array();
        $r['records']=count($query);
        $r['page']=$this->get('request')->query->get('page', 1);
        $r['rows']=array();
        $r['total'] = $pdata['pageCount'];

        foreach($pagination as $row){
	        $line=$row;
	      	$r['rows'][]=$line;
        }
        $response->setContent(json_encode($r));
        return $response;
    }
    /**
     * Creates a form to create a Viaje entity.
     *
     * @param Viaje $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Viaje $entity)
    {
        $form = $this->createForm(new ViajeType(), $entity, array(
            'action' => $this->generateUrl('viaje_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Viaje entity.
     *
     * @Route("/new", name="viaje_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Viaje();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Viaje entity.
     *
     * @Route("/{id}", name="viaje_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArmensaViajesBundle:Viaje')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Viaje entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Viaje entity.
     *
     * @Route("/{id}/edit", name="viaje_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArmensaViajesBundle:Viaje')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Viaje entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Viaje entity.
    *
    * @param Viaje $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Viaje $entity)
    {
        $form = $this->createForm(new ViajeType(), $entity, array(
            'action' => $this->generateUrl('viaje_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Viaje entity.
     *
     * @Route("/{id}", name="viaje_update")
     * @Method("PUT")
     * @Template("ArmensaViajesBundle:Viaje:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArmensaViajesBundle:Viaje')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Viaje entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('viaje_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Viaje entity.
     *
     * @Route("/{id}", name="viaje_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ArmensaViajesBundle:Viaje')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Viaje entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('viaje'));
    }

    /**
     * Creates a form to delete a Viaje entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('viaje_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Borrar'))
            ->getForm()
        ;
    }
}
