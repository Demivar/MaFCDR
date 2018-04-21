<?php

namespace BM2\SiteBundle\Controller;

use BM2\SiteBundle\Entity\House;
use BM2\SiteBundle\Entity\Character;

use BM2\SiteBundle\Form\HouseBackgroundType;

use BM2\SiteBundle\Service\Geography;
use BM2\SiteBundle\Service\History;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/house")
 */
class HouseController extends Controller {

	private $house;

	/**
	  * @Route("/{id}", name="bm2_house", requirements={"id"="\d+"})
	  * @Template("BM2SiteBundle:House:house.html.twig")
	  */
	
	public function viewAction(House $id) {
		$house = $id;
		$inhouse = false;
		
		$character = $this->get('appstate')getCharacters(false, true, true);
		if ($character) {
			if ($character->getHouse() == $house) {
				$inhouse = true;
			}
		}
	}	

	/**
	  * @Route("/create")
	  * @Template
	  */	
	
	public function createAction(Request $request) {
		$character = $this->get('appstate')->getCharacter(true, true, true);
		$em = $this->getDoctrine()->getManager();
		$location = null;
		$inside = null;
		$crest = $character->getCrest();
		if ($character->getInsideSettlement()) {
			$settlement = $character->getInsideSettlement();
		} else {
			$location = $character->getLocation();
		}
		
		$form = $this->createForm(new HouseBackgroundType($house);
		$form->handleRequest($request);
		if ($character->getHouse() > 0 ) {
			throw createNotFoundException('error.found.house');
		}
		if ($form->isValid()) {
			// FIXME: this causes the (valid markdown) like "> and &" to be converted - maybe strip-tags is better?;
			// FIXME: need to apply this here - maybe data transformers or something?
			// htmlspecialchars($data['subject'], ENT_NOQUOTES);
			if ($character->getCrest()); {
				$crest = $character->getCrest();
			}
			$house = $this->get('house_manager')->create($data['name'], $data['description'], $data['private_description'], $data['secret_description'], null, $location, $settlement, $crest, $character);
			$em->flush();
			$this->addFlash('notice', $this->get('translator')->trans('house.updated.created', array(), 'actions'));
		}
		return array(
			'form' => $form->createView(),
		);
	}	
	
	/**
	  * @Route("/{id}/edit", requirements={"id"="\d+"})
	  * @Template
	  */
		
	public function editAction(Request $request) {
		$character = $this->get('appstate')->getCharacter(true, true, true);
		$house = $id;
		$em = $this->getDoctrine()->getManager();

		$form = $this->createForm(new HouseBackgroundType($house);
		$form->handleRequest($request);
		if ($character != $house->getHead()) {
			throw createNotFoundException('error.noaccess.nothead');
		}
		if ($form->isValid()) {
			// FIXME: this causes the (valid markdown) like "> and &" to be converted - maybe strip-tags is better?;
			// FIXME: need to apply this here - maybe data transformers or something?
			// htmlspecialchars($data['subject'], ENT_NOQUOTES);
			$em->flush();
			$this->addFlash('notice', $this->get('translator')->trans('house.updated.background', array(), 'actions'));
		}
		return array(
			'form' => $form->createView(),
		);
	}
					  
	/**
	  * @Route("/{id}/join", requirements={"id"="\d+"})
	  * @Template
	  */
					  
	public function joinAction(Request $request) {
		$house = $id;
		$hashouse = FALSE;
		$character = $this->get('appstate')->getCharacter(true, true, true);
		
		$em = $this->getDoctrine()->getManager();
		if ($character->getInsideSettlement()->getHouses() != $house) {
			throw createNotFoundException('error.notfound.nohouse');
		} 
		if ($character->getInsideSettlement()) {
			$form = $this->createForm(new HouseJoinType($house);
			$form->handleRequest($request);
		} else {
			$location = $this->getCharacter()->getLocation();
			$nearest = $this->geography->findNearestHouse($character);
			if($nearest['distance'] < $this->geography->caluclateInteractionDistance($character)) {
				$form = $this->createForm(new HouseJoinType($nearest);
				$form->handleRequest($request);
			} else throw createNotFoundException('error.notfound.toofar');
		}
		if ($form->isValid()) {
			$em->flush();
			$this->addFlash('notice', $this->get('translator')->trans('house.member.join', array(), 'actions'));
		}
		return array(
			'form' => $form->createView(),
		);
	}

	/**
	  * @Route("/{id}/approve", requirements={"id"="\d+"})
	  * @Template
	  */
	
	public function approveAction(Request $request) {
		$house = $id;
		$character = $this->get('appstate')->getCharacter(true, true, true);
		$em = $this->getDoctrine()->getManager();
		if ($character->getHouse() < 1) {
			throw createNotFoundException('error.noaccess.nohouse')
		}
		if ($character->getHouse()->getHead() !== $house->getHead()) {
			throw createNotFoundException('error.noaccess.nothead');
		}
		$form = $this->createForm(new HouseApproveType($house);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$em->flush();
			$this->addFlash('notice', $this->get('translator')->trans('house.member.approve', array(), 'actions'));
		}
		return array(
			'form' => $form->createView(),
		);
	}
