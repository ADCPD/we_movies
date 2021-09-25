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
            'application' => [
                'name' => getenv('APP_PROJECT_NAME')
            ],
            'items' => $tmdbService->getMoviesData()
        ]);
    }

    /**
     * @Route(path="/movies", name="tmdb_movies_list_json", methods={"GET","HEAD"}))
     *
     * @param TmdbService $tmdbService
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMovies(
        TmdbService $tmdbService
    ){
        return $this->json($tmdbService->getTopRatedMovies());
    }

    /**
     * @Route(path="/movies/genres", name="tmdb_movie_gender_list_json", methods={"GET","HEAD"}))
     **/
    public function getMovieGendre(
        TmdbService $tmdbService
    )
    {
        return $this->json($tmdbService->getMoviesGender());
    }

    /**
     * @Route(path="/movie/genre/{id}", name="tmdb_movie_list_by_gender_json", methods={"GET","HEAD"}))
     **/
    public function movieByGenre(
        int $id,
        TmdbService $tmdbService
    )
    {
        return $this->json($tmdbService->getMovieByGenre($id));
    }

    /**
     * @Route(path="/movie/{id}/streaming", name="tmdb_movie_stream_json", methods={"GET","HEAD"}))
     * @param int $id
     * @param TmdbService $tmdbService
     */
    public function getStreamMovieData(
        int $id,
        TmdbService $tmdbService
    )
    {
        return $this->json($tmdbService->getSteamMovieData($id));
    }
}
