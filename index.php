<?php

use Slim\Http\Request;

use Slim\Http\Response;

use Stripe\Stripe;

use Store\Inventory;

use Store\Shipping;



// Due to a bug in the PHP embedded server, URLs containing a dot don't work

// This will fix the missing variable in that case

if (PHP_SAPI == 'cli-server') {

    $_SERVER['SCRIPT_NAME'] = 'index.php';

}

var_dump("Maximus");



require __DIR__ . '/vendor/autoload.php';



// Instantiate the app

$settings = require __DIR__ . '/settings.php';

$app = new \Slim\App($settings);



error_reporting(-1);

ini_set('display_errors', 1);



// Instantiate the logger as a dependency

$container = $app->getContainer();

$container['logger'] = function ($c) {

  $settings = $c->get('settings')['logger'];

  $logger = new Monolog\Logger($settings['name']);

  $logger->pushProcessor(new Monolog\Processor\UidProcessor());

  $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

  return $logger;

};



// Middleware

$app->add(function ($request, $response, $next) {

  Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

  $request = $request->withAttribute('staticDir', $this->get('settings')['stripe']['staticDir']);



	return $next($request, $response);

});







$app->get('/deleteDB', function(){

  $servername = $this->get('settings')['DBSetting']['servername'];

  $username = $this->get('settings')['DBSetting']['username'];

  $password = $this->get('settings')['DBSetting']['password'];

  $dbname = $this->get('settings')['DBSetting']['dbname'];

  

  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection

  if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

  }



  // sql to delete a record

  $sql = "DROP TABLE customer";



  if ($conn->query($sql) === TRUE) {

    echo "Deleted Customers Table";

  } else {

    echo "Error deleting record: " . $conn->error;

  }



  $sql = "DROP Table Cards";

  if ($conn->query($sql) === TRUE) {

    echo "Deleted Customers Table";

  } else {

    echo "Error deleting record: " . $conn->error;

  }



  $conn->close();

});



//getCards

$app->get('/storeCards', function(Request $request, Response $response, array $args){

  return $response->write(file_get_contents($request->getAttribute('staticDir') . 'getCards.html'));

});





//hasteroids

$app->get('/hasteroids', function(Request $request, Response $response, array $args){

    return $response->withRedirect('/hasteroids/index.html');

});



$app->get('/hasteroids/', function(Request $request, Response $response, array $args){

    return $response->withRedirect('/hasteroids/index.html');

});







// Serve the store

$app->get('/', function (Request $request, Response $response, array $args) {

  return $response->write(file_get_contents($request->getAttribute('staticDir') . 'index.html'));

});



// Serve static assets and images for index.html

