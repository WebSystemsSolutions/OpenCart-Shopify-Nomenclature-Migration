<?php

class ModelModuleShopifyOpencart extends Model
{
    protected $shopifyName;
    protected $shopifyToken;
    //protected $params;
    protected $load;
    protected $registry;

    function __construct()
    {
        //print_r($param);
//        $this->shopifyName = $param->get('shopifyName');
//        $this->shopifyToken = $param->get('shopifyToken');
        global $loader, $registry;
        $this->shopifyName = $registry->get('shopifyName');
        $this->shopifyToken = $registry->get('shopifyToken');
        $this->load = $loader;
        $this->registry = $registry;
    }

    private function connectAPI($path_stuff = 'products', $curl_type = 'GET', $params = NULL)
    {
        $full_url = "https://{$this->shopifyName}/admin/api/2019-04/{$path_stuff}.json" . $tmp_stuff = ($curl_type == "GET" and $params != NULL) ? '?' . $params : '';
        //var_dump($full_url);
        $path_stuff = str_replace("/", "-", $path_stuff);
        if (file_exists(DIR_CACHE . "shopify-{$path_stuff}.json") and $path_stuff != 'products' and $path_stuff != 'products-count' and $curl_type == 'GET' and time() - filemtime(DIR_CACHE . "shopify-{$path_stuff}.json") < 21600) {
            $server_output = file_get_contents(DIR_CACHE . "shopify-{$path_stuff}.json");
            //var_dump('if'.$path_stuff);
        } else {
            //var_dump('else'.$path_stuff);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $full_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "{$curl_type}");
            if ($curl_type == 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $headers = [
                "X-Shopify-Access-Token: {$this->shopifyToken}",
                "Content-Type: application/json"
            ];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $server_output = curl_exec($ch);
            //var_dump($server_output);
            curl_close($ch);
            if (!$server_output) {
                return 'Check shopify URL';
            }
            if ($path_stuff != 'products' and $path_stuff != 'products-count' and $curl_type == 'GET') {
                $fd = fopen(DIR_CACHE . "shopify-{$path_stuff}.json", 'w');
                fwrite($fd, $server_output);
                fclose($fd);
            }
        }
        return json_decode($server_output);

    }

    private function connectAPI_productAdd_async($paramsss = array())
    {
        $model_catalog_product = $this->registry->get('model_catalog_product');
        $headers = [
            "X-Shopify-Access-Token: {$this->shopifyToken}",
            "Content-Type: application/json"
        ];
//        function curlHeaderCallback($resURL, $strHeader) {
//            print_r($strHeader);
//            var_dump($strHeader);
//            var_dump($resURL);
//            var_dump(stristr($strHeader, 'Retry-After: 2.0'));
//            usleep(300000);
//
//            //return strlen($strHeader);
//        }

        $res = array();
        $count_custom = 0;
        // Create get requests for each URL
        $paramss = array_chunk($paramsss, 15);
        foreach ($paramss as $params) {
            $sleep = false;
            $mh = curl_multi_init();

            foreach ($params as $i => $param) {
                $paramTMP = $param;
                unset($paramTMP['productOpencarId']);
                unset($paramTMP['categories_id']);
                $url = "https://{$this->shopifyName}/admin/api/2019-04/products.json";
                //var_dump($url);
                $ch[$i] = curl_init($url);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch[$i], CURLOPT_HEADER, true);
                //curl_setopt($ch[$i], CURLOPT_HEADERFUNCTION, 'curlHeaderCallback');

//                curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 10);
//                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 10);

                if ($paramTMP) {
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, json_encode($paramTMP));
                }
                curl_multi_add_handle($mh, $ch[$i]);
            }

            // Start performing the request
            do {
                $execReturnValue = curl_multi_exec($mh, $runningHandles);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
            // Loop and continue processing the request
            while ($runningHandles && $execReturnValue == CURLM_OK) {
                // !!!!! changed this if and the next do-while !!!!!

                if (curl_multi_select($mh) != -1) {
                    usleep(100);
                }


                do {
                    $execReturnValue = curl_multi_exec($mh, $runningHandles);

                } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
            }


            // Check for any errors
            if ($execReturnValue != CURLM_OK) {
                trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
            }

            // Extract the content
            foreach ($params as $i => $param) {
                // Check for errors
                $curlError = curl_error($ch[$i]);

                if ($curlError == "") {
                    $responseContent = curl_multi_getcontent($ch[$i]);
                    //var_dump($responseContent);
                    $header_size = curl_getinfo($ch[$i], CURLINFO_HEADER_SIZE);
                    $header = substr($responseContent, 0, $header_size);
                    $body = json_decode(substr($responseContent, $header_size));
                    //var_dump(stristr($responseContent, 'X-Shopify-Shop-Api-Call-Limit'));
                    $call_Limit = (int)explode(': ', explode('/40', stristr($header, 'X-Shopify-Shop-Api-Call-Limit'), -1)[0])[1];
                    //var_dump($call_Limit);
                    if ($call_Limit >= 25) {
                        $sleep = true;
                    }
                    //var_dump($body);
                    $tmp = (isset($body->product->id)) ? $body->product->id : 0;
                    $model_catalog_product->editProductJan($param['productOpencarId'], $tmp);
                    $this->addProductToCollection($tmp, $param['categories_id']);
                    $res[] = $body;
                } else {
                    print "Curl error on handle $i: $curlError\n";
                }
                // Remove and close the handle
                curl_multi_remove_handle($mh, $ch[$i]);
                curl_close($ch[$i]);
            }
            if ($sleep) {
                sleep(3);
                //print('sleep');
            }

            // Clean up the curl_multi handle
            curl_multi_close($mh);

            // Print the response data
            //print "response data: " . print_r($res, true);
            return $res;
        }
        //return $res;


    }

