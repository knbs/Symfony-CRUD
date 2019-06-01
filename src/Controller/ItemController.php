<?php
namespace App\Controller;

use App\Entity\Items;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
	private function upload($file){
		$filename = $file->getClientOriginalName();
		$file->move('uploads', $filename);
		$path = "/uploads/$filename";
		return $path;
	}

	/**
	 * @Route("/api/item/new_edit", name="api_new_edit", methods={"POST"})
	 */
	public function new_edit(Request $request, EntityManagerInterface $em) {
		try {
			$file = $request->files->get("file");

			if ($id = $request->request->get('id')){
				$item = $em->getRepository(Items::class)->find($id);
				if (!$item) {
					throw $this->createNotFoundException('No Item found for id '. $id ); 
				}
				if (!empty($file)) {
					$path = $this->upload($file);
					$item->setPath($path);
				}
			}else{
				$item = new Items();
				$path = $this->upload($file);
				$item->setPath($path);
			}
			
			$item->setName($request->request->get('name'));
			$item->setSds($request->request->get('sds'));
			$item->setManufacturer($request->request->get('manufacturer'));
			$em->persist($item);
			$em->flush();

			return new Response('Items updated!!!');
        } catch (FileException $e){
            $this->logger->error('failed to upload: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
	}

	/**
	 * @Route("/api/item/remove", name="api_remove", methods={"POST"})
	 */
	public function remove(Request $request, EntityManagerInterface $em) {
		$id = $request->request->get('id');
		$item = $em->getRepository(Items::class)->find($id);

		if (!$item) {
			throw $this->createNotFoundException('No Item found for id '. $id ); 
		}

		$em->remove($item);
		$em->flush();

		return new Response('Item deleted!!!');
	}
}