<?php

  class pm_stripe_checkout {
    public $id = __CLASS__;
    public $name = 'BTCPayServer Checkout';
    public $description = '';
    public $author = 'Nokware, LLC';
    public $version = '1.0';
    public $website = 'https://btcpayserver.org/';
    public $priority = 0;

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

    // If not enabled
      if (empty($this->settings['status'])) return;

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
          'mode' => 'payment',
          'locale' => $order->data['language_code'],
          'client_reference_id' => $order->data['id'],
          'payment_intent_data' => [
            'description' => 'Order ' . $order->data['id'],
          ],
          'payment_method_types' => [preg_replace('#^.*:#', '', $order->data['payment_option']['id'])],
          'line_items' => [],
          'success_url' => document::ilink('order_process'),
          'cancel_url' => document::ilink('checkout'),
        ];

        if ($customer_id = $this->_get_remote_customer_id($order->data['customer']['email'])) {
          $request['customer'] = $customer_id;
        } else {
          $request['customer_email'] = $order->data['customer']['email'];
        }

        foreach ($order->data['items'] as $item) {
          if ($item['price'] <= 0) continue;
          $request['line_items'][] = [
            'images' => [
              document::link(WS_DIR_APP .'images/'. reference::product($item['product_id'])->image),
            ],
            'name' => $item['name'],
            'quantity' => (float)$item['quantity'],
            'amount' => $this->_amount($item['price'] + $item['tax'], $order->data['currency_code'], $order->data['currency_value']),
            'currency' => $order->data['currency_code'],
          ];
        }

        foreach ($order->data['order_total'] as $row) {
          if (empty($row['calculate'])) continue;
          $request['line_items'][] = [
            'name' => $row['title'],
            'quantity' => 1,
            'amount' => $this->_amount($row['value'] + $row['tax'], $order->data['currency_code'], $order->data['currency_value']),
            'currency' => $order->data['currency_code'],
          ];
        }

      // TODO: check to see if BTCPayServer needs this Workaround (because Stripe does not support negative values)
        foreach ($request['line_items'] as $item) {
          if ($item['amount'] < 0) {
            $request['line_items'] = [[
              'name' => 'Order '. $order->data['id'],
              'quantity' => 1,
              'amount' => $this->_amount($order->data['payment_due'], $order->data['currency_code'], $order->data['currency_value']),
              'currency' => $order->data['currency_code'],
            ]];
            break;
          }
        }

        $result = $this->_call('POST', '/checkout/sessions', $request);

        session::$data['stripe']['payment_intent_id'] = $result['payment_intent'];

        return [
          'method' => 'GET',
          'action' => $result['url'],
        ];

      } catch (Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }

    public function verify($order) {

      try {
        if (empty(session::$data['stripe']['payment_intent_id'])) {
          throw new Exception('Missing payment intent id');
        }

        $result = $this->_call('GET', '/payment_intents/'. session::$data['stripe']['payment_intent_id']);

        if ($result['status'] != 'succeeded') {
          throw new Exception('Payment status not succeeded');
        }

        return [
          'order_status_id' => $this->settings['order_status_id'],
          'transaction_id' => $result['id'],
        ];

      } catch (Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }

    private function _get_remote_customer_id($email) {

      $result = $this->_call('POST', '/customers?email='. urlencode($email));

      if (!empty($result['data'][0]['id'])) {
        return $result['data'][0]['id'];
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
        'Authorization' => 'Bearer '. $this->settings['secret_key'],
        'X-STRIPE-CLIENT-USER-AGENT' => json_encode([
          'lang' => 'php',
          'publisher' => 'litecart',
          'application' => [
            'url' => 'https://litecart.net/',
            'version' => $this->version,
            'partner_id' => 'pp_partner_DaR9Lw5TGJwRbK',
            'name' => 'Stripe Checkout for LiteCart'
          ],
        ], JSON_UNESCAPED_SLASHES),
        'Stripe-Version' => '2020-08-27',
      ];

      $response = $client->call($method, 'https://api.stripe.com/v1'.$endpoint, $request, $headers);

      if (!$result = json_decode($response, true)) {
        throw new Exception('Invalid response from remote machine');
      }

      if (!empty($result['error'])) {
        throw new Exception($result['error']['message']);
      }

      return $result;
    }

    function settings() {
      return [
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
      ];
    }

    public function install() {}

    public function uninstall() {}
  }
