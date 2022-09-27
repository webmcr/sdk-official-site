<?php


namespace webmcr\sitesdk;


class SiteSDK {


    const VERSION = '1.1.0';


    public $API_URI = 'https://api.webmcr.ru';


    private $accessKeyInterface, $accessKeyNotifications;

    /**
     * WebMCR API Key
     *
     * You can get it here https://webmcr.ru/profile/settings/#input-api-web
     *
     * @param string|null $accessKeyInterface
     *
     * @param string|null $accessKeyNotifications
    */
    public function __construct($accessKeyInterface = null, $accessKeyNotifications = null) {

        $this->accessKeyInterface = $accessKeyInterface;

        $this->accessKeyNotifications = $accessKeyNotifications;

    }



    /**
     * Get notification input
     *
     * @return array
    */
    private function getInput() {
        $post = $_POST;

        return !is_array($post) ? [] : $post;
    }



    /**
     * Generate notification signature
     *
     * @param array $data
     *
     * @param string|null $accesskey
     *
     * @return string
    */
    public function createNotificationSign($data = [], $accesskey = null) {

        $data['sign'] = !is_null($accesskey) ? $accesskey : $this->accessKeyNotifications;

        ksort($data);

        return hash('sha256', implode(':', $data));
    }



    /**
     * Check input notification signature
     *
     * Default used input POST
     *
     * @param array|null $params
     *
     * @param string|null $accesskey
     *
     * @return bool
    */
    public function checkNotificationSign($params = null, $accesskey = null) {

        if(is_array($params)){

            $params = $this->getInput();

        }

        if(!is_string(@$params['sign'])){

            return false;

        }

        return $params['sign'] === $this->createNotificationSign($params, $accesskey);
    }



    /**
     * Get input notification event name
     *
     * @return string
     */
    public function getNotificationEvent() {

        $input = $this->getInput();

        return !is_string(@$input['event']) ? 'undefined' : trim($input['event']);

    }



    /**
     * Get notification full
     *
     * WARNING!!! If you don't check signature (@see checkNotificationSign) your application may not work correctly
     *
     * @return array
     */
    public function getNotification() {

        return $this->getInput();

    }



    /**
     * Request to API WebMCR
     *
     * @param string $method
     *
     * @param array $params
     *
     * @param boolean $signed
     *
     * @return array
    */
    public function request($method, $params = [], $signed = false) {

        $method = trim($method, '/');

        $url = "{$this->API_URI}/{$method}/";

        if($signed){ $url .= $this->accessKeyInterface; }

        $c = curl_init($url);

        curl_setopt_array($c, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_TIMEOUT => 3,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_USERAGENT => 'WebMCR API SDK '.self::VERSION
        ]);

        $json = curl_exec($c);

        curl_close($c);

        $response = @json_decode($json, true);