    public function getProducts($GET = NULL)
    {
        $count = $this->connectAPI('products/count', 'GET');

        if ($count->count > 250) {
            $results = new stdClass();
            $results->products = array();
            $pages = ceil($count->count / 250);
            for ($i = 0; $i < $pages; $i++) {
                $test = $this->connectAPI("products", 'GET', "limit=250&page=" . ($i + 1) . $GET);
                if ($i == 0) {
                    $results->products = $test->products;
                } else {
                    $results->products = array_merge($results->products, $test->products);
                }
            }
        } else {
            $results = $this->connectAPI('products', 'GET', 'limit=250' . $GET);
        }
        //check if empty obj
        if (!empty($results->errors)) {
            return $results->errors;
        }
        //var_dump($results->products);
        return $results->products;
    }

    public function getProduct($shopify_product_id, $GET = NULL)
    {
        $connect = $this->connectAPI("products/{$shopify_product_id}", 'GET', $GET);
        //check if empty obj
        if (!empty($connect->errors)) {
            return $connect->errors;
        }
        return $connect->product;
    }

    public function addProduct($productData = array())
    {
        /*
        //base example
           $productData = [
               'product' => [
                   "title"         => "Burton Custom Freestyle 151",
                   "body_html"     => "<strong>Good snowboard!</strong>",
                   "vendor"        => "Burton",
                   "product_type"  => "Snowboard",
                   'variants'      => [
                       [
                           "option1" => "First",
                           "price"   => 10,
                           "sku"     => "123",
                       ]
                   ],
                   'images'        => [
                       ["attachment" => base64_encode(file_get_contents('/var/www/kokm.loc/image/catalog/fromshopify/fdsgsdfg_720x720.jpg'))]
                   ]
               ]
        ];*/
        $connect = $this->connectAPI("products", 'POST', $productData);
        return $connect;
    }

    public function addCustomCollection($customCollectiontData = array())
    {
        $connect = $this->connectAPI("custom_collections", 'POST', $customCollectiontData);
        return $connect;
    }

    public function downloadImageSizes($url = '', $fileName = 'FileName', $imageSizeVariants = ['720x720'])
    {

        foreach ($imageSizeVariants as $variant) {
            if (!file_exists(DIR_IMAGE . "/catalog/fromshopify/{$fileName}_{$variant}.jpg")) {
                //for stable working file_get_contents
                $context = stream_context_create(
                    array(
                        "http" => array(
                            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                        )
                    )
                );
                $file = str_replace('.jpg', '_' . $variant . '.jpg', $url);
                //$data = file_get_contents($url, false, $context);
                //$new = DIR_IMAGE."/catalog/fromshopify/{$fileName}_{$variant}.jpg";
                //file_put_contents($new, $data);
                //var_dump("(php -f /var/www/kokm.loc/admin/model/module/test.php {$url} {$fileName} {$variant} ".DIR_IMAGE." & ) >> /dev/null 2>&1");
                passthru("(php -f " . DIR_APPLICATION . "model/module/download_image_async.php {$url} {$fileName} {$variant} " . DIR_IMAGE . " & ) >> /dev/null 2>&1");
                return "catalog/fromshopify/{$fileName}_{$variant}.jpg";
            } else {
                return "catalog/fromshopify/{$fileName}_{$variant}.jpg";
            }

        }
    }

