<?php

function clean($strin) {
    $strout = null;

    for ($i = 0; $i < strlen($strin); $i++) {
            $ord = ord($strin[$i]);

            if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
                    $strout .= "&amp;#{$ord};";
            }
            else {
                    switch ($strin[$i]) {
                            case '<':
                                    $strout .= '&lt;';
                                    break;
                            case '>':
                                    $strout .= '&gt;';
                                    break;
                            case '&':
                                    $strout .= '&amp;';
                                    break;
                            case '"':
                                    $strout .= '&quot;';
                                    break;
                            default:
                                    $strout .= $strin[$i];
                    }
            }
    }

    return $strout;
    
}

function registration_callback($username, $email, $userdir){
    
    global $data;
    
    $data = array($username, $email, $userdir);

}

function printArray($arrayName){
    
    $messageCount = count($arrayName);
    
    $i = 0;
    
    foreach ( $arrayName as $item ) :
    
        $i++; 
    
        if($i < $messageCount) :
    
            echo "<small class='text-uppercase'>" . $item . "</small> & ";
    
        elseif($i = $messageCount) :
    
            echo "<small class='text-uppercase'>" . $item . "</small>";
    
        endif;
        
    endforeach;
    
}

function write_ini_file($content, $path) { 
    
    if (!$handle = fopen($path, 'w')) {
        
        return false; 
    
    }
    
    $success = fwrite($handle, trim($content));
    
    fclose($handle); 
    
    return $success; 

}

function gotTimezone(){

    $regions = array(
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Arctic' => DateTimeZone::ARCTIC,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Australia' => DateTimeZone::AUSTRALIA,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC
    );
    
    $timezones = array();

    foreach ($regions as $name => $mask) {
        
        $zones = DateTimeZone::listIdentifiers($mask);

        foreach($zones as $timezone) {

            $time = new DateTime(NULL, new DateTimeZone($timezone));

            $ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';

            $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;

        }
        
    }   
    
    print '<select name="timezone" id="timezone" class="form-control material input-sm" required>';
    
    foreach($timezones as $region => $list) {
    
        print '<optgroup label="' . $region . '">' . "\n";
    
        foreach($list as $timezone => $name) {
            
            if($timezone == TIMEZONE) : $selected = " selected"; else : $selected = ""; endif;
            
            print '<option value="' . $timezone . '"' . $selected . '>' . $name . '</option>' . "\n";
    
        }
    
        print '</optgroup>' . "\n";
    
    }
    
    print '</select>';
    
}

function getTimezone(){

    $regions = array(
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Arctic' => DateTimeZone::ARCTIC,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Australia' => DateTimeZone::AUSTRALIA,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC
    );
    
    $timezones = array();

    foreach ($regions as $name => $mask) {
        
        $zones = DateTimeZone::listIdentifiers($mask);

        foreach($zones as $timezone) {

            $time = new DateTime(NULL, new DateTimeZone($timezone));

            $ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';

            $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;

        }
        
    }   
    
    print '<select name="timezone" id="timezone" class="form-control material" required>';
    
    foreach($timezones as $region => $list) {
    
        print '<optgroup label="' . $region . '">' . "\n";
    
        foreach($list as $timezone => $name) {
            
            print '<option value="' . $timezone . '">' . $name . '</option>' . "\n";
    
        }
    
        print '</optgroup>' . "\n";
    
    }
    
    print '</select>';
    
}

function explosion($string, $position){
    
    $getWord = explode("|", $string);
    return $getWord[$position];
    
}

function getServerPath() {
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { 
        
        $protocol = "https://"; 
    
    } else {  
        
        $protocol = "http://"; 
    
    }
    
    return $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
      
}

function get_browser_name() {
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
    
    return 'Other';
    
}

