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

    public function getMoviesData(): array
    {
        // check api connexion
        $apiConnect = $this->authenticationGuestSession();

        if ($apiConnect['success']) {
            $genders = $this->getMoviesGender();
            $movies = $this->getTopRatedMovies();
        }

        return [
            'genders' => $genders ? $genders : [],
            "movies" => $movies ? $movies : []
        ];
    }

    public function getTopRatedMovies() : array
    {
        $requestPath = "https://api.themoviedb.org/3/movie/top_rated?api_key=" . $this->apiKey . "&language=en-US";
        $data = $this->getClientRequest('GET', $requestPath);
        if ($data->getStatusCode() === 200) {
            $this->movies = json_decode($data->getContent(), true);
            return $this->movies;
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
    private function getMoviesGender(): array
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

    /**
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function authenticationRequestToken(): array
    {
        $requestPath = "https://api.themoviedb.org/3/authentication/token/new?api_key=" . $this->apiKey;
        $response = $this->getClientRequest('GET', $requestPath);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getContent(), true);
        }

        return [
            'status'  => $response->getStatusCode(),
            'message' => "Error to generate token"
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
