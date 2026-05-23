<?php

namespace Drupal\google_gemini_ai\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class GeminiService {

  protected $httpClient;

  public function __construct(
    ClientInterface $httpClient
  ) {
    $this->httpClient = $httpClient;
  }

  public function generateContent($prompt) {

    try {

      $api_key='AIzaSyDM6fXJc85onXKf_YoyCYZRWWUnsWg6TDw';

      // model change
      $url='https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='
      .$api_key;

      $response=
      $this->httpClient->post(
        $url,
        [

          'headers'=>[
            'Content-Type'=>
            'application/json'
          ],

          'json'=>[
            'contents'=>[
              [
                'parts'=>[
                  [
                    'text'=>$prompt
                  ]
                ]
              ]
            ]
          ],

          'timeout'=>30

        ]
      );

      $result=
      json_decode(
        $response->getBody(),
        TRUE
      );

      return
      $result['candidates'][0]
      ['content']['parts'][0]
      ['text']
      ?? '';

    }
    catch(RequestException $e){

      \Drupal::logger(
        'google_gemini_ai'
      )->error(
        $e->getMessage()
      );

      // AJAX crash mat hone do
      return 'Gemini API quota exceeded. Create new API key or try later.';
    }

  }

}