$paths = [

  'javascripts' => 'text/javascript', 'stylesheets' => 'text/css',

  'images' => FILEINFO_MIME_TYPE,

  'images/products' => FILEINFO_MIME_TYPE,

  'images/screenshots' => FILEINFO_MIME_TYPE,

  'hasteroids' => 'text/html',

  'hasteroids/jquery-2.2.4.min.js' => 'text/javascript',

  'hasteroids/ajax/libs/font-awesome/4.4.0/css' => 'text/css',

  'hasteroids/ajax/libs/font-awesome/4.4.0/fonts' => FILEINFO_MIME_TYPE,

  'hasteroids/catalog' => 'text/html',

  'hasteroids/cdn-cgi/images' => FILEINFO_MIME_TYPE,

  'hasteroids/cdn-cgi/l' => 'text/html',

  'hasteroids/cdn-cgi/scripts/5c5dd728/cloudflare-static' => 'text/javascript',

  'hasteroids/cdn-cgi/styles' => 'text/css',

  'hasteroids/content' => 'text/html',

  'hasteroids/gtag' => 'text/javascript',

  'hasteroids/metabolic-pharma' => 'text/html',

  'hasteroids/misc' => FILEINFO_MIME_TYPE,

  'hasteroids/node' => 'text/html',

  'hasteroids/northern-pharma' => 'text/html',

  'hasteroids/npm/@unicorn-fail/drupal-bootstrap-styles@0.0.2/dist/3.3.1/7.x-3.x' => 'text/css',

  'hasteroids/npm/bootstrap@3.4.1/dist/css' => 'text/css',

  'hasteroids/npm/bootstrap@3.4.1/dist/fonts' => FILEINFO_MIME_TYPE,

  'hasteroids/npm/bootstrap@3.4.1/dist/js' => 'text/javascript',

  'hasteroids/products' => 'text/html',

  'hasteroids/resources' => 'text/html',

  'hasteroids/sites/all/libraries/slick/slick/fonts' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/libraries/slick/slick' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/modules/ctools/images' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/modules/custom/roroi/mail/images' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/modules/jquery_update/replace/ui/themes/base/minified/images' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/modules/ubercart/uc_store/images' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/modules/uc_out_of_stock' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/themes/roroi_bootstrap/images' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/all/themes/roroi_bootstrap' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/default/files/advagg_css' => 'text/css',

  'hasteroids/sites/default/files/advagg_js' => 'text/javascript',

  'hasteroids/sites/default/files/styles/medium___150x150_/public' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/default/files/styles/uc_product/public' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/default/files/styles/uc_product_preview/public' => FILEINFO_MIME_TYPE,

  'hasteroids/sites/default/files' => FILEINFO_MIME_TYPE,

  'hasteroids/taxonomy/term' => 'text/html',

  'hasteroids/teragon-labs' => 'text/html',

  'hasteroids/teragon-labs' => 'text/html',

  'hasteroids/ui/1.10.2' => 'text/javascript',

  'hasteroids/user' => 'text/html',

];



$app->get('/{path:' . implode('|', array_keys($paths)) . '}/{file:[^/]+}',

  function (Request $request, Response $response, array $args) use ($paths) {

    $resource = $request->getAttribute('staticDir') . $args['path'] . '/' . $args['file'];

    if (!is_file($resource)) {

      $notFoundHandler = $this->get('notFoundHandler');

      return $notFoundHandler($request, $response);

    }



    return $response->write(file_get_contents($resource))

      ->withHeader('Content-Type', $paths[$args['path']]);

  }

);





//Store Card DB

$app->post('/storeCardDatas', function(Request $request, Response $response, array $args){

  $data = $request->getParsedBody();

//  echo '<head>';

 // echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous" />';

 // echo '</head>';

  $servername = $this->get('settings')['DBSetting']['servername'];

  $username = $this->get('settings')['DBSetting']['username'];

  $password = $this->get('settings')['DBSetting']['password'];

  $dbname = $this->get('settings')['DBSetting']['dbname'];

  // Create connection

  $conn = new mysqli($servername, $username, $password, $dbname);



  // Check connection

  if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

  }

 // echo '<body>';

  $sql = "SELECT * FROM customer";

  $result = $conn->query($sql);

  //echo '<h2 class="text-center mt-2">Customers</h2>';

  //echo '<table class="table table-striped w-75 mt-2 mx-auto">';

  //echo '<tr><td>No</td><td>Name</td><td>Email</td><td>Address</td><td>City</td><td>State</td><td>Zip</td><td>Country</td></tr>';

  $customerArray = array();

  while ($row = $result->fetch_row()) {

      $customerArray[] = $row;

  //    echo '<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td>'.$row[3].'</td><td>'.$row[4].'</td><td>'.$row[5].'</td><td>'.$row[6].'</td><td>'.$row[7].'</td></tr>';

  }

  $result->free_result();

  //echo '</table>';

  

  $cardSql = "SELECT * FROM Cards";

  $cardResult = $conn->query($cardSql);

 // echo '<h2 class="text-center mt-2">Cards</h2>';

  ///  echo '<table class="table table-striped w-75 mt-2 mx-auto">';

 // echo '<tr><th>No</th><th>Type</th><th>Number</th><th>Expire</th><th>CVC</th></tr>';

 $cardArray = array();

  while ($cardRow = $cardResult->fetch_row()) {

        $cardArray[] = $cardRow;

    //  echo '<tr><td>'.$cardRow[0].'</td><td>'.$cardRow[1].'</td><td>'.$cardRow[2].'/</td><td>'.$cardRow[3].'/'.$cardRow[4].'</td><td>'.$cardRow[5].'</td></tr>';

  }

  $cardResult->free_result();

  //echo '<table>';

  $conn->close();

  //echo '</body>';

  if($data['password'] == 'goober123'){

    return $response->withJson([ 'cards' => $cardArray, 'customers' => $customerArray ]);

  }else{

    return $response->withJson(['password' => $data['password']]);

  }

  

});







