<?php

// Script to generate IBM Watson Keywords, Concepts, Categories and Entities, alongwith emotions and sentiments, using the the text stored in Mongo for HISTORY TOPICS.
// Author: Abhishek Neralla
// Date: 11/13/2017
// Version: 1





// *** MPX Access Credentials **//

$username = "abhishek.neralla@aetn.com";
$password = "Letmein2";

// ********* //




function signIn($username, $password){
  $ch = curl_init('https://identity.auth.theplatform.com/idm/web/Authentication/signIn?schema=1.0&form=json');

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
  $result = curl_exec($ch);

  curl_close($ch);

  $json_result = json_decode($result);

  $tken = $json_result->signInResponse->token;

  return $tken;
}


function post_to_mpx($token, $post_data, $type){

  //$account = 'http://access.auth.theplatform.com/data/Account/2703446663'; // NWSL-DEv
  $account = 'http://access.auth.theplatform.com/data/Account/2703446073'; // NWSL-PROD

  // $post = (object) [
  //   '$xmlns' => ['AETN' => "http://theplatform.aetn.com/"],
  //   'id' => 'http://data.media.theplatform.com/media/data/Media/'.$media_id,
  //   'title' => $title,
  //   'AETN$mlWatson' => $watson_resp
  // ];

  //$post_json = json_encode($post_data);

  //print "Full Post is: \n";
  //print $post_data;

  if ($type == "MO"){
    $url = 'http://data.media2.theplatform.com/media/data/Media?schema=1.10.0&searchSchema=1.0.0&form=cjson&pretty=true&token='.$token.'&account='.$account;
  } elseif ($type == "MF") {
    $url = 'http://fms2.theplatform.com/web/FileManagement?token='.$token.'&account='.$account.'&schema=1.5';
  }


  //print("Posting URL is: ".$url);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json'));


    //print("Curl init is : \n");
    //print_r($ch);
    print "post body: \n";
    print($post_data);

    $result = curl_exec($ch);
    print "Result of POST is: \n";
    print $result."\n";
    return $result;
    curl_close($ch);
  }


function getTeamName($guid){
  $team_name = "na";
  $teams_endpoint = json_decode(file_get_contents('http://d2x8k3ml6asc4u.cloudfront.net/data/teams/'.$guid), true);
  if ($teams_endpoint['response']['team']['abbreviationName']) {
    $team_name = $teams_endpoint['response']['team']['abbreviationName'];
  }
  return $team_name;
}



  ###### END - FUNCTIONS USED IN THIS PROGRAM ######


    $token = signIn($username, $password);

    //$topics = file_get_contents('history-topics.json');
    // $ss_data = json_decode(file_get_contents('http://d2x8k3ml6asc4u.cloudfront.net/videos/'), true);
    $ss_data = json_decode(utf8_encode(file_get_contents('./all_videos.json')), true);
    $ss_response = $ss_data['response'];

    //print_r($ss_response);
    //$ss_response_arr = (array)($ss_response);





// $awayTeam = 'not available';
// $homeTeam = 'not available';