    public function moveProductsOpencartToShopify()
    {
        //Custom load model
        $this->load->model('catalog/product');
        $model_catalog_product = $this->registry->get('model_catalog_product');
        $this->load->model('catalog/manufacturer');
        $model_catalog_manufacturer = $this->registry->get('model_catalog_manufacturer');
        $this->load->model('catalog/category');


        //sync category and custom collects shopify
        /*$test = */
        $this->categoryCheckOpencartToShopify();
        //var_dump($test);

        $products = array();
        $image_path = DIR_IMAGE;

        //get all Opencart products
        $productsOP = $model_catalog_product->getProducts();

        //get all Shopify product ID and create array
        $shopifyId_obj = $this->getProducts('&fields=id');
        $shopifyId_array = array();
        foreach ($shopifyId_obj as $value) {
            $shopifyId_array[] = $value->id;
        }
        //var_dump($shopifyId_array);

        $productsData = array();
        foreach ($productsOP as $product) {
            //var_dump($product);exit();
            if (!in_array($product['jan'], $shopifyId_array)) {
                $product['categories_id'] = $model_catalog_product->getProductCategories($product['product_id']);
                $productData = [
                    'product' => [
                        "title" => "{$product['name']}",
                        "body_html" => "{$product['description']}",
                        "vendor" => ($product['manufacturer_id'] > 0) ? $model_catalog_manufacturer->getManufacturerName($product['manufacturer_id'])['name'] : '',
                        "product_type" => "{$product['model']}",
                        "tags" => "{$product['tag']}",
                        "handle" => $this->db->query("SELECT `keyword` FROM " . DB_PREFIX . "url_alias WHERE `query` = 'product_id=" . $product['product_id'] . "'")->row['keyword'],
                        'variants' => [
                            [
                                "option1" => "{$product['model']}",
                                "price" => "{$product['price']}",
                                "sku" => "{$product['sku']}",
                                "inventory_quantity" => "{$product['quantity']}",
                                'inventory_management' => 'shopify'
                            ]
                        ],
                        'images' => [
                            ["attachment" => base64_encode(file_get_contents($image_path . $product['image']))]
                            /*shopify download this
                            ["src" => 'https://i1.rozetka.ua/goods/6419366/philips_hd9650_90_66_images_6419366558.jpg']*/
                        ]
                    ],
                    'productOpencarId' => $product['product_id'],
                    'categories_id' => $product['categories_id']
                ];
                //$tmp = $this->addProduct($productData);

                //update jan in product
//                $model_catalog_product->editProductJan($product['product_id'], $tmp->product->id);
//                $this->addProductToCollection($tmp->product->id, $product['categories_id']);
//                $products[] = $tmp;
//                unset($tpm);
//                var_dump($product);

                $productsData[] = $productData;
            }
        }
        $products = $this->connectAPI_productAdd_async($productsData);
        $return_array = [
            'add_count_products' => count($shopifyId_array),
            'all_count_products' => count($productsOP)
        ];
        return $return_array;
    }

    public function moveProductsShopifyToOpencart()
    {
        //Custom load model
        $this->load->model('catalog/product');
        $model_catalog_product = $this->registry->get('model_catalog_product');
        $this->load->model('catalog/category');

        $productData = array();
        $result = array();

        //create new category and set array Key = shopify collect id, value = Opencart category ID
        $categoryFromShopifyArray = $this->categoryCheckShopifyToOpencart();

        //get array - shopify products id from DB
        $shopifyId = $model_catalog_product->getProductsShopifyId();

        //get array - shopify products from API
        $shopify_API_products = $this->getProducts();

        //try do all in one request
        $this->registry->set('shopify_API_collects', $this->connectAPI("collects", 'GET'));

        foreach ($shopify_API_products as $product) {
            if (!in_array($product->id, $shopifyId)) {
                $productData = array
                (
                    'model' => "{$product->product_type}",
                    'sku' => "{$product->variants[0]->sku}",
                    'upc' => '',
                    'ean' => '',
                    //add in jan id from shopify
                    'jan' => $product->id,
                    'isbn' => '',
                    'mpn' => '',
                    'location' => ':',
                    'quantity' => $product->variants[0]->inventory_quantity,
                    'stock_status_id' => '7',
                    'image' => (!empty($product->image)) ? $this->downloadImageSizes($product->image->src, $product->handle) : '',
                    'manufacturer_id' => $this->addManufacturerByVendor($product->vendor),
                    'product_category' => $this->returnCategoryIdByProductShopifyId($product->id, $categoryFromShopifyArray),
                    'shipping' => '1',
                    'price' => "{$product->variants[0]->price}",
                    'points' => '0',
                    'tax_class_id' => '0',
                    'date_available' => "{$product->published_at}",
                    'weight' => '0.00',
                    'weight_class_id' => '1',
                    'length' => '0.00',
                    'width' => '0.00',
                    'height' => '0.00',
                    'length_class_id' => '1',
                    'subtract' => '1',
                    'minimum' => '1',
                    'sort_order' => "{$product->variants[0]->position}",
                    'status' => '1',
                    'viewed' => '0',
                    'date_added' => "{$product->published_at}",
                    'date_modified' => "{$product->updated_at}",
                    'uktz' => '0',
                    'language_id' => $this->config->get('config_language_id'),
                    'product_store' => [0],
                    'name' => "{$product->product_type}",
                    'product_description' =>
                        [$this->config->get('config_language_id') =>
                            [
                                'name' => "{$product->title}",
                                'description' => "{$product->body_html}",
                                'meta_title' => '',
                                'meta_h1' => '',
                                'meta_description' => '',
                                'meta_keyword' => '',
                                'tag' => "{$product->tags}",
                            ]
                        ],
                    'keyword' => "{$product->handle}"
                );
                $result[] = $model_catalog_product->addProduct($productData);
                //var_dump($productData);
            }
        }
        //return + check count array, if 0 - output message
        return count($result);
    }

