<?php

namespace App\Controller;



use App\Services\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @Route(path="/", name="tmdb_index")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'application' => [
                'name' => getenv('APP_PROJECT_NAME')
            ]
        ]);
    }

    /**
     * @Route(path="/authentication/token", name="tmdb_authentication")
     */
    public function connectToTmdb(
        TmdbService $tmdbService
    ): Response
    {
       $connect = $tmdbService->authenticationGuestSession();
       if (key_exists("success", $connect) && $connect['success']) {
           $response = $this->redirectToRoute('tmdb_movie_list');
       } else {
           $response = $this->redirectToRoute('tmdb_index');
       }

       return $response;
    }

    /**
     * @Route(path="/movie/list", name="tmdb_movie_list")
     */
    public function movieList(
        TmdbService $tmdbService
    ): Response
    {
        return $this->render('default/movieList.html.twig', [
            'items' => $tmdbService->getMoviesData()
        ]);
    }
}