$count = 0;
    foreach ($ss_response as $ss) {
      $homeTeamOptaID = '';
      $awayTeamOptaID = '';
      //$playerTags = '';
      $matchOptaID = '';
      $SSVideoTag = '';
      $SSIsReplay = '';
      //print_r($ss)."\n";
      //$mediaGUID = $ss['uvid'];
      print("******* Count = ".$count."********\n");
      $title = $ss['title'];
      $airdate = strtotime($ss['created']);
      if ($ss['metadata']){
        $homeTeamOptaID = $ss['metadata']['home_team'];
        $awayTeamOptaID = $ss['metadata']['away_team'];
      //  $teams_endpoint_away = json_decode(file_get_contents('http://d2x8k3ml6asc4u.cloudfront.net/data/teams/'+$awayTeamOptaID), true);
        $awayTeam = getTeamName($awayTeamOptaID);
        $homeTeam = getTeamName($homeTeamOptaID);

        $playerTags_tmp = $ss['metadata']['player_tags'];
        $playerTags = explode(",", $playerTags_tmp);
        //$playerTags = array_map('utf8_encode', $playerTags_tmp_arr);

        print("Player Tags: \n");
        print_r($playerTags)."\n";
      } else {
        $awayTeam = 'na';
        $homeTeam = 'na';
      }
      if ($ss['metadata']['is_replay']){
        $SSIsReplay = $ss['metadata']['is_replay'];
        if ($ss['metadata']['is_replay'] == 'true'){
          $categories = [array('name' => "Game")];
        }

      }
      $description = $ss['description'];
      //$categories = [array('name' => "Game")];


      if ($ss['metadata']['match_id']){
        $matchOptaID = $ss['metadata']['match_id'];
      }

      if ($ss['metadata']['video_tag']){
        $SSVideoTag = $ss['metadata']['video_tag'];
      }

      $mediaGUID = "MM".$ss['uvid'];
      $USStreamingPartner = "go90";

      $media_streaming_url = $ss['streams'];
      $media_thumbnail_url = $ss['thumbnail'];

      $post_data_mediaObject = json_encode(array(
        '$xmlns' => ['AETN' => "http://theplatform.aetn.com/"],
        'title' => $title,
        'pubDate' => $airdate.'000',
        'AETN$homeTeamOptaID' => $homeTeamOptaID,
        'AETN$awayTeamOptaID' => $awayTeamOptaID,
        'AETN$awayTeam' => $awayTeam,
        'AETN$homeTeam' => $homeTeam,
        'AETN$SSIsReplay' => $SSIsReplay,
        'categories' => $categories,
        'AETN$playerTags' => $playerTags,
        //'AETN$playerTags_String' => (string) $playerTags,
        'AETN$matchOptaID' => $matchOptaID,
        'AETN$SSVideoTag' => $SSVideoTag,
        'guid' => $mediaGUID,
        'AETN$USStreamingPartner' => $USStreamingPartner

      ));

      //print("Post data : \n");
      //print_r($post_data_mediaObject);

      $post_object = post_to_mpx($token, $post_data_mediaObject, "MO");
      //print("Posting to MPX: \n");
      //print_r($post_object);

      $post_data_json = json_decode($post_object, true);

      $media_id_created = $post_data_json['id'];

      $post_data_mediaFile_m3u = json_encode(array('linkNewFile' => array('mediaId' => $media_id_created,
          'sourceUrl' => $media_streaming_url,
          'mediaFileInfo' => array('format' => 'M3U', 'contentType' => 'Video', 'transferInfo' => array('supportsStreaming' => 'true')))));

      $post_data_mediaFile_jpg = json_encode(array('linkNewFile' => array('mediaId' => $media_id_created,
              'sourceUrl' => $media_thumbnail_url,
              'mediaFileInfo' => array('format' => 'JPEG', 'contentType' => 'image', 'transferInfo' => array('supportsStreaming' => 'true')))));

      $post_files1 = post_to_mpx($token, $post_data_mediaFile_m3u, "MF");
      $post_files2 = post_to_mpx($token, $post_data_mediaFile_jpg, "MF");

      /// ***** PUBLISHING *****

      $profile_id = 'http://data.publish.theplatform.com/publish/data/PublishProfile/28099562';

      $publish_url = 'http://publish.theplatform.com/web/Publish/publish?_mediaId='.$media_id_created.'&_profileId='.$profile_id.'&schema=1.2&token='.$token.'&account=http%3A%2F%2Faccess.auth.theplatform.com%2Fdata%2FAccount%2F2703446663&form=json';

      $pub = file_get_contents($publish_url);
      //print("Result of Publish is: ".$pub."\n");


      $count++;

      // if ($count > 90) {
      //   break;
      // }

      }









    // foreach($ss_response as $key => $value) {
    //     print "$key => $value\n";
    // }
    // echo "\n";
      // $media_id = $ss['featured_video'];
      // $title = $ss['title'];
      // print ("Topic is: ").$title."\n";
      //$content = strip_tags($ss['content']);
      //print ("Content is: \n");
      //print $content;


  //     $post_data = json_encode(array('text' => $content,
  //     'features' => array('entities' => array('sentiment' => true, 'emotion' => true),
  //     'emotion' => array('document' => true),
  //     'sentiment' => array('document' => true),
  //     'concepts' => array('document' => true),
  //     'categories' => array('document' => true),
  //     'keywords' => array('sentiment' => true, 'emotion' => true, 'limit' => 20)))
  //   );
  //
  // ##### POSTING TEXT RETRIEVED ABOVE TO WATSON ####
  //
  //
  // $url = "https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27";
  //
  //
  // $ch = curl_init($url);
  //
  //
  // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // curl_setopt($ch, CURLOPT_USERPWD, "$bluemix_username:$bluemix_password");
  // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  //   'Content-Type: application/json',
  //   'Content-Length: ' . strlen($post_data))
  // );

