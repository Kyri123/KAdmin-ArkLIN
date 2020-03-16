<?php

//Changelog_function
function changelog($str) {
    $str = str_replace("[t]", '<i class="fas fa-check"></i>', $str);
    $str = str_replace("[x]", '<i class="fas fa-times"></i>', $str);
    $str = str_replace("[n]", 'style="list-style-type: none;"', $str);
    return $str;
}


// Vars
$tpl_dir = 'tpl/changelog/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$pagename = 'Changelogs';

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();


$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json');

if(isset($json['file'])) {
    echo 'error 404';
}
else {
    $list = null;
    $now = false;
    for($i=count($json)-1;$i>-1;$i--) {
        $listtpl = new Template('list.htm', $tpl_dir);
        $listtpl->load();


        if($now) $color = 'bg-green';
        if(!$now) $color = 'bg-danger';
        if($version == $json[$i]['version']) $color = 'bg-primary';
        if($version == $json[$i]['version']) $now = true;
        $listtpl->repl('color', $color);

        // fix
        if($json[$i]['fix'] == "") {
            $listtpl->replif('iffix', false);
        }
        else {
            $newstring = null;
            $listtpl->replif('iffix', true);
            $bits = explode("\r", $json[$i]['fix']);
            foreach($bits as $bit)
            {
                $newstring .= changelog($bit);
            }
            $listtpl->repl('fix', $newstring);
        }

        // neu
        if($json[$i]['new'] == "") {
            $listtpl->replif('ifnew', false);
        }
        else {
            $newstring = null;
            $listtpl->replif('ifnew', true);
            $bits = explode("\r", $json[$i]['new']);
            foreach($bits as $bit)
            {
                $newstring .= changelog($bit);
            }
            $listtpl->repl('new', $newstring);
        }

        // change
        if($json[$i]['change'] == "") {
            $listtpl->replif('ifchange', false);
        }
        else {
            $newstring = null;
            $listtpl->replif('ifchange', true);
            $bits = explode("\r", $json[$i]['change']);
            foreach($bits as $bit)
            {
                $newstring .= changelog($bit);
            }
            $listtpl->repl('change', $newstring);
        }


        // java
        if($json[$i]['java'] == "") {
            $listtpl->replif('ifjava', false);
        }
        else {
            $newstring = null;
            $listtpl->replif('ifjava', true);
            $bits = explode("\r", $json[$i]['java']);
            foreach($bits as $bit)
            {
                $newstring .= changelog($bit);
            }
            $listtpl->repl('java', $newstring);
        }

        $listtpl->repl('datestring', $json[$i]['datestring']);
        $listtpl->repl('version', $json[$i]['version']);
        $list .= $listtpl->loadin();
    }
}



// lade in TPL
$tpl->repl('list', $list);
$content = $tpl->loadin();
$H_btn_group = null;
$H_btn_extra = null;
$site_name = 'Changelogs';
?>