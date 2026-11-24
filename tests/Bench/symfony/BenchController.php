<?php declare(strict_types=1);
/**
 * Symfony Benchmark Controller
 *
 * Serve the three HTTP workloads from a Symfony skeleton with Twig installed.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace App\Controller;

/**
 * Dependances
 */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Benchmark Controller
 *
 * Match the CrazyPHP route and response contracts using Symfony's HTTP kernel.
 */
final class BenchController extends AbstractController {

    /** Public Methods
     ******************************************************
     */

    /**
     * Ping
     *
     * Send a fixed plaintext body.
     *
     * @return Response
     */
    #[Route("/ping", methods: [
        "GET"
    ])]
    public function ping():Response {

        # Define the plaintext response
        $result = new Response("ok", 200, [
            "Content-Type"    =>  "text/plain"
        ]);

        # Return the response
        return $result;

    }

    /**
     * User
     *
     * Serialize the captured path parameter.
     *
     * @param int $id Matched identifier
     * @return JsonResponse
     */
    #[Route("/users/{id<\d+>}", methods: [
        "GET"
    ])]
    public function user(int $id):JsonResponse {

        # Encode the matched numeric identifier
        $result = new JsonResponse([
            "id"    =>  $id
        ]);

        # Return the JSON response
        return $result;

    }

    /**
     * Catalog
     *
     * Render the same list and item partial through Twig.
     *
     * @return Response
     */
    #[Route("/catalog", methods: [
        "GET"
    ])]
    public function catalog():Response {

        # Prepare and render the numeric catalog
        $result = $this->render("catalog.html.twig", [
            "items"    =>  range(1, 100)
        ]);

        # Return the rendered list
        return $result;

    }

}
