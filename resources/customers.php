<?php 
/* * ** Hooks used for logging ******** */
$app->hook('slim.before.router', function () use ($app) {
    $request = $app->request;
    $response = $app->response;

    $app->log->debug('[' . date('H:i:s', time()) . '] Request path: ' . $request->getPathInfo());
    $app->log->debug('[' . date('H:i:s', time()) . '] Request body: ' . $request->getBody());
});

$app->hook('slim.after.router', function () use ($app) {
    $request = $app->request;
    $response = $app->response;

    $app->log->debug('[' . date('H:i:s', time()) . '] Response status: ' . $response->getStatus());
});

// handle GET requests for /api/items
$app->get('/customers', function () use ($app) { 
    // get all items
    $customers = R::find('customers');
     
    
    // create JSON response
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(R::exportAll($customers));
});
$app->get('/customers/:id',  function ($id) use ($app) {
	$customer = R::findOne('customers', 'id=?', array($id));
     
    
    if ($customer) {
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($customer));
    } else {
        $app->response()->status(404);
    }
});
 
// handle POST requests
$app->post('/customers', function () use ($app) {
	$request = $app->request();
    $body = $request->getBody(); 
    $input = json_decode($body);
 
    // create and save element record 
    $customer = \RedBeanPHP\R::dispense('customers'); 
    $customer->website = (string)$input->website; 
    list($facebook, $twitter, $pinterest, $linkedin) = \RedBeanPHP\R::dispense('options',4);

    $facebook->meta_key = "facebook_connected";
    $facebook->meta_value = false;

    $twitter->meta_key = "twitter_connected";
    $twitter->meta_value = false;

    $pinterest->meta_key = "linkedin_connected";
    $pinterest->meta_value = false;

    $linkedin->meta_key = "pinterest_connected";
    $linkedin->meta_value = false;
    
    $customer->ownOptions = array($facebook, $twitter, $pinterest, $linkedin);


     
    // do same for other attributes 
    \RedBeanPHP\R::store($customer); 
     
    // create and send JSON response 
    $app->response()->status(201); 
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(\RedBeanPHP\R::exportAll($customer));
});
 
// handle PUT requests
$app->put('/customers/:id', function($id) use ($app) {
    // get request body, decode into PHP object
    $request = $app->request();
     
    $body = $request->getBody();
    $input = json_decode($body);
    
     
 
    // retrieve specified element record 
    // save modified record, create and send JSON response 
    $customer = \RedBeanPHP\R::findOne('customers', 'id = ?', array($id)); 

     
    if ($customer) { 
        if(isset($customer->website))
            $customer->website = (string)$input->website;

        if(isset($input->ownOptions)) {
            foreach($input->ownOptions as $option) {
                $customerOption = \RedBeanPHP\R::dispense("options", 1);
                
                foreach($option as $key=>$val) {
                    $customerOption->$key = $val;
                }

                $customer->ownOptions[] = $customerOption;
            }
        }

        \RedBeanPHP\R::store($customer); 
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(\RedBeanPHP\R::exportAll($customer)); 
    } else { 
        $app->response()->status(404); 
    }
});
 
// handle DELETE requests
$app->delete('/customers/:id', function($id) use ($app) { 
    //\RedBeanPHP\R::dependencies(array('options'=>array('customers')));

    // retrieve specified element record 
    $customer = \RedBeanPHP\R::findOne('customers', 'id = ?', array($id)); 
     
    if ($customer) { 
        
        \RedBeanPHP\R::trashAll($customer->ownOptions);
        
        \RedBeanPHP\R::trash($customer); 
        $app->response()->status(204); 
    } else { 
        $app->response()->status(404); 
    }
});
