<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$user = $_GET['user'] ?? null;
if (!$user) {
  echo json_encode(["error" => "Missing username"]);
  exit;
}

$cacheFile = __DIR__ . "/cache_$user.json";
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 3 * 3600) {
  echo file_get_contents($cacheFile);
  exit;
}

$url = "https://leetcode.com/graphql";
$query = <<<GQL
{
  matchedUser(username: "$user") {
    username
    profile {
      realName
      ranking
      reputation
      countryName
      userAvatar
    }
    submitStats {
      acSubmissionNum {
        difficulty
        count
        submissions
      }
    }
  }
}
GQL;

$payload = json_encode(['query' => $query]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_USERAGENT => 'LeetCodeStatsBot/1.0 (+https://wherepanda.xyz)',
]);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
  echo json_encode(["error" => "Could not connect to LeetCode"]);
  exit;
}

$data = json_decode($response, true);
$userData = $data['data']['matchedUser'] ?? null;

if (!$userData) {
  echo json_encode(["error" => "User not found"]);
  exit;
}

$stats = [];
foreach ($userData['submitStats']['acSubmissionNum'] as $s) {
  $stats[strtolower($s['difficulty'])] = [
    "solved" => $s['count'],
    "submissions" => $s['submissions']
  ];
}

$result = [
  "username" => $userData['username'],
  "name" => $userData['profile']['realName'],
  "avatar" => $userData['profile']['userAvatar'],
  "ranking" => $userData['profile']['ranking'],
  "country" => $userData['profile']['countryName'],
  "reputation" => $userData['profile']['reputation'],
  "stats" => $stats
];

file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