    public function addManufacturerByVendor($vendorName)
    {
        //Custom load model
        $this->load->model('catalog/manufacturer');
        $model_catalog_manufacturer = $this->registry->get('model_catalog_manufacturer');


        $manufacturer_id = $model_catalog_manufacturer->getManufacturerIdByName($vendorName);
        if (!empty($manufacturer_id['manufacturer_id'])) {
            return $manufacturer_id['manufacturer_id'];
        } else {
            $manufacturer_id = $this->model_catalog_manufacturer->addManufacturer(array(
                'manufacturer_description' =>
                    [$this->config->get('config_language_id') => [
                        'name' => $vendorName,
                        'description' => '&lt;p&gt;&lt;br&gt;&lt;/p&gt;',
                        'meta_title' => '',
                        'meta_h1' => '',
                        'meta_description' => '',
                        'meta_keyword' => '',
                    ]
                    ],
                'sort_order' => 1,
                'image' => '',
                'manufacturer_store' => [0],
            ));
            return $manufacturer_id;
        }
    }

    public function returnCategoryIdByProductShopifyId($productShopifyId, $categoryFromShopifyArray)
    {
        $categoryId_array = array();
        //$collects = $this->connectAPI("collects", 'GET', "product_id={$productShopifyId}");
        $collects = $this->registry->get('shopify_API_collects')->collects;

        foreach ($collects as $collect) {
            if ($collect->product_id == $productShopifyId) {

                $categoryId_array[] = $categoryFromShopifyArray[$collect->collection_id];
            }
        }

        return $categoryId_array;
    }

    public function categoryCheckShopifyToOpencart()
    {
        $allOpencartCategoriesNameAndId_array = array();
        $allShopifyCategories_array = array();
        $allOpencartCategoriesId_array = array();

        //get model from registry
        $model_catalog_category = $this->registry->get('model_catalog_category');

        //get all collections from shopify
        $allShopifyCustomCollections = $this->connectAPI('custom_collections');
        $allShopifySmartCollections = $this->connectAPI('smart_collections');

        $allOpencartCategoriesName = $model_catalog_category->getCategoriesNameAndId();
        foreach ($allOpencartCategoriesName as $OpencartCategoryName) {
            $allOpencartCategoriesNameAndId_array[$OpencartCategoryName['name']] = $OpencartCategoryName['category_id'];
        }
        //var_dump($allOpencartCategoriesNameAndId_array);


        //var_dump($allShopifyCustomCollections->custom_collections);
        foreach ($allShopifyCustomCollections->custom_collections as $allShopifyCustomCollection) {
            $allShopifyCategories_array[] = [
                'name' => $allShopifyCustomCollection->title,
                'keyword' => $allShopifyCustomCollection->handle,
                'description' => $allShopifyCustomCollection->body_html,
                'id' => $allShopifyCustomCollection->id,
            ];
            //var_dump($allShopifyCustomCollection);
        }

        foreach ($allShopifySmartCollections->smart_collections as $allShopifySmartCollection) {
            $allShopifyCategories_array[] = [
                'name' => $allShopifySmartCollection->title,
                'keyword' => $allShopifySmartCollection->handle,
                'description' => $allShopifySmartCollection->body_html,
                'id' => $allShopifySmartCollection->id,
            ];
            //var_dump($allShopifyCustomCollection);
        }
        //var_dump($allOpencartCategoriesNameAndId_array);
        foreach ($allShopifyCategories_array as $ShopifyCategory) {
            if (!array_key_exists($ShopifyCategory['name'], $allOpencartCategoriesNameAndId_array)) {
                $data = [
                    'parent_id' => 0,
                    'top' => 0,
                    'home' => 0,
                    'column' => 1,
                    'sort_order' => 0,
                    'status' => 1,
                    'category_description' =>
                        [$this->config->get('config_language_id') =>
                            [
                                'name' => $ShopifyCategory['name'],
                                'description' => $ShopifyCategory['description'],
                                'meta_title' => '',
                                'meta_h1' => '',
                                'meta_description' => '',
                                'meta_keyword' => '',
                            ]
                        ],
                    'keyword' => $ShopifyCategory['keyword'],
                    'category_store' => [0],
                    'shopify_category_id' => $ShopifyCategory['id']
                ];
                $categoryId = $model_catalog_category->addCategory($data);
                $allOpencartCategoriesId_array[$ShopifyCategory['id']] = $categoryId;
                //var_dump($data);
                //var_dump($categoryId);
            } else {
                $allOpencartCategoriesId_array[$ShopifyCategory['id']] = $allOpencartCategoriesNameAndId_array[$ShopifyCategory['name']];
            }

        }
        return $allOpencartCategoriesId_array;
        //var_dump($allOpencartCategoriesId_array);
    }

