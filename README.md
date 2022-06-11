### Install
`composer require webmcr/sdk-official-site`

### Example output request
```php
<?php

use webmcr\sitesdk\SiteSDK;

require_once('vendor/autoload.php');

$interfaceKey = '123456';

$sdk = new SiteSDK($interfaceKey);

$list = $sdk->extensions_list();

foreach($list as $extension){
    var_dump($extension);
}
```

### Example input notification request
```php
<?php

use webmcr\sitesdk\SiteSDK;

require_once('vendor/autoload.php');

$notificationsKey = '654321';

$sdk = new SiteSDK(null, $notificationsKey);

if(!$sdk->checkNotificationSign()){
    exit('Error sign');
}

$notification = $sdk->getNotification();

// Do something...
```