function getPlexRecent($url, $port, $type, $token, $size, $header){
    
    $urlCheck = stripos($url, "http");

    if ($urlCheck === false) {
        
        $url = "http://" . $url;
    
    }
    
    if($port !== ""){ $url = $url . ":" . $port; }
    
    $address = $url;
    
    $api = file_get_contents($address."/library/recentlyAdded?X-Plex-Token=".$token);
    $api = simplexml_load_string($api);
    $getServer = file_get_contents($address."/servers?X-Plex-Token=".$token);
    $getServer = simplexml_load_string($getServer);
    
    foreach($getServer AS $child) {

       $gotServer = $child['machineIdentifier'];
    }

    $i = 0;
    
    $gotPlex = '<div class="col-lg-'.$size.'"><h5 class="text-center">'.$header.'</h5><div id="carousel-'.$type.'" class="carousel slide box-shadow white-bg" data-ride="carousel"><div class="carousel-inner" role="listbox">';
        
    foreach($api AS $child) {
     
        if($child['type'] == $type){
            
            $i++;
            
            if($i == 1){ $active = "active"; }else{ $active = "";}
            
            $thumb = $child['thumb'];

            $plexLink = "https://app.plex.tv/web/app#!/server/$gotServer/details?key=/library/metadata/".$child['ratingKey'];
            
            if($type == "movie"){ 
                
                $title = $child['title']; 
                $summary = $child['summary'];
                $height = "150";
                $width = "100";
            
            }elseif($type == "season"){ 
                
                $title = $child['parentTitle'];
                $summary = $child['parentSummary'];
                $height = "150";
                $width = "100";
            
            }elseif($type == "album"){
                
                $title = $child['parentTitle']; 
                $summary = $child['title'];
                $height = "150";
                $width = "150";
            
            }
            
            
            $gotPlex .= '<div class="item '.$active.'"> <a href="'.$plexLink.'" target="_blank"> <img alt="'.$title.'" class="carousel-image '.$type.'" src="image.php?img='.$thumb.'&height='.$height.'&width='.$width.'"> </a> <div class="carousel-caption '.$type.'"> <h4>'.$title.'</h4> <small> <em>'.$summary.'</em> </small> </div> </div>';
            
            $plexLink = "";

        }
        
    }
    
    $gotPlex .= '</div>';
    
    if ($i > 1){ 

        $gotPlex .= '<a class="left carousel-control '.$type.'" href="#carousel-'.$type.'" role="button" data-slide="prev"><span class="fa fa-chevron-left" aria-hidden="true"></span><span class="sr-only">Previous</span></a><a class="right carousel-control '.$type.'" href="#carousel-'.$type.'" role="button" data-slide="next"><span class="fa fa-chevron-right" aria-hidden="true"></span><span class="sr-only">Next</span></a>';
        
    }

    $gotPlex .= '</div></div>';

    $noPlex = '<div class="col-lg-'.$size.'"><h5 class="text-center">'.$header.'</h5>';
    $noPlex .= '<div id="carousel-'.$type.'" class="carousel slide box-shadow white-bg" data-ride="carousel">';
    $noPlex .= '<div class="carousel-inner" role="listbox">';
    $noPlex .= '<div class="item active">';
    $noPlex .= "<img alt='nada' class='carousel-image movie' src='images/nadaplaying.jpg'>";
    $noPlex .= '<div class="carousel-caption"> <h4>Nothing New</h4> <small> <em>Get to Adding!</em> </small></div></div></div></div></div>';
    
    if ($i != 0){ return $gotPlex; }
    if ($i == 0){ return $noPlex; }

}

