<?php
// api/tmdb.php
require_once 'config.php';

class TMDB {
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct() {
        $this->apiKey = TMDB_API_KEY;
    }

    public function request($endpoint, $params = []) {
        $params['api_key'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function search($query, $type = 'movie') {
        return $this->request("/search/$type", ['query' => $query]);
    }

    public function getSeasonDetails($tvId, $seasonNumber) {
        return $this->request("/tv/$tvId/season/$seasonNumber");
    }


    public function getTrending($type = 'movie', $timeWindow = 'week') {
        return $this->request("/trending/$type/$timeWindow");
    }

    public function getDetails($id, $type = 'movie') {
        return $this->request("/$type/$id", ['append_to_response' => 'credits,similar,videos']);
    }

    public function getPopular($type = 'movie', $page = 1) {
        return $this->request("/$type/popular", ['page' => $page]);
    }

    public function getTopRated($type = 'movie', $page = 1) {
        return $this->request("/$type/top_rated", ['page' => $page]);
    }

    public function getDiscover($type = 'movie', $params = []) {
        return $this->request("/discover/$type", $params);
    }

    public function getImageUrl($path, $size = 'original') {
        if (!$path) return ''; // placeholder logic here
        return "https://image.tmdb.org/t/p/$size$path";
    }
}
?>
