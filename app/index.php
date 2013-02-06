<?php
  
  $DEV_KEY = 'ABCD-EFGH-JKLM-NOPQ-RSTU-VWXY-0123-4567';
  $OAUTH2_REDIRECT_URI = 'http://personbrowserphp-justincy.dotcloud.com/index.php';
  
  require_once 'guzzle.phar';
  require_once 'FS.phar';
  
  session_start();
  
  $fs = new FS\Client($DEV_KEY, 'sandbox');
  
  // If we receive a 401, logout
  $fs->getEventDispatcher()->addListener('request.error', function(Event $event) {
    if ($event['response']->getStatusCode() == 401) {
      unset($_SESSION['fs-session']);
      header('Location: index.php');
      exit;
    }
  });
  
  // If we're returning from the oauth2 redirect, capture the code
  if( isset($_REQUEST['code']) ) {
    $_SESSION['fs-session'] = $fs->getOAuth2AccessToken($_REQUEST['code']);
    // Reload the page without the oauth2 parameters
    header('Location: index.php');
    exit;
  } 
  
  // Start a session if we haven't already
  else if( !isset($_SESSION['fs-session']) ) {
    $fs->startOAuth2Authorization($OAUTH2_REDIRECT_URI);
  }
  
  // If we reach here, it means we have a session
  // so we're going to give the access token to
  // the FS client so that API requests will be
  // authenticated.
  $fs->setAccessToken($_SESSION['fs-session']);
  
  // If a person was requested, fetch and display them
  if( isset($_REQUEST['person']) ) {
    $person = $fs->getPersonWithRelationships($_REQUEST['person']);
  } 
  
  // Otherwise, get and display the current person with their relationships
  else {
    $person = $fs->getCurrentUserPerson();
    $person = $fs->getPersonWithRelationships($person->getUri());
  }
  
  function person_link($personUri) {
    return '<a href="index.php?person=' . urlencode($personUri) . '">' . $personUri . '</a>';
  }

?>
<html>
<body>

<h1><?php echo $person->getName(); ?></h1>
<div><label>Birth Date:</label> <?php echo $person->getBirthDate(); ?></div>
<div><label>Birth Place:</label> <?php echo $person->getBirthPlace(); ?></div>

<h2>Parents</h2>
<?php foreach( $person->getParents() as $parents ) { ?>
<div class="parents-relationship">
  <div><label>Mother:</label> <?php echo person_link($parents['mother']); ?></div>
  <div><label>Father:</label> <?php echo person_link($parents['father']); ?></div>
</div>
<?php } ?>

<h2>Spouses</h2>
<?php foreach( $person->getSpouses() as $spouse ) { ?>
<div class="parents-relationship">
  <div><label>Spouse:</label> <?php echo person_link($spouse['spouse']); ?></div>
</div>
<?php } ?>

<h2>Children</h2>
<?php foreach( $person->getChildren() as $child ) { ?>
<div class="parents-relationship">
  <div><label>Child:</label> <?php echo person_link($child['child']); ?></div>
</div>
<?php } ?>

</body>
</html>