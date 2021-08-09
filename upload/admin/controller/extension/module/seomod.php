<?php
// v1.0.0
class ControllerExtensionModuleSeoMod extends Controller {
	private $modulePath = 'extension/module/seomod';
	private $moduleName = 'seomod';
	private $events = [
		'model_catalog_category_editCategory_before' => [
			'code'      =>  'seomod_model_catalog_category_editCategory_before',
			'trigger'   =>  'admin/model/catalog/category/editCategory/before',
			'action'    =>  'extension/module/seomod/unsetSeoUrl',
			'sort_order'    =>  0,
			'status'        =>  0,
		],
		'model_catalog_product_editProduct_before' => [
			'code'      =>  'seomod_model_catalog_product_editProduct_before',
			'trigger'   =>  'admin/model/catalog/product/editProduct/before',
			'action'    =>  'extension/module/seomod/unsetSeoUrl',
			'sort_order'    =>  0,
			'status'        =>  0,
		],
		'model_catalog_information_editInformation_before' => [
			'code'      =>  'seomod_model_catalog_information_editInformation_before',
			'trigger'   =>  'admin/model/catalog/information/editInformation/before',
			'action'    =>  'extension/module/seomod/unsetSeoUrl',
			'sort_order'    =>  0,
			'status'        =>  0,
		],
		'model_catalog_manufacturer_editManufacturer_before' => [
			'code'      =>  'seomod_model_catalog_manufacturer_editManufacturer_before',
			'trigger'   =>  'admin/model/catalog/manufacturer/editManufacturer/before',
			'action'    =>  'extension/module/seomod/unsetSeoUrl',
			'sort_order'    =>  0,
			'status'        =>  0,
		],
	];

	public function index() {
		$this->load->language($this->modulePath);
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$data = [
				'status'    =>  $this->request->post['status'],
				'name'      =>  $this->moduleName,
			];

			if ( 1 == 1 ) { // <!-- Збереження даних в конфіг.
				$this->load->model('setting/setting');
				$this->model_setting_setting->editSetting('module_' . $this->moduleName,
					[
						'module_' . $this->moduleName . '_status' => $this->request->post['status'],
					]
				);
			} // --> Збереження даних в конфіг.

			$this->load->model('setting/event');
			foreach ($this->events as $event) {
				$stored_event = $this->model_setting_event->getEventByCode($event['code']);
				if ($stored_event['event_id']) {
					$null = $data['status']?    $this->model_setting_event->enableEvent($stored_event['event_id'])
											:   $this->model_setting_event->disableEvent($stored_event['event_id']);
				}
				else {
					$event['status'] = $data['status'];
					$this->model_setting_event->addEvent($event['code'], $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if ( 1 == 1 ) { // <!-- environment section
			if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
			} else {
				$data['error_warning'] = false;
			}

			// breadcrumbs
			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['status'] = $this->config->get('module_' . $this->moduleName . '_status');
			$data['action'] = $data['action'] = $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'], true);
			$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		} // --> environment section

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->modulePath, $data));
	}
	public function unsetSeoUrl(&$route, &$args) {
		$module = explode('/', $route)[1];
		unset($args[1][$module . '_seo_url']);
	}

	public function validate() {
		return true;
	}
	public function install() {
		$this->load->model('setting/event');
		foreach ($this->events as $event) {
			$stored_event = $this->model_setting_event->getEventByCode($event['code']);
			unset($stored_event['event_id']);
			if(empty($stored_event) || $stored_event != $event) { //event update/install
				$this->model_setting_event->deleteEventByCode($event['code']);
				$this->model_setting_event->addEvent($event['code'], $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
			} //event update/install
		}

		$this->load->model('setting/modification');
		$ocmod = $this->model_setting_modification->getModificationByCode('Seo-url prevent update');
		if (!empty($ocmod['modification_id'])) {
			$this->model_setting_modification->enableModification($ocmod['modification_id']);
		}
	}
	public function uninstall() {
		$this->load->model('setting/event');
		foreach ($this->events as $event) {
			$this->model_setting_event->deleteEventByCode($event['code']);
		}

		$this->load->model('setting/modification');
		$ocmod = $this->model_setting_modification->getModificationByCode('Seo-url prevent update');
		if (!empty($ocmod['modification_id'])) {
			$this->model_setting_modification->disableModification($ocmod['modification_id']);
		}
	}
}