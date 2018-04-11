<?php
/**
 * Created by PhpStorm.
 * User: m.ogeret
 * Date: 09/04/2018
 * Time: 13:07
 */

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Service\LinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LinkController extends Controller
{
    public function linkHandler($uuid)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Link $link */
        $link = $em->getRepository(Link::class)->findOneByUuid($uuid);

        if (is_null($link)) {
            return $this->redirectToRoute('app_main_route');
        }

        $link->setCount($link->getCount()+1);
        $em->persist($link);
        $em->flush();

        return $this->redirect($link->getURL());
    }
}
