<?php






// *** MPX Access Credentials **//

$username = "xxxxx@xx.com";
$password = "xxxxxxx";

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
    $ss_data = json_decode(utf8_encode(file_get_contents('./test.json')), true);
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
      //$duration = 0.00;

      $duration = $ss['duration']*1000;
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

        // print("Player Tags: \n");
        // print_r($playerTags)."\n";
      } else {
        $awayTeam = 'na';
        $homeTeam = 'na';
      }
      if ($ss['metadata']['is_replay']){
        $SSIsReplay = $ss['metadata']['is_replay'];
        // if ($ss['metadata']['is_replay'] == 'true'){
        //   $categories = [array('name' => "Game")];
        // }

      }

      $ss_data_vod = json_decode(utf8_encode(file_get_contents('http://mm.api.simplestream.com/vod/vod/get-single-vod?key=0Yo5Cr2Pb9Yu7Wp1Gp0Xg9Cw5Tz5Qh&uvid='.$ss['uvid'])), true);
      // print("SS_DATA_VOD IS: \n");
      // print_r($ss_data_vod)."\n";

      //$categories = $ss_data_vod['result']['vod']['categories'][0]['name'];
      // print("Categories are: \n");
      // print_r($categories);

      $description = $ss['description'];
      $categories = [array('name' => $ss_data_vod['result']['vod']['categories'][0]['name'])];

      $source_media_bitrates = $ss_data_vod['result']['vod']['streams']['encodings'];
      //print_r($source_media_bitrates)."\n";
      $highest_bitrate = 0;
      foreach ($source_media_bitrates as $bitrates) {
        $rate = $bitrates['bitrate'];
        $source = $bitrates['links']['mp4'];
        if ($rate > $highest_bitrate) {
          $highest_bitrate = $rate;
          $highest_bitrate_source = $source;
          # code...
        }
      }
      print("Highest bitrate is: ").$highest_bitrate."\n";
      print("Highest bitrate source is: ").$highest_bitrate_source."\n";
      print("Highest bitrate filename is: ").basename($highest_bitrate_source)."\n";



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
          'mediaFileInfo' => array('format' => 'M3U', 'duration' => $duration, 'contentType' => 'Video', 'transferInfo' => array('supportsStreaming' => 'true')))));

      $post_data_mediaFile_mp4 = json_encode(array('linkNewFile' => array('mediaId' => $media_id_created,
              'sourceUrl' => 's3://mezzanines-aetn.s3.amazonaws.com/NWSL_Prod/'.basename($highest_bitrate_source),
              'mediaFileInfo' => array('format' => 'MPEG4', 'duration' => $duration, 'contentType' => 'Video', 'assetTypes' => array('Primary Mezzanine Video'), 'transferInfo' => array('supportsStreaming' => 'true')))));

      $post_data_mediaFile_jpg = json_encode(array('linkNewFile' => array('mediaId' => $media_id_created,
              'sourceUrl' => $media_thumbnail_url,
              'mediaFileInfo' => array('format' => 'JPEG', 'contentType' => 'image', 'transferInfo' => array('supportsStreaming' => 'true')))));

      $post_files1 = post_to_mpx($token, $post_data_mediaFile_m3u, "MF");
      $post_files2 = post_to_mpx($token, $post_data_mediaFile_jpg, "MF");
      $post_files3 = post_to_mpx($token, $post_data_mediaFile_mp4, "MF");

      /// ***** PUBLISHING *****

      $profile_id = 'http://data.publish.theplatform.com/publish/data/PublishProfile/28247630';

      $publish_url = 'http://publish.theplatform.com/web/Publish/publish?_mediaId='.$media_id_created.'&_profileId='.$profile_id.'&schema=1.2&token='.$token.'&account=http%3A%2F%2Faccess.auth.theplatform.com%2Fdata%2FAccount%2F2703446073&form=json';

      $pub = file_get_contents($publish_url);
      //print("Result of Publish is: ".$pub."\n");


      $count++;

      // if ($count > 90) {
      //   break;
      // }

      }




?>
