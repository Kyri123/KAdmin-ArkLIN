<?php

$n = null;

if($page == 'home') {
    $n = 'active';
}

$tpl_b->repl('curr_home_css', $n);

###############################
# next
###############################

$n_changelog = null;
$n = null;

if($page == 'changelog') {
    $n = 'active';
}

$tpl_b->repl('curr_changelog_css', $n);

###############################
# next
###############################

$n_changelog = null;
$n = null;

if($page == 'cluster') {
    $n = 'active';
}

$tpl_b->repl('curr_cluster_css', $n);

###############################
# next
###############################

$n = null;

if($page == 'servercontrollcenter') {
    $n = 'active';
}

$tpl_b->repl('curr_servercontrollcenter_css', $n);

###############################
# next
###############################

$n = null;

if($page == 'userpanel') {
    $n = 'active';
}

$tpl_b->repl('curr_userpanel_css', $n);


###############################
# next
###############################

$n = null;

if($page == 'config') {
    $n = 'active';
}

$tpl_b->repl('curr_config_css', $n);

################################

$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json');
$c = true;
for($i=count($json)-1;$i>-1;$i--) {

    if($version == $json[$i]['version']) {
        $cc = $i;
        break;
    }
}

$n_changelog = '<span class="badge badge-secondary">Neu!</span>';
for($i=count($json)-1;$i>-1;$i--) {
    if($cc >= $i) {
        $n_changelog = null;
        break;
    }
    elseif($json[$i]['datestring'] == "--.--.----") {

    }
    else {
        if($version == $json[$i]['version']) {
            $n_changelog = null;
            break;
        }
        elseif($version != $json[$i]['version']) {
            $n_changelog = '<span class="badge badge-success">Neu!</span>';
            break;
        }
        else {
            break;
        }
        if($version != $json[$i]['version']) $n_changelog = '<span class="badge badge-success">Neu!</span>';
    }
}



$tpl_b->repl('curr_changelog', $n_changelog);








?>


