<?php
class ControllerModuleShopifyOpencart extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('module/shopify_opencart');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['name']) && isset($this->request->post['token'])) {
            if ($this->validate()) {
                if (!isset($this->request->get['module_id'])) {
                    $this->model_extension_module->addModule('shopify_opencart', $this->request->post);
                } else {
                    $this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
                }
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_token'] = $this->language->get('entry_token');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        //default $data['created']
        $data['created'] = false;

        //created stuff. do this if $data['created'] == true
        $data['text_shopify_opencart'] = $this->language->get('text_shopify_opencart');
        $data['button_shopify_to_opencart'] = $this->language->get('button_shopify_to_opencart');;
        $data['button_opencart_to_shopify'] = $this->language->get('button_opencart_to_shopify');;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/shopify_opencart', 'token=' . $this->session->data['token'], 'SSL')
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/shopify_opencart', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('module/shopify_opencart', 'token=' . $this->session->data['token'], 'SSL');
        } else {
            $data['action'] = $this->url->link('module/shopify_opencart', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');


        //If module exists - Do this
        if (isset($this->request->get['module_id'])) {
            $module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
            //If we on created page
            $data['created'] = true;
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
            //add this for __construct func
            $this->shopifyName = $data['name'];
            //////////////////////////////////
        } else {
            $data['name'] = '';
        }
        if (isset($this->request->post['token'])) {
            $data['token'] = $this->request->post['token'];
        } elseif (!empty($module_info)) {
            $data['token'] = $module_info['token'];
            //add this for __construct func
            $this->shopifyToken = $data['token'];
            ///////////////////////////////////////
        } else {
            $data['token'] = '';
        }
        ///////////////////////If module exist

        if ($data['created']) {
            $this->load->model('module/shopify_opencart');
            if (isset($this->request->post['productsOpencartToShopify'])) {
                $test = $this->model_module_shopify_opencart->moveProductsOpencartToShopify();
                die(json_encode($test));
            }
            if (isset($this->request->post['productsShopifyToOpencart'])) {
                $test = $this->model_module_shopify_opencart->moveProductsShopifyToOpencart();
                die(json_encode($test));
            }
            if (isset($this->request->post['delete_cache'])) {
                $test = $this->model_module_shopify_opencart->delete_cache();
                die(json_encode($test));
            }

        }
///////////////////////////////////////////////////////////////
        //var_dump($data);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('module/shopify_opencart.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/shopify_opencart')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }

}