//   $watson_resp = curl_exec($ch);
//   print "Watson Response is: \n";
//   print $watson_resp."\n";
// //   //$watson_resp = file_get_contents('watson_response');
//   $final_post_data = [];
//
//   $watson_resp_arr = json_decode($watson_resp, true);
//
//   for ($i = 0; $i < count($watson_resp_arr['keywords']); $i++) {
//   //for ($i = 0; $i < 2; $i++) {
//
//     $final_post_data['keywords'][$i]['text'] = $watson_resp_arr['keywords'][$i]['text'];
//
//     $final_post_data['keywords'][$i]['score'] =  $watson_resp_arr['keywords'][$i]['relevance'];
//     $final_post_data['keywords'][$i]['sentiment'] = $watson_resp_arr['keywords'][$i]['sentiment']['score'];
//     $final_post_data['keywords'][$i]['emotion']['sadness'] = $watson_resp_arr['keywords'][$i]['emotion']['sadness'];
//     $final_post_data['keywords'][$i]['emotion']['joy'] = $watson_resp_arr['keywords'][$i]['emotion']['joy'];
//     $final_post_data['keywords'][$i]['emotion']['fear'] = $watson_resp_arr['keywords'][$i]['emotion']['fear'];
//     $final_post_data['keywords'][$i]['emotion']['disgust'] = $watson_resp_arr['keywords'][$i]['emotion']['disgust'];
//     $final_post_data['keywords'][$i]['emotion']['anger'] = $watson_resp_arr['keywords'][$i]['emotion']['anger'];
//
//   }
//
//   $final_post_data['sentiment'] = $watson_resp_arr['sentiment']['document']['score'];
//   $final_post_data['emotion']['sadness'] = $watson_resp_arr['emotion']['document']['emotion']['sadness'];
//   $final_post_data['emotion']['joy'] = $watson_resp_arr['emotion']['document']['emotion']['joy'];
//   $final_post_data['emotion']['fear'] = $watson_resp_arr['emotion']['document']['emotion']['fear'];
//   $final_post_data['emotion']['disgust'] = $watson_resp_arr['emotion']['document']['emotion']['disgust'];
//   $final_post_data['emotion']['anger'] = $watson_resp_arr['emotion']['document']['emotion']['anger'];
//
//   for ($i = 0; $i < count($watson_resp_arr['entities']); $i++) {
//     $final_post_data['entities'][$i]['type'] = $watson_resp_arr['entities'][$i]['type'];
//     $final_post_data['entities'][$i]['text'] = $watson_resp_arr['entities'][$i]['text'];
//     $final_post_data['entities'][$i]['score'] = $watson_resp_arr['entities'][$i]['relevance'];
//     $final_post_data['entities'][$i]['sentiment'] = $watson_resp_arr['entities'][$i]['sentiment']['score'];
//     $final_post_data['entities'][$i]['emotion']['sadness'] = $watson_resp_arr['entities'][$i]['emotion']['sadness'];
//     $final_post_data['entities'][$i]['emotion']['joy'] = $watson_resp_arr['entities'][$i]['emotion']['joy'];
//     $final_post_data['entities'][$i]['emotion']['fear'] = $watson_resp_arr['entities'][$i]['emotion']['fear'];
//     $final_post_data['entities'][$i]['emotion']['disgust'] = $watson_resp_arr['entities'][$i]['emotion']['disgust'];
//     $final_post_data['entities'][$i]['emotion']['anger'] = $watson_resp_arr['entities'][$i]['emotion']['anger'];
//
//   }
//
//   for ($i = 0; $i < count($watson_resp_arr['concepts']); $i++) {
//     $final_post_data['concepts'][$i]['text'] = $watson_resp_arr['concepts'][$i]['text'];
//     $final_post_data['concepts'][$i]['score'] = $watson_resp_arr['concepts'][$i]['relevance'];
//   }
//
//   for ($i = 0; $i < count($watson_resp_arr['categories']); $i++) {
//     $final_post_data['categories'][$i]['text'] = $watson_resp_arr['categories'][$i]['label'];
//     $final_post_data['categories'][$i]['score'] = $watson_resp_arr['categories'][$i]['score'];
//   }
//
//   $watson_resp = json_encode($final_post_data);
//   $post_resp = post_to_mpx($media_id, $token, $account, $title, $watson_resp);
//   print($post_resp);
//   // break;
// }






?>