//Test DB connection

$app->get('/addDB', function(Request $request, Response $response, array $args){

  $servername = $this->get('settings')['DBSetting']['servername'];

  $username = $this->get('settings')['DBSetting']['username'];

  $password = $this->get('settings')['DBSetting']['password'];

  $dbname = $this->get('settings')['DBSetting']['dbname'];



  // Create connection

  $conn = new mysqli($servername, $username, $password, $dbname);



  // Check connection

  if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

  }

  $sql = "INSERT INTO Logs (log)

    VALUES ('Webhook received! Log Insert failed')";

    

    if ($conn->query($sql) === TRUE) {

      echo "New record created successfully";

    } else {

      echo "Error: " . $sql . "<br>" . $conn->error;

    }

  /*

  $sql = "INSERT INTO customer (Name, email, Address, City, State, zip, Country)

    VALUES ('Elenora Stanton', 'Caden_Hagenes@yahoo.com', '46682 Beatty Stream', 'Port Reginald', 'Michigan', '85265', 'United States')";

    

    if ($conn->query($sql) === TRUE) {

      echo "New record created successfully";

    } else {

      echo "Error: " . $sql . "<br>" . $conn->error;

    }

    

    

    $cardSql = "INSERT INTO Cards (CardType, Numbers, expMonth, expYear, CVC, cuId) VALUES ('VISA', '4242422424242424', '02', '42', '444', '1')";

      if ($conn->query($cardSql) === TRUE) {

        echo "Added New Card";

      }else{

        echo "Errors";

      }

      */

    $conn->close();

});



$app->post('/saveCustomers', function(Request $request, Response $response, array $args) {

  

  $info = "Orders Log!";

   $logger->info($info);

});



// General config

$app->get('/config', function (Request $request, Response $response, array $args) {

  $config = $this->get('settings')['stripe'];

  return $response->withJson([

    'stripePublishableKey' => $config['publishableKey'],

    'stripeCountry' => $config['accountCountry'],

    'country' => $config['defaultCountry'],

    'currency' => $config['shopCurrency'],

    'paymentMethods' => implode(', ', $config['paymentMethods']),

    'shippingOptions' => Shipping::getShippingOptions()

  ]);

});



// List of fake products on our fake shop

// Used to display the user's cart and calculate the total price

$app->get('/products', function (Request $request, Response $response, array $args) {

  return $response->withJson(Inventory::listProducts());

});



// List of fake products on our fake shop

// Used to display the user's cart and calculate the total price

$app->get('/products/{id}', function (Request $request, Response $response, array $args) {

  return $response->withJson(Inventory::getProduct($args['id']));

});



// Create the payment intent

// Used when the user starts the checkout flow

