<?php

namespace App\Controller;

use App\Entity\Items;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, EntityManagerInterface $em) {
    	$page = (int)$request->query->get("page")?: 1;
    	$page = $page < 1 ? 1 : $page;
        $limit = 5;
        $offset = $limit * ($page - 1);

    	$repository = $em->getRepository(items::class);
        $count = (int)$repository->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $pages = ceil($count/$limit);

        $data = [
        	"items" => $repository->findBy([], [], $limit, $offset),
        	"pages" => $pages
        ];

    	return $this->render('default/index.html.twig', $data);
    }

    /**
	 * @Route("/new", name="new")
	 */
	public function new() {
		$data = [
			"id" => '',
			"name" => '',
			"path" => '',
			"manufacturer" => '',
			"sds" => '',
		];
		return $this->render('default/form.html.twig', $data);
	}

	/**
	 * @Route("/edit/{id}", name="edit")
	 */
	public function edit(EntityManagerInterface $em, SerializerInterface $serializer, $id) {
		$repository = $em->getRepository(Items::class);
		$item = json_decode($serializer->serialize($repository->findBy(['id' => $id])[0], 'json'), true);
		return $this->render('default/form.html.twig', $item);
	}

	/**
	 * @Route("/delete/{id}", name="delete")
	 */
	public function delete(EntityManagerInterface $em, SerializerInterface $serializer, $id) {
		$repository = $em->getRepository(Items::class);
		$item = json_decode($serializer->serialize($repository->findBy(['id' => $id])[0], 'json'), true);
		return $this->render('default/delete.html.twig', $item);
	}	
}