    public function categoryCheckOpencartToShopify()
    {
        $collectionsFromShopify = array();
        //get model from registry
        $model_catalog_category = $this->registry->get('model_catalog_category');

        $allOpencartCategories = $model_catalog_category->getCategoriesDescriptionsAll();
        //var_dump($allOpencartCategories);

        $allShopifyCustomCollections = $this->connectAPI('custom_collections');


        $handleShopifyCollections_array = array();
        //var_dump($allShopifyCustomCollections);
        foreach ($allShopifyCustomCollections->custom_collections as $allShopifyCustomCollection) {
            $handleShopifyCollections_array[] = $allShopifyCustomCollection->handle;
        }
        //var_dump($handleShopifyCollections_array);

        foreach ($allOpencartCategories as $allOpencartCategory) {
            //var_dump($allOpencartCategory);
            $allOpencartCategory['keyword'] = $this->db->query("SELECT `keyword` FROM " . DB_PREFIX . "url_alias WHERE `query` = 'category_id=" . $allOpencartCategory['category_id'] . "'")->row['keyword'];

            if (!in_array($allOpencartCategory['keyword'], $handleShopifyCollections_array)) {
                $data = [
                    'custom_collection' =>
                        [
                            'title' => $allOpencartCategory['name'],
                            'handle' => $allOpencartCategory['keyword']
                        ],
                ];
                $tpm = $this->addCustomCollection($data);
                $collectionsFromShopify[] = $tpm;
                $model_catalog_category->editCategoryShopifyId($allOpencartCategory['category_id'], ['shopify_category_id' => $tpm->custom_collection->id]);
                unset($tpm);
            }
        }
        //test
        $tmp1 = $model_catalog_category->getCategoriesAll();
        $this->registry->category_OpencartId_ShopifyId = array();
        foreach ($tmp1 as $categoryOpencart) {
            $this->registry->category_OpencartId_ShopifyId[$categoryOpencart['category_id']] = $categoryOpencart['shopify_category_id'];
        }

        return $allOpencartCategories;
        //var_dump(in_array('7u',$allOpencartCategories[0]));
    }

    public function addProductToCollection($productShopifyId = 0, $categories_array = array())
    {
        foreach ($categories_array as $category) {
            $data = [
                'collect' =>
                    [
                        'product_id' => $productShopifyId,
                        'collection_id' => $this->registry->category_OpencartId_ShopifyId[$category]
                    ],
            ];
            $connect = $this->connectAPI('collects', 'POST', $data);
        }
    }

    public function getTimeFromScriptStart()
    {
        $time = microtime(true);
        $time2 = $_SERVER["REQUEST_TIME_FLOAT"];
        return ($time - $time2) * 1000;
    }

    public function delete_cache()
    {
        $result = 0;
        foreach (new DirectoryIterator(DIR_CACHE) as $file) {
            if ($file->isFile() and strpos($file->getFilename(), 'shopify') !== false) {
                unlink($file->getRealPath());
                $result++;
            }
        }
        return $result;
    }
}