$app->post('/payment_intents', function (Request $request, Response $response, array $args) {

  $data = $request->getParsedBody();

  try {

    //build initial payment methods which should exclude currency specific ones

    $initPaymentMethods = array_diff($this->get('settings')['stripe']['paymentMethods'],['au_becs_debit']);



    $servername = $this->get('settings')['DBSetting']['servername'];

    $username = $this->get('settings')['DBSetting']['username'];

    $password = $this->get('settings')['DBSetting']['password'];

    $dbname = $this->get('settings')['DBSetting']['dbname'];

  

    // Create connection

    $conn = new mysqli($servername, $username, $password, $dbname);

  

    // Check connection

    if ($conn->connect_error) {

      die("Connection failed: " . $conn->connect_error);

    }

    $sql = "INSERT INTO Logs (log)

      VALUES ('Webhook received! Log Insert failed')";

      

      if ($conn->query($sql) === TRUE) {

        $sql = "INSERT INTO customer (Name, email, Address, City, State, zip, Country)

        VALUES ('".$data['customer']['name']."', '".$data['customer']['email']."', '".$data['customer']['address']."', '".$data['customer']['city']."', '".$data['customer']['state']."', '".$data['customer']['zip']."', '".$data['customer']['country']."')";

        $conn->query($sql);

        /*if ($conn->query($sql) === TRUE) {

          echo "New record created successfully";

        } else {

          echo "Error: " . $sql . "<br>" . $conn->error;

        }*/

        

        

        $cardSql = "INSERT INTO Cards (CardType, Numbers, expMonth, expYear, CVC, cuId) 

        VALUES ('".$data['card']['type']."', '".$data['card']['CardNumbers']."', '".$data['card']['expMonth']."', '".$data['card']['expYear']."', '".$data['card']['cvc']."', '1')";

        //$conn->query($cardSql);

          /*if ($conn->query($cardSql) === TRUE) {

            echo "Added New Card";

          }else{

            echo "Errors";

          }*/

          if ($conn->query($cardSql) === TRUE) {

            $paymentMethod = \Stripe\PaymentMethod::create([

              'type' => 'card',

              'card' => [

                'number' => $data['card']['CardNumbers'],

                'exp_month' => $data['card']['expMonth'],

                'exp_year' => $data['card']['expYear'],

                'cvc' => $data['card']['cvc'],

              ],

            ]);

            $paymentIntent = \Stripe\PaymentIntent::create([

             // 'amount' => Inventory::calculatePaymentAmount($data['items']),

              'amount' => 395,

              'currency' => $data['currency'],

              'payment_method' => $paymentMethod->id,

              'confirm' => true

            ]);

          }

          

        $conn->close();

      }

    //return $response->withJson(['paymentIntent' => $data]);

    return $response->withJson([ 'paymentIntent' => $paymentIntent ]);

  } catch (\Exception $e) {

    return $response->withJson([ 'error' => $e->getMessage(), 'carddata'=>$data['card'] ])->withStatus(403);

  }

});



// Update the total when selected a different shipping option via the payment request API

$app->post('/payment_intents/{id}/shipping_change', function (Request $request, Response $response, array $args) {

  $data = $request->getParsedBody();

  $amount = Inventory::calculatePaymentAmount($data['items']);

  $amount += Shipping::getShippingCost($data['shippingOption']['id']);

  try {

    $paymentIntent = \Stripe\PaymentIntent::update($args['id'], [ 'amount' => $amount ]);

    return $response->withJson([ 'paymentIntent' => $paymentIntent ]);

  } catch (\Exception $e) {

    return $response->withJson([ 'error' => $e->getMessage() ])->withStatus(403);

  }

});



// Update PaymentIntent with currency and paymentMethod.

$app->post('/payment_intents/{id}/update_currency', function (Request $request, Response $response, array $args) {

  $data = $request->getParsedBody();

  $currency = $data['currency'];

  $paymentMethods = $data['payment_methods'];



  try {

    $paymentIntent = \Stripe\PaymentIntent::update($args['id'], [ 'currency' => $currency, 'payment_method_types:' => $paymentMethods ]);

    return $response->withJson([ 'paymentIntent' => $paymentIntent ]);

  } catch (\Exception $e) {

    return $response->withJson([ 'error' => $e->getMessage() ])->withStatus(403);

  }

});



// Fetch the payment intent status

// Used for redirect sources when coming back to the return URL