        return is_array($response) ? $response : [
            'success' => false,
            'message' => 'request failed',
            'code' => -1
        ];
    }



    /**
     * Get current cms list
     *
     * @link https://webmcr.ru/api/#cms-list
     *
     * @param array $params
     *
     * @return array
     */
    public function cms_list($params = []) {

        return $this->request('cms/list', $params);
    }



    /**
     * Get cms by ID
     *
     * @link https://webmcr.ru/api/#cms-get
     *
     * @param int $cms_id
     *
     * @param array $params
     *
     * @return array
     */
    public function cms_get($cms_id, $params = []) {

        $params['id'] = $cms_id;

        return $this->request('cms/get', $params);
    }



    /**
     * Get cms version list
     *
     * @link https://webmcr.ru/api/#cms-versions-list
     *
     * @param array $params
     *
     * @return array
     */
    public function cms_versions_list($params = []) {

        return $this->request('cms/versions/list', $params);
    }



    /**
     * Get cms version by ID
     *
     * @link https://webmcr.ru/api/#cms-versions-get
     *
     * @param int $version_id
     *
     * @param array $params
     *
     * @return array
     */
    public function cms_version_get($version_id, $params = []) {

        $params['id'] = $version_id;

        return $this->request('cms/versions/get', $params);
    }



    /**
     * Get extension by ID
     *
     * @link https://webmcr.ru/api/#extensions-get
     *
     * @param int $extension_id
     *
     * @param array $params
     *
     * @param bool $signed
     *
     * @return array
     */
    public function extensions_get($extension_id, $params = [], $signed = false) {

        $params['id'] = $extension_id;

        return $this->request('extensions/get', $params, $signed);
    }



    /**
     * Get extension list
     *
     * @link https://webmcr.ru/api/#extensions-list
     *
     * @param array $params
     *
     * @param bool $signed
     *
     * @return array
     */
    public function extensions_list($params = [], $signed = false) {

        return $this->request('extensions/list', $params, $signed);
    }



    /**
     * Get extension version list
     *
     * @link https://webmcr.ru/api/#extensions-versions-list
     *
     * @param array $params
     *
     * @param bool $signed
     *
     * @return array
     */
    public function extensions_versions_list($params = [], $signed = false) {

        return $this->request('extensions/versions/list', $params, $signed);
    }



    /**
     * Get extension tag list
     *
     * @link https://webmcr.ru/api/#extensions-tags-list
     *
     * @param array $params
     *
     * @return array
     */
    public function extensions_tags_list($params = []) {

        return $this->request('extensions/tags/list', $params);
    }


    /**
     * Get extension sale list
     *
     * @link https://webmcr.ru/api/#extensions-sales-list
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function extensions_sales_list($params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        return $this->request('extensions/versions/list', $params, true);
    }



    /**
     * Get news by ID
     *
     * @link https://webmcr.ru/api/#news-get
     *
     * @param int $new_id
     *
     * @param array $params
     *
     * @param bool $signed
     *
     * @return array
     */
    public function news_get($new_id, $params = [], $signed = false) {

        $params['id'] = $new_id;

        return $this->request('news/get', $params, $signed);
    }



    /**
     * Get news list
     *
     * @link https://webmcr.ru/api/#news-list
     *
     * @param array $params
     *
     * @param bool $signed
     *
     * @return array
     */
    public function news_list($params = [], $signed = false) {

        return $this->request('news/list', $params, $signed);
    }



    /**
     * Get news tag list
     *
     * @link https://webmcr.ru/api/#news-tags-list
     *
     * @param array $params
     *
     * @return array
     */
    public function news_tags_list($params = []) {

        return $this->request('news/tags/list', $params);
    }


    /**
     * Get notification list
     *
     * @link https://webmcr.ru/api/#notifications-list
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function notifications_list($params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        return $this->request('notifications/list', $params, true);
    }


    /**
     * Get notification by ID
     *
     * @link https://webmcr.ru/api/#notifications-get
     *
     * @param int $notification_id
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function notifications_get($notification_id, $params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        $params['id'] = $notification_id;

        return $this->request('notifications/get', $params, true);
    }


    /**
     * Get request list
     *
     * @link https://webmcr.ru/api/#requests-list
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function requests_list($params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        return $this->request('requests/list', $params, true);
    }


    /**
     * Get request by ID
     *
     * @link https://webmcr.ru/api/#requests-get
     *
     * @param int $request_id
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function requests_get($request_id, $params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        $params['id'] = $request_id;

        return $this->request('requests/get', $params, true);
    }


    /**
     * Get promo list
     *
     * @link https://webmcr.ru/api/#promo-list
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function promo_list($params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        return $this->request('promo/list', $params, true);
    }


    /**
     * Get promo by ID
     *
     * @link https://webmcr.ru/api/#promo-get
     *
     * @param int $promo_id
     *
     * @param array $params
     *
     * @return array
     *
     * @throws SiteSDKException
     */
    public function promo_get($promo_id, $params = []) {

        if(!$this->accessKeyInterface){
            throw new SiteSDKException('Interface access key required for this method');
        }

        $params['id'] = $promo_id;

        return $this->request('promo/get', $params, true);
    }
}

?>