<?php
  
  $DEV_KEY = 'ABCD-EFGH-JKLM-NOPQ-RSTU-VWXY-0123-4567';
  $OAUTH2_REDIRECT_URI = 'http://personbrowserphp-justincy.dotcloud.com/index.php';
  
  require_once 'guzzle.phar';
  require_once 'FS.phar';
  
  session_start();
  
  $fs = new FS\Client($DEV_KEY, 'sandbox');
  
  // Response to 4xx and 5xx errors
  $fs->getEventDispatcher()->addListener('request.error', function(Guzzle\Common\Event $event) {
    
    // Logout when receiving a 401
    if ($event['response']->getStatusCode() == 401) {
      unset($_SESSION['fs-session']);
      header('Location: ' . basename(__FILE__));
      exit;
    } 
    
    // Show all other errors
    else {
      $event->stopPropagation();
      echo '<pre>', $event['request'], '</pre>';
      echo '<pre>', $event['response'], '</pre>';
      exit;
    }
    
  });
  
  // If we're returning from the oauth2 redirect, capture the code
  if( isset($_REQUEST['code']) ) {
    $_SESSION['fs-session'] = $fs->getOAuth2AccessToken($_REQUEST['code']);
    header('Location: ' . basename(__FILE__));
    exit;
  } 
  
  // Start a session if we haven't already
  else if( !isset($_SESSION['fs-session']) ) {
    $fs->startOAuth2Authorization('http://dev.fs.org/fs/test.php');
  }
  
  // If we reach here, it means we have a session
  // so we're going to give the access token to
  // the FS client so that API requests will be
  // authenticated.
  $fs->setAccessToken($_SESSION['fs-session']);
  
  // If a person was requested, fetch and display them
  if( isset($_REQUEST['person']) ) {
    $response = $fs->getPersonWithRelationships($_REQUEST['person']);
  } 
  
  // Otherwise, get and display the current person with their relationships
  else {
    $response = $fs->getCurrentUserPerson();
    $response = $fs->getPersonWithRelationships($response['persons'][0]['id']);
  }
  
  $person = $response->getPerson();
  
  function person_link($personId) {
    return '<a href="test.php?person=' . urlencode($personId) . '">' . $personId . '</a>';
  }

?>
<html>
<body>

<h1><? echo $person['display']['name']; ?></h1>
<div><label>Birth Date:</label> <? echo $person['display']['birthDate']; ?></div>
<div><label>Birth Place:</label> <? echo $person['display']['birthPlace']; ?></div>

<h2>Parents</h2>
<? foreach( $response->getParents() as $rel ) { ?>
<div class="parents-relationship">
  <div><label>Mother:</label> <? echo person_link($rel['mother']['resourceId']); ?></div>
  <div><label>Father:</label> <? echo person_link($rel['father']['resourceId']); ?></div>
</div>
<? } ?>

<h2>Spouses</h2>
<? foreach( $response->getSpouses() as $rel ) { ?>
<div class="parents-relationship">
  <div><label>Spouse:</label> <? echo person_link($rel['spouse']['resourceId']); ?></div>
</div>
<? } ?>

<h2>Children</h2>
<? foreach( $response->getChildren() as $rel ) { ?>
<div class="parents-relationship">
  <div><label>Child:</label> <? echo person_link($rel['child']['resourceId']); ?></div>
</div>
<? } ?>

</body>
</html>