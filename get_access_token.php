<?php


require_once 'vendor/autoload.php'; 


function get_access_token($JSON_file_path)
{
    $client = new Google_Client();
    try {
        $client->setAuthConfig($JSON_file_path);
        $client->addScope(Google_Service_FirebaseCloudMessaging::CLOUD_PLATFORM);

        $savedTokenJson = readSavedToken();

        if ($savedTokenJson) {
            // the token exists, set it to the client and check if it's still valid
            $client->setAccessToken($savedTokenJson);
            $accessToken = $savedTokenJson;
            if ($client->isAccessTokenExpired()) {
                // the token is expired, generate a new token and set it to the client
                $accessToken = generateToken($client);
                $client->setAccessToken($accessToken);
            }
        } else {
            // the token doesn't exist, generate a new token and set it to the client
            $accessToken = generateToken($client);
            $client->setAccessToken($accessToken);
        }
        

        $oauthToken = $accessToken["access_token"];

        return $oauthToken;

        
    } catch (Google_Exception $e) {}
   return false;
}

//Using a simple file to cache and read the toke, can store it in a databse also
function readSavedToken() {
  $tk = @file_get_contents('token.cache');
  if ($tk) return json_decode($tk, true); else return false;
}
function writeToken($tk) {
 file_put_contents("token.cache",$tk);
}

function generateToken($client)
{
    $client->fetchAccessTokenWithAssertion();
    $accessToken = $client->getAccessToken();

    $tokenJson = json_encode($accessToken);
    writeToken($tokenJson);

    return $accessToken;
}