function getPlexStreams($url, $port, $token, $size, $header){
    
    $urlCheck = stripos($url, "http");

    if ($urlCheck === false) {
        
        $url = "http://" . $url;
    
    }
    
    if($port !== ""){ $url = $url . ":" . $port; }
    
    $address = $url;
    
    $api = file_get_contents($address."/status/sessions?X-Plex-Token=".$token);
    $api = simplexml_load_string($api);
    $getServer = file_get_contents($address."/servers?X-Plex-Token=".$token);
    $getServer = simplexml_load_string($getServer);
    
    foreach($getServer AS $child) {

       $gotServer = $child['machineIdentifier'];
    }
    
    $i = 0;
    
    $gotPlex = '<div class="col-lg-'.$size.'"><h5 class="text-center">'.$header.'</h5>';
    $gotPlex .= '<div id="carousel-streams" class="carousel slide box-shadow white-bg" data-ride="carousel">';
    $gotPlex .= '<div class="carousel-inner" role="listbox">';
        
    foreach($api AS $child) {
     
        $type = $child['type'];

        $plexLink = "https://app.plex.tv/web/app#!/server/$gotServer/details?key=/library/metadata/".$child['ratingKey'];
            
        $i++;

        if($i == 1){ $active = "active"; }else{ $active = "";}

        
        if($type == "movie"){ 

            $title = $child['title']; 
            $summary = htmlentities($child['summary'], ENT_QUOTES);
            $thumb = $child['thumb'];
            $image = "movie";
            $height = "150";
            $width = "100";

        }elseif($type == "episode"){ 

            $title = $child['grandparentTitle'];
            $summary = htmlentities($child['summary'], ENT_QUOTES);
            $thumb = $child['grandparentThumb'];
            $image = "season";
            $height = "150";
            $width = "100";


        }elseif($type == "track"){

            $title = $child['grandparentTitle'] . " - " . $child['parentTitle']; 
            $summary = htmlentities($child['title'], ENT_QUOTES);
            $thumb = $child['thumb'];
            $image = "album";
            $height = "150";
            $width = "150";

        }elseif($type == "clip"){

            $title = $child['title'].' - Trailer';
            $summary = ($child['summary'] != "" ? $child['summary'] : "<i>No summary loaded.</i>");
            $thumb = ($child['thumb'] != "" ? $child['thumb'] : 'images/nadaplaying.jpg');
            $image = "movie";
            $height = "150";
            $width = "100";

        }

        $gotPlex .= '<div class="item '.$active.'">';

        $gotPlex .= "<a href='$plexLink' target='_blank'><img alt='$title' class='carousel-image $image' src='image.php?img=$thumb&height=$height&width=$width'></a>";

        $gotPlex .= '<div class="carousel-caption '. $image . '""><h4>'.$title.'</h4><small><em>'.$summary.'</em></small></div></div>';
        
        $plexLink = "";

        
    }
    
    $gotPlex .= '</div>';
    
    if ($i > 1){ 

        $gotPlex .= '<a class="left carousel-control streams" href="#carousel-streams" role="button" data-slide="prev"><span class="fa fa-chevron-left" aria-hidden="true"></span><span class="sr-only">Previous</span></a><a class="right carousel-control streams" href="#carousel-streams" role="button" data-slide="next"><span class="fa fa-chevron-right" aria-hidden="true"></span><span class="sr-only">Next</span></a>';
        
    }

    $gotPlex .= '</div></div>';
    
    $noPlex = '<div class="col-lg-'.$size.'"><h5 class="text-center">'.$header.'</h5>';
    $noPlex .= '<div id="carousel-streams" class="carousel slide box-shadow white-bg" data-ride="carousel">';
    $noPlex .= '<div class="carousel-inner" role="listbox">';
    $noPlex .= '<div class="item active">';
    $noPlex .= "<img alt='nada' class='carousel-image movie' src='images/nadaplaying.jpg'>";
    $noPlex .= '<div class="carousel-caption"><h4>Nothing Playing</h4><small><em>Get to Streaming!</em></small></div></div></div></div></div>';
    
    if ($i != 0){ return $gotPlex; }
    if ($i == 0){ return $noPlex; }

}

