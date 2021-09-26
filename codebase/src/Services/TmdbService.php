<?php

namespace App\Services;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class TmdbService
 * @package App\Services
 */
class TmdbService
{
    private $apiKey ;
    private $kernel;
    private $client;
    private $movies = [];
    private $gender = [];

    public function __construct(
        KernelInterface $kernel,
        HttpClientInterface $client
    )
    {
        $this->apiKey = $_ENV['TMDB_API_KEY'];
        $this->kernel = $kernel;
        $this->client = $client;
    }

    /**
     * @param int $genreId
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getMovieByGenre(int $genreId): array
    {
        $requestPath = "https://api.themoviedb.org/3/discover/movie?api_key=" . $this->apiKey . "&with_genres=" . $genreId;
        $apiConnect = $this->authenticationGuestSession();
        if ($apiConnect['success']) {
            $data = $this->getClientRequest('GET', $requestPath);
            if ($data->getStatusCode() === 200) {
                return json_decode($data->getContent(), true);
            }
        }
    }

    public function getMoviesData(): array
    {
        // check api connexion
        $apiConnect = $this->authenticationGuestSession();

        if ($apiConnect['success']) {
            $genders = $this->getMoviesGender();
            $movies = $this->getTopRatedMovies();
            $topMovie = $this->getTopOneMovies();
        }

        return [
            "top"       => $topMovie ? $topMovie : [],
            "genders"   => $genders ? $genders : [],
            "movies"    => $movies ? $movies : []
        ];
    }

    public function getTopRatedMovies()
    {
        $requestPath = "https://api.themoviedb.org/3/movie/top_rated?api_key=" . $this->apiKey . "&language=en-US";
        $data = $this->getClientRequest('GET', $requestPath);
        if ($data->getStatusCode() === 200) {
            $this->movies = json_decode($data->getContent(), true);
            return $this->movies ;
        } else {
            return [
                'status' => 404,
                'message' => 'Movies data not found'
            ];
        }
    }

    /**
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getTopOneMovies() : array
    {
        $requestPath = "https://api.themoviedb.org/3/movie/top_rated?api_key=" . $this->apiKey . "&language=en-US&page=1";
        $data = $this->getClientRequest('GET', $requestPath);
        if ($data->getStatusCode() === 200) {
           $movie = json_decode($data->getContent(), true);
           if ($movie){
               $movieId = $movie['results'][0]['id'];
               $videoMovieData = $this->getVideos($movieId);
               return [
                   "movie_id"    => $movieId,
                   "movie_key"   => $videoMovieData['results'][0]['key'],
                   "movie_streaming_path" => $this->getYoutubeLink($videoMovieData['results'][0]['key'])
               ];
           }
        } else {
            return [
                'status' => 404,
                'message' => 'Top movie data not found'
            ];
        }
    }

    /**
     * @param string $movieID
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getSteamMovieData(string $movieID)
    {
        $requestPath = "https://api.themoviedb.org/3/movie/{$movieID}?api_key=" . $this->apiKey . "&language=en-US";
        $data = $this->getClientRequest('GET', $requestPath);

        if ($data->getStatusCode() === 200) {
            $movie = json_decode($data->getContent(), true);
            if ($movie) {
                $videoMovieData = $this->getVideos($movieID);
                return [
                    "status"                => $data->getStatusCode(),
                    "movie_id"              => $movieID,
                    "movie_key"             => $videoMovieData['results'][0]['key'],
                    "movie_streaming_path"  => $this->getYoutubeLink($videoMovieData['results'][0]['key']),
                ];
            }
        } else {
            return [
                'status' => 404,
                'message' => "Movie ID#{$movieID} not found"
            ];
        }
    }

    public function getVideos(int $movieId)
    {
        $movieRequestPath = "https://api.themoviedb.org/3/movie/283566/videos?api_key=" . $this->apiKey . "&language=en-US";
        $topMovieData = $this->getClientRequest('GET', $movieRequestPath);

        if ($topMovieData->getStatusCode() === 200) {
            return json_decode($topMovieData->getContent(), true);
        } else {
            return [
                'status' => 404,
                'message' => 'Top movie data not found'
            ];
        }
    }

    /**
     * @param string $code
     * @return string
     */
    private function getYoutubeLink(string $code): string
    {
        return "https://www.youtube.com/embed/{$code}";
    }
    /**
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getMoviesGender(): array
    {
        $requestPath = "https://api.themoviedb.org/3/genre/movie/list?api_key=" . $this->apiKey . "&language=en-US";

        $data = $this->getClientRequest('GET', $requestPath);
        if ($data->getStatusCode() === 200) {
            $this->gender = json_decode($data->getContent(), true);
            return $this->gender;
        } else {
            return  [
                'status' => 404,
                'message' => 'Gender movies data not found'
            ];
        }
    }

    /**
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function authenticationGuestSession(): array
    {
        $requestPath = "https://api.themoviedb.org/3/authentication/guest_session/new?api_key=" . $this->apiKey;
        $response = $this->getClientRequest('GET', $requestPath);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getContent(), true);
        }

        return [
            'status'  => $response->getStatusCode(),
            'message' => "Connexion no etablished"
        ];
    }

    public function getMovieById(int $movieId)
    {
        $requestPath = "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$this->apiKey}&language=en-US";
        $response = $this->getClientRequest('GET', $requestPath);
        if ($response->getStatusCode() === 200) {
           $movie = json_decode($response->getContent(), true);

           if ($this->getSteamMovieData($movieId)['status'] === 200) {
               $movie['movie_streaming_path'] = $this->getSteamMovieData($movieId)['movie_streaming_path'];
           }

           return $movie;
        }
        return [
            'status'  => $response->getStatusCode(),
            'message' => "Movie no found"
        ];
    }

    /**
     * @param string $method
     * @param string $path
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getClientRequest(string $method, string $path)
    {
        return $this->client->request($method, $path);
    }
}
