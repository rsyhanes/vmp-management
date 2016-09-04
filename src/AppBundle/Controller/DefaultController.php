<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/{date}/all", name="all")
     *
     * @param $date string
     * @return Response
     */
    public function allAction($date = '')
    {
        $previous = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date);
        $date = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date)->add(new \DateInterval('PT24H'));

        // For get going.. move this Db logic to service layer
        $results = $this->orderedByManufacturer($date);

        return $this->render('default/all.html.twig', array(
            'previous' => $previous->format('m-d-Y'),
            'ddate' => $date->format('m-d-Y'),
            'needs' => $results
        ));
    }

    /**
     * @Route("/{date}/by_manufacturer/{manufacturer}", name="by_manufacturer")
     *
     * @param $date string
     * @param $manufacturer int
     * @return Response
     */
    public function byManufacturerAction(Request $request, $date = '', $manufacturer = 0)
    {
        $previous = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date);
        $manufacturer = ($request->isMethod('POST')) ? $request->request->get('manufacturer_id') : $manufacturer;
        $date = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date)->add(new \DateInterval('PT24H'));

        $response = array();

        if (empty($manufacturer)) {
            $response['needs'] = null;
            $response['manufac'] = null;
        } else {
            $products = $this->productsByManufacturer($date, $manufacturer);
            $name = $this->getManufacturerName($manufacturer);

            $response['needs'] = $products;
            $response['manufac'] = $name;
        }
        $response['previous'] = $previous->format('m-d-Y');
        $response['ddate'] = $date->format('m-d-Y');
        $response['manufacturers'] = $this->getManufacturers();

        return $this->render('default/by_manufac.html.twig', $response);
    }

    /**
     * @Route("/{date}/by_manufacturer_per_order/{manufacturer}", name="by_manufacturer_per_order")
     *
     * @param $date string
     * @param $manufacturer int
     * @return Response
     */
    public function byManufacturerPerOrderAction(Request $request, $date = '', $manufacturer = 0)
    {
        $manufacturer = ($request->isMethod('POST')) ? $request->request->get('manufacturer_id') : $manufacturer;
        $previous = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date);
        $date = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date)->add(new \DateInterval('PT24H'));

        $response = array();

        if (empty($manufacturer)) {
            $response['needs'] = null;
            $response['manufac'] = null;
        } else {
            $products = $this->productsByManufacturerWithOrder($date, $manufacturer);
            $name = $this->getManufacturerName($manufacturer);

            $response['needs'] = $products;
            $response['manufac'] = $name;
        }
        $response['previous'] = $previous->format('m-d-Y');
        $response['ddate'] = $date->format('m-d-Y');
        $response['manufacturers'] = $this->getManufacturers();

        return $this->render('default/by_manufac_per_order.html.twig', $response);
    }

    /**
     * @Route("/{date}/labels", name="labels")
     *
     * @param $date string
     * @return Response
     */
    public function labelAction(Request $request, $date = '')
    {
        $previous = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date);
        $date = (empty($date)) ? new \DateTime() : \DateTime::createFromFormat('m-d-Y', $date)->add(new \DateInterval('PT24H'));

        // For get going.. move this Db logic to service layer
        $results = $this->ordersForLabels($date);

        return $this->render('default/labels.html.twig', array(
            'previous' => $previous->format('m-d-Y'),
            'ddate' => $date->format('m-d-Y'),
            'needs' => $results
        ));
    }

    /**
     * @param $mId
     * @return string
     */
    private function getManufacturerName($mId)
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT `oc_manufacturer`.`name` FROM `oc_manufacturer` WHERE `oc_manufacturer`.`manufacturer_id` = ?');
        $stmt->bindValue(1, (int) $mId);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();
        return !empty($results) ? $results[0]['name'] : '';
    }

    /**
     * @param $date
     * @param $manufacturer
     * @return mixed
     */
    private function productsByManufacturer($date, $manufacturer)
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('CALL order_product_filtered_by_manufac(?, ?)');
        $stmt->bindValue(1, $date->format('Y-m-d'));
        $stmt->bindValue(2, (int) $manufacturer);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();

        return $results;
    }

    /**
     * @param $date
     * @param $manufacturer
     * @return mixed
     */
    private function productsByManufacturerWithOrder($date, $manufacturer)
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('CALL order_productq_ordered_by_product(?, ?)');
        $stmt->bindValue(1, $date->format('Y-m-d'));
        $stmt->bindValue(2, (int) $manufacturer);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();

        return $results;
    }

    /**
     * @param $date
     * @return mixed
     */
    private function orderedByManufacturer($date)
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('CALL order_productq_by_manufac(?)');
        $stmt->bindValue(1, $date->format('Y-m-d'));
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @param $date
     * @return mixed
     */
    private function ordersForLabels($date)
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('CALL orders_for_labels(?)');
        $stmt->bindValue(1, $date->format('Y-m-d'));
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @return mixed
     */
    private function getManufacturers()
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM `oc_manufacturer`');
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();
        return $results;
    }
}