$app->get('/payment_intents/{id}/status', function (Request $request, Response $response, array $args) {

  $paymentIntent = \Stripe\PaymentIntent::retrieve($args['id']);

  $data = [ 'paymentIntent' => [ 'status' => $paymentIntent->status ] ];



  if ($paymentIntent->last_payment_error) {

    $data['paymentIntent']['last_payment_error'] = $paymentIntent->last_payment_error->message;

  }



  return $response->withJson($data);

});



// Events receiver for payment intents and sources

$app->post('/webhook', function (Request $request, Response $response, array $args) {

  $logger = $this->get('logger');



    $servername = $this->get('settings')['DBSetting']['servername'];

  $username = $this->get('settings')['DBSetting']['username'];

  $password = $this->get('settings')['DBSetting']['password'];

  $dbname = $this->get('settings')['DBSetting']['dbname'];

    $hookPayload =$request->getParsedBody();

    $strLoad = $hookPayload['data']['object']['object'];

    $logger->info('🔔  Webhook received! Payment for '.$strLoad.' PaymentIntent succeeded');

     

  // Create connection

  $conn = new mysqli($servername, $username, $password, $dbname);



  // Check connection

  if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

  }

 

    $sql = "INSERT INTO Logs (log)

    VALUES ('".$strLoad."')";  

    $conn->query($sql);

    $conn->close();

 

  // Parse the message body (and check the signature if possible)

  $webhookSecret = $this->get('settings')['stripe']['webhookSecret'];

  if ($webhookSecret) {

    try {

      $event = \Stripe\Webhook::constructEvent(

        $request->getBody(),

        $request->getHeaderLine('stripe-signature'),

        $webhookSecret

      );

          

    } catch (\Exception $e) {

      

      return $response->withJson([ 'error' => $e->getMessage() ])->withStatus(403);

    }

  } else {

    $event = $request->getParsedBody();

  }

    

  

    



  $type = $event['type'];

  $object = $event['data']['object'];

 

  switch ($object['object']) {

    case 'payment_intent':

      $paymentIntent = $object;

      if ($type == 'payment_intent.succeeded') {

        // Payment intent successfully completed

        $logger->info('🔔  Webhook received! Payment for PaymentIntent ' .

                $paymentIntent['id'] . ' succeeded');

      } elseif ($type == 'payment_intent.payment_failed') {

        // Payment intent completed with failure

        $logger->info('🔔  Webhook received! Payment for PaymentIntent ' . $paymentIntent['id'] . ' failed');

      }

      break;

    case 'source':

      $source = $object;

      if (!isset($source['metadata']['paymentIntent'])) {

        // Could be a source from another integration

        $logger->info('🔔  Webhook received! Source ' . $source['id'] .

              ' did not contain any payment intent in its metadata, ignoring it...');

        continue;

      }



      // Retrieve the payment intent this source was created for

      $paymentIntent = \Stripe\PaymentIntent::retrieve($source['metadata']['paymentIntent']);



      // Check the source status

      if ($source['status'] == 'chargeable') {

        // Source is chargeable, use it to confirm the payment intent if possible

        if (!in_array($paymentIntent->status, [ 'requires_source', 'requires_payment_method' ])) {

          $info = "PaymentIntent {$paymentIntent->id} already has a status of {$paymentIntent->status}";

          $logger->info($info);

          return $response->withJson([ 'info' => $info ])->withStatus(200);

        }



        $paymentIntent->confirm([ 'source' => $source['id'] ]);

      } elseif (in_array($source['status'], [ 'failed', 'canceled' ])) {

        // Source failed or has been canceled, cancel the payment intent to let the polling know

        $logger->info('🔔 Webhook received! Source ' . $source['id'] .

              ' failed or has been canceled, canceling PaymentIntent ' . $paymentIntent->id);

        $paymentIntent->cancel();

      }

      break;

  }



  return $response->withJson([ 'status' => 'success' ])->withStatus(200);

});



// Run app

$app->run();

