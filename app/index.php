<?php
  
  $DEV_KEY = 'ABCD-EFGH-JKLM-NOPQ-RSTU-VWXY-0123-4567';
  $OAUTH2_REDIRECT_URI = 'http://personbrowserphp-justincy.dotcloud.com/index.php';
  
  require_once 'guzzle.phar';
  require_once 'FS.phar';
  
  session_start();
  
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
    $response = $fs->getPersonWithRelationships($response->persons[0]->id);
  }
  
  $person = $response->getPerson();
  
  //echo '<pre>',print_r($response),'</pre>';
  //exit;
  
  function person_link($personId, $text) {
    return '<a href="test.php?person=' . urlencode($personId) . '">' . $text . '</a>';
  }
  
?>
<html>
<body>

<h1><? echo $person->getPreferredName(); ?></h1>

<h2>Summary</h2>
<div><label>Name:</label> <? echo $person->displayExtension->name; ?></div>
<div><label>Lifespan:</label> <? echo $person->displayExtension->lifespan; ?></div>
<div><label>Birth Date:</label> <? echo $person->displayExtension->birthDate; ?></div>
<div><label>Birth Place:</label> <? echo $person->displayExtension->birthPlace; ?></div>
<div><label>Death Date:</label> <? echo $person->displayExtension->deathDate; ?></div>
<div><label>Death Place:</label> <? echo $person->displayExtension->deathPlace; ?></div>

<h2>Vitals</h2>
<div><label>Given Name:</label> <? echo $person->getPreferredName()->getGivenName(); ?></div>
<div><label>Surname:</label> <? echo $person->getPreferredName()->getSurname(); ?></div>
<div><label>Birth Date:</label> <? echo $person->getBirth() ? $person->getBirth()->getDate() : ''; ?></div>
<div><label>Birth Place:</label> <? echo $person->getBirth() ? $person->getBirth()->getPlace() : ''; ?></div>
<div><label>Death Date:</label> <? echo $person->getDeath() ? $person->getDeath()->getDate() : ''; ?></div>
<div><label>Death Place:</label> <? echo $person->getDeath() ? $person->getDeath()->getPlace() : ''; ?></div>

<h2>Other Information</h2>
<? foreach( $person->getNonVitalFacts() as $fact ) { ?>
<div><label><? echo $fact->type ?>:</label> <? echo $fact; ?></div>
<? } ?>

<h2>Parents</h2>
<? 
  foreach( $response->getParents() as $rel ) {
    $father = $rel->getFather();
    $mother = $rel->getMother();
?>
<div class="parents-relationship">
  <div><label>Father:</label> <? if( $father ) echo person_link($father->id, $father->getPreferredName()); ?></div>
  <div><label>Mother:</label> <? if( $mother ) echo person_link($mother->id, $mother->getPreferredName()); ?></div>
</div>
<? } ?>

<h2>Spouses</h2>
<? 
  foreach( $response->getSpouses() as $rel ) {
    $spouse = $rel->getSpouse();
?>
<div class="parents-relationship">
  <div><label>Spouse:</label> <? echo person_link($spouse->id, $spouse->getPreferredName()); ?></div>
</div>
<? } ?>

<h2>Children</h2>
<? 
  foreach( $response->getChildren() as $rel ) {
    $child = $rel->getChild();
?>
<div class="parents-relationship">
  <div><label>Child:</label> <? echo person_link($child->id, $child->getPreferredName()); ?></div>
</div>
<? } ?>

</body>
</html>