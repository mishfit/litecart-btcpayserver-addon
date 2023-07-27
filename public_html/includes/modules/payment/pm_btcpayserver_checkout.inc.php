<?php

  class pm_btcpayserver_checkout {
    public $id = __CLASS__;
    public $name = 'BTCPayServer Checkout';
    public $description = 'Use BTCPayServer to checkout.';
    public $author = 'Mishael Ochu';
    public $version = '1.0';
    public $website = 'https://btcpayserver.org/';
    public $priority = 0;

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

    // If not enabled
      if (empty($this->settings['status'])) return;

      $forbidden_items = $this-> settings['forbidden_items'];
      if (!empty($forbidden_items)) {
        foreach ($items as $item) {

          $product_id = $item['product_id'];

          $result = array_search(strval($product_id), $forbidden_items);

          if (!is_bool($result)) {
            $log_message = '['. date('Y-m-d H:i:s e').'] product is **forbidden**: ' . json_encode($item['name']) . PHP_EOL . PHP_EOL;
            file_put_contents(FS_DIR_STORAGE . 'logs/debug.log', $log_message, FILE_APPEND);
            return;
          } else {

          }
        }
      }

      $options = [];

      $options[] = [
        'id' => 'btcpay',
        'icon' => 'images/payment/btcpayserver.png',
        'name' => language::translate(__CLASS__.':title_card_payment', 'BTCPayserver Payment'),
        'description' => language::translate(__CLASS__.':description_card_payment', ''),
        'fields' => '',
        'cost' => 0,
        'tax_class_id' => 0,
        'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
      ];

      return [
        'title' => $this->name,
        'options' => $options,
      ];
    }

    public function transfer($order) {

      try {

        $order->save(); // Create order ID

        $request = [
          'metadata' => [
              'orderId' => $order->data['id'],
              'orderUrl' => document::link(WS_DIR_ADMIN, ['doc' => 'edit_order'], ['app' => 'orders'], ['order_id' => $order->data['id']] ),
              'buyerName' => $order->data['customer']['firstname'].' '.$order->data['customer']['lastname'],
              'buyerEmail' => $order->data['customer']['email'],
              'buyerAddress1' => $order->data['customer']['address1'],
              'buyerAddress2' => $order->data['customer']['address2'],
              'buyerPhone' => $order->data['customer']['phone'],
              'buyerCity' => $order->data['customer']['city'],
              'buyerState' => $order->data['customer']['zone_code'],
              'buyerZip' => $order->data['customer']['postcode'],
              'buyerCountry' => $order->data['customer']['country_code'],
          ],
          'checkout' => [
              'speedPolicy' => 'HighSpeed',
              'expirationMinutes' => 90,
              'redirectAutomatically' => true,
              'redirectURL' => document::ilink('order_process'),
          ],
          'currency' => $order->data['currency_code'],
          'amount' => $order->data['payment_due'],
          'cancel_url' => document::ilink('checkout'),
        ];

        foreach ($order->data['items'] as $item) {
            $request['metadata']['line_items'][] = [
            'images' => [
              document::link(WS_DIR_APP .'images/'. reference::product($item['product_id'])->image),
            ],
            'name' => $item['name'],
            'quantity' => (float)$item['quantity'],
            'amount' => $this->_amount($item['price'] + $item['tax'], $order->data['currency_code'], $order->data['currency_value']),
            'currency' => $order->data['currency_code'],
          ];
        }

        $result = $this->_call('POST', '/stores/'.$this->settings['store_id'].'/invoices' , $request);

        session::$data['btcpayserver']['id'] = $result['id'];

        return [
          'method' => 'GET',
          'action' => $result['checkoutLink'],
        ];

      } catch (Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }

    public function verify($order) {
      try {
        if (empty(session::$data['btcpayserver']['id'])) {
          throw new Exception('Missing invoice id');
        }
        $result = $this->_call('GET', '/stores/'.$this->settings['store_id'].'/invoices/'.session::$data['btcpayserver']['id']);
        if (empty($result['status'])) {
          throw new Exception('Payment status not succeeded');
        }

        // if the transaction is already settled set Litecart to 'Processing'
        if ($result['status'] == 'Settled') {
          return [
            'order_status_id' => 'Processing',
          ];
        }

        // if the transaction is expired set Litecart to 'Cancelled'
        if ($result['status'] == 'Expired') {
          return [
            'order_status_id' => 'Cancelled',
          ];
        }

        // handles 'Pending', 'Processing'
        // (there's a one-to-one relationship with these  statuses for BTCPayServer -> Litecart)
        return [
          'order_status_id' => $result['status'],
        ];
      } catch (Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }


    private function _amount($amount, $currency_code, $currency_value) {

    // Zero-decimal currencies
      if (in_array($currency_code, explode(',', 'BIF,CLP,DJF,GNF,JPY,KMF,KRW,MGA,PYG,RWF,UGX,VND,VUV,XAF,XOF,XPF'))) {
        return currency::format_raw($amount, $currency_code, $currency_value);
      }

      return currency::format_raw($amount, $currency_code, $currency_value) * 100;
    }

    private function _call($method, $endpoint, $request = null) {
      $client = new wrap_http();

      $headers = [
        'Authorization' => 'token '. $this->settings['access_token'],
        'X-BTCPAYSERVER-CLIENT-USER-AGENT' => json_encode([
          'lang' => 'php',
          'publisher' => 'litecart',
          'application' => [
            'url' => 'https://litecart.net/',
            'version' => $this->version,
            'name' => 'BTCPay Server for LiteCart'
          ],
        ], JSON_UNESCAPED_SLASHES),
        'BTCPayServer-Version' => '1.6.0',
        'Content-Type' => 'application/json',
      ];

      $url = $this->settings['btcpayserver_url'];

      $response = $client->call($method, $url.$endpoint, $request ? json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '', $headers);


      $result = json_decode($response, true);
      if (empty($result)) {
        throw new Exception('Invalid response from remote machine');
      }

      // TODO: BTCPayServer does not throw errors in this way...have to check for errors in other ways
      if (!empty($result['error'])) {
        throw new Exception($result['error']['message']);
      }

      return $result;
    }

    function settings() {
      return [
        [
          'key' => 'store_id',
          'default_value' => '{your store id}',
          'title' => language::translate(__CLASS__.':title_store_id', 'Store Id'),
          'description' => language::translate(__CLASS__.':description_store_id', 'BTCPayServer Store ID.'),
          'function' => 'text()',
        ],
        [
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'icon',
          'default_value' => 'images/payment/btcpayserver.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
          'function' => 'text()',
        ],
        [
          'key' => 'btcpayserver_url',
          'default_value' => 'https://{example-btcpayserver-url}/api/v1',
          'title' => language::translate(__CLASS__.':title_btcpayserver_url', 'BTC Pay Server URL'),
          'description' => language::translate(__CLASS__.':description_btcpayserver_url', 'Your BTCPayServer instance URL.'),
          'function' => 'text()',
        ],
        [
          'key' => 'access_token',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_publishable_key', 'Access Token'),
          'description' => language::translate(__CLASS__.':description_publishable_key', 'Your access token from BTCPayServer'),
          'function' => 'text()',
        ],
        [
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ],
        [
          'key' => 'forbidden_items',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_forbidden_Items', 'Forbidden Items'),
          'description' => language::translate(__CLASS__.':description_forbidden_items', 'A comma separated list of items (by product ID#) for which this module should be disabled.'),
          'function' => 'products()',
          'multiple' => 'true',
        ],
      ];
    }

    public function install() {}

    public function uninstall() {}
  }