function getSickrageCalendarWanted($array){
    
    $array = json_decode($array, true);
    $gotCalendar = "";
    $i = 0;

    foreach($array['data']['missed'] AS $child) {

            $i++;
            $seriesName = $child['show_name'];
            $episodeAirDate = $child['airdate'];
            $episodeAirDateTime = explode(" ",$child['airs']);
            $episodeAirDateTime = date("H:i:s", strtotime($episodeAirDateTime[1].$episodeAirDateTime[2]));
            $episodeAirDate = strtotime($episodeAirDate.$episodeAirDateTime);
            $episodeAirDate = date("Y-m-d H:i:s", $episodeAirDate);
            if (new DateTime() < new DateTime($episodeAirDate)) { $unaired = true; }
            $downloaded = "0";
            if($downloaded == "0" && isset($unaired)){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg";}else{ $downloaded = "red-bg"; }
            $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }
    
    foreach($array['data']['today'] AS $child) {

            $i++;
            $seriesName = $child['show_name'];
            $episodeAirDate = $child['airdate'];
            $episodeAirDateTime = explode(" ",$child['airs']);
            $episodeAirDateTime = date("H:i:s", strtotime($episodeAirDateTime[1].$episodeAirDateTime[2]));
            $episodeAirDate = strtotime($episodeAirDate.$episodeAirDateTime);
            $episodeAirDate = date("Y-m-d H:i:s", $episodeAirDate);
            if (new DateTime() < new DateTime($episodeAirDate)) { $unaired = true; }
            $downloaded = "0";
            if($downloaded == "0" && isset($unaired)){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg";}else{ $downloaded = "red-bg"; }
            $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }
    
    foreach($array['data']['soon'] AS $child) {

            $i++;
            $seriesName = $child['show_name'];
            $episodeAirDate = $child['airdate'];
            $episodeAirDateTime = explode(" ",$child['airs']);
            $episodeAirDateTime = date("H:i:s", strtotime($episodeAirDateTime[1].$episodeAirDateTime[2]));
            $episodeAirDate = strtotime($episodeAirDate.$episodeAirDateTime);
            $episodeAirDate = date("Y-m-d H:i:s", $episodeAirDate);
            if (new DateTime() < new DateTime($episodeAirDate)) { $unaired = true; }
            $downloaded = "0";
            if($downloaded == "0" && isset($unaired)){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg";}else{ $downloaded = "red-bg"; }
            $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }
    
    foreach($array['data']['later'] AS $child) {

            $i++;
            $seriesName = $child['show_name'];
            $episodeAirDate = $child['airdate'];
            $episodeAirDateTime = explode(" ",$child['airs']);
            $episodeAirDateTime = date("H:i:s", strtotime($episodeAirDateTime[1].$episodeAirDateTime[2]));
            $episodeAirDate = strtotime($episodeAirDate.$episodeAirDateTime);
            $episodeAirDate = date("Y-m-d H:i:s", $episodeAirDate);
            if (new DateTime() < new DateTime($episodeAirDate)) { $unaired = true; }
            $downloaded = "0";
            if($downloaded == "0" && isset($unaired)){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg";}else{ $downloaded = "red-bg"; }
            $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }

    if ($i != 0){ return $gotCalendar; }

}

function getSickrageCalendarHistory($array){
    
    $array = json_decode($array, true);
    $gotCalendar = "";
    $i = 0;

    foreach($array['data'] AS $child) {

            $i++;
            $seriesName = $child['show_name'];
            $episodeAirDate = $child['date'];
            $downloaded = "green-bg";
            $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }

    if ($i != 0){ return $gotCalendar; }

}

function getSonarrCalendar($array){
    
    $array = json_decode($array, true);
    $gotCalendar = "";
    $i = 0;
    foreach($array AS $child) {

        $i++;
        $seriesName = htmlentities($child['series']['title'], ENT_QUOTES);
        $runtime = $child['series']['runtime'];
        $episodeName = htmlentities($child['title'], ENT_QUOTES);
        $episodeAirDate = $child['airDateUtc'];
        $episodeAirDate = strtotime($episodeAirDate);
        $episodeAirDate = date("Y-m-d H:i:s", $episodeAirDate);
        
        if (new DateTime() < new DateTime($episodeAirDate)) { $unaired = true; }

        $downloaded = $child['hasFile'];
        if($downloaded == "0" && isset($unaired)){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg";}else{ $downloaded = "red-bg"; }
        
        $gotCalendar .= "{ title: \"$seriesName\", start: \"$episodeAirDate\", className: \"$downloaded\", imagetype: \"tv\" }, \n";
        
    }

    if ($i != 0){ return $gotCalendar; }

}

function getRadarrCalendar($array){
    
    $array = json_decode($array, true);
    $gotCalendar = "";
    $i = 0;
    foreach($array AS $child) {
        if(isset($child['physicalRelease'])){
            $i++;
            $movieName = htmlentities($child['title'], ENT_QUOTES);
            $runtime = $child['runtime'];
            $physicalRelease = $child['physicalRelease'];
            $physicalRelease = strtotime($physicalRelease);
            $physicalRelease = date("Y-m-d", $physicalRelease);
            
            if (new DateTime() < new DateTime($physicalRelease)) { $notReleased = "true"; }else{ $notReleased = "false"; }

            $downloaded = $child['hasFile'];
            if($downloaded == "0" && $notReleased == "true"){ $downloaded = "indigo-bg"; }elseif($downloaded == "1"){ $downloaded = "green-bg"; }else{ $downloaded = "red-bg"; }

            $gotCalendar .= "{ title: \"$movieName\", start: \"$physicalRelease\", className: \"$downloaded\", imagetype: \"film\" }, \n";
        }
        
    }

    if ($i != 0){ return $gotCalendar; }

}

function nzbgetConnect($url, $port, $username, $password, $list){
    
    $urlCheck = stripos($url, "http");

    if ($urlCheck === false) {
        
        $url = "http://" . $url;
    
    }
    
    if($port !== ""){ $url = $url . ":" . $port; }
    
    $address = $url;
    
    $api = file_get_contents("$url/$username:$password/jsonrpc/$list");
                    
    $api = json_decode($api, true);
    
    $i = 0;
    
    $gotNZB = "";
    
    foreach ($api['result'] AS $child) {
        
        $i++;
        //echo '<pre>' . var_export($child, true) . '</pre>';
        $downloadName = htmlentities($child['NZBName'], ENT_QUOTES);
        $downloadStatus = $child['Status'];
        $downloadCategory = $child['Category'];
        if($list == "history"){ $downloadPercent = "100"; $progressBar = ""; }
        if($list == "listgroups"){ $downloadPercent = (($child['FileSizeMB'] - $child['RemainingSizeMB']) / $child['FileSizeMB']) * 100; $progressBar = "progress-bar-striped active"; }
        if($child['Health'] <= "750"){ 
            $downloadHealth = "danger"; 
        }elseif($child['Health'] <= "900"){ 
            $downloadHealth = "warning"; 
        }elseif($child['Health'] <= "1000"){ 
            $downloadHealth = "success"; 
        }
        
        $gotNZB .= '<tr>

                        <td>'.$downloadName.'</td>
                        <td>'.$downloadStatus.'</td>
                        <td>'.$downloadCategory.'</td>

                        <td>

                            <div class="progress">

                                <div class="progress-bar progress-bar-'.$downloadHealth.' '.$progressBar.'" role="progressbar" aria-valuenow="'.$downloadPercent.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$downloadPercent.'%">

                                    <p class="text-center">'.round($downloadPercent).'%</p>
                                    <span class="sr-only">'.$downloadPercent.'% Complete</span>

                                </div>

                            </div>

                        </td>

                    </tr>';
        
        
    }
    
    if($i > 0){ return $gotNZB; }
    if($i == 0){ echo '<tr><td colspan="4"><p class="text-center">No Results</p></td></tr>'; }

}

function sabnzbdConnect($url, $port, $key, $list){
    
    $urlCheck = stripos($url, "http");

    if ($urlCheck === false) {
        
        $url = "http://" . $url;
    
    }
    
    if($port !== ""){ $url = $url . ":" . $port; }
    
    $address = $url;

    $api = file_get_contents("$url/api?mode=$list&output=json&apikey=$key");
                    
    $api = json_decode($api, true);
    
    $i = 0;
    
    $gotNZB = "";
    
    foreach ($api[$list]['slots'] AS $child) {
        
        $i++;
        if($list == "queue"){ $downloadName = $child['filename']; $downloadCategory = $child['cat']; $downloadPercent = (($child['mb'] - $child['mbleft']) / $child['mb']) * 100; $progressBar = "progress-bar-striped active"; } 
        if($list == "history"){ $downloadName = $child['name']; $downloadCategory = $child['category']; $downloadPercent = "100"; $progressBar = ""; }
        $downloadStatus = $child['status'];
        
        $gotNZB .= '<tr>

                        <td>'.$downloadName.'</td>
                        <td>'.$downloadStatus.'</td>
                        <td>'.$downloadCategory.'</td>

                        <td>

                            <div class="progress">

                                <div class="progress-bar progress-bar-success '.$progressBar.'" role="progressbar" aria-valuenow="'.$downloadPercent.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$downloadPercent.'%">

                                    <p class="text-center">'.round($downloadPercent).'%</p>
                                    <span class="sr-only">'.$downloadPercent.'% Complete</span>

                                </div>

                            </div>

                        </td>

                    </tr>';
        
        
    }
    
    if($i > 0){ return $gotNZB; }
    if($i == 0){ echo '<tr><td colspan="4"><p class="text-center">No Results</p></td></tr>'; }

}

function getHeadphonesCalendar($url, $port, $key, $list){

    $urlCheck = stripos($url, "http");

    if ($urlCheck === false) {
        
        $url = "http://" . $url;
    
    }
    
    if($port !== ""){ $url = $url . ":" . $port; }
    
    $address = $url;
    
    $api = file_get_contents($address."/api?apikey=".$key."&cmd=$list");
                    
    $api = json_decode($api, true);
    
    $i = 0;
    
    $gotCalendar = "";

    foreach($api AS $child) {

        if($child['Status'] != "Skipped"){
        
            $i++;
            $albumName = addslashes($child['AlbumTitle']);
            $albumArtist = htmlentities($child['ArtistName'], ENT_QUOTES);
            $albumDate = $child['ReleaseDate'];
            $albumDate = strtotime($albumDate);
            $albumDate = date("Y-m-d", $albumDate);
            $albumStatus = $child['Status'];
            
            if (new DateTime() < new DateTime($albumDate)) {  $notReleased = "true"; }else{ $notReleased = "false"; }

            if($albumStatus == "Wanted" && $notReleased == "true"){ $albumStatusColor = "indigo-bg"; }elseif($albumStatus == "Downloaded"){ $albumStatusColor = "green-bg"; }else{ $albumStatusColor = "red-bg"; }

            $gotCalendar .= "{ title: \"$albumArtist - $albumName\", start: \"$albumDate\", className: \"$albumStatusColor\", imagetype: \"music\" }, \n";
            
        }
        
    }

    if ($i != 0){ return $gotCalendar; }

}
?>