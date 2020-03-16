<?php
$n_home = null;
$n_home_css = null;

if($page == 'home') {
    $n_home = '<span class="sr-only">(current)</span>';
    $n_home_css = 'active';
}

$tpl_b->repl('curr_home', $n_home);
$tpl_b->repl('curr_home_css', $n_home_css);

###############################
# next
###############################

$n_changelog = null;
$n_changelog_css = null;

if($page == 'changelog') {
    $n_changelog = '<span class="sr-only">(current)</span>';
    $n_changelog_css = 'active';
}

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
$tpl_b->repl('curr_changelog_css', $n_changelog_css);








?>


