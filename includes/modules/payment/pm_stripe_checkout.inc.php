<?php

  class pm_stripe_checkout {
    public $id = __CLASS__;
    public $name = 'Stripe Checkout';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $website = 'https://www.stripe.com/';
    public $priority = 0;

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

    // If not enabled
      if (empty($this->settings['status'])) return;

      $options = [];

      if (in_array('alipay', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && $customer['country_code'] == 'CN') {
        $options[] = [
          'id' => 'alipay',
          'icon' => 'images/payment/alipay.png',
          'name' => 'AliPay',
          'description' => language::translate(__CLASS__.':description_alipay', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
        ];
      }

      if (in_array('ban', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && $customer['country_code'] == 'BE') {
        $options[] = [
          'id' => 'ban',
          'icon' => 'images/payment/bancontact.png',
          'name' => 'Bancontact',
          'description' => language::translate(__CLASS__.':description_bancontact', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('card', preg_split('#\s*,\s*#', $this->settings['payment_methods']))) {
        $options[] = [
          'id' => 'card',
          'icon' => 'images/payment/cards.png',
          'name' => language::translate(__CLASS__.':title_card_payment', 'Card Payment'),
          'description' => language::translate(__CLASS__.':description_card_payment', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
        ];
      }

      if (in_array('eps', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && $customer['country_code'] == 'AT') {
        $options[] = [
          'id' => 'eps',
          'icon' => 'images/payment/eps.png',
          'name' => 'EPS',
          'description' => language::translate(__CLASS__.':description_eps', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('giropay', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && $customer['country_code'] == ['BE', 'DE', 'NL']) {
        $options[] = [
          'id' => 'giropay',
          'icon' => 'images/payment/giropay.png',
          'name' => 'GiroPay',
          'description' => language::translate(__CLASS__.':description_giropay', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('ideal', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && in_array($customer['country_code'], ['BE', 'DE', 'NL'])) {
        $options[] = [
          'id' => 'ideal',
          'icon' => 'images/payment/ideal.png',
          'name' => 'iDeal',
          'description' => language::translate(__CLASS__.':description_ideal', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('p24', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && $customer['country_code'] == 'PL') {
        $options[] = [
          'id' => 'p24',
          'icon' => 'images/payment/przelewy24.png',
          'name' => 'Przelewy24',
          'description' => language::translate(__CLASS__.':description_przelewy24', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('sofort', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && in_array($customer['country_code'], ['AT', 'BE', 'BG', 'DE', 'IT', 'NL'])) {
        $options[] = [
          'id' => 'sofort',
          'icon' => 'images/payment/sofort.png',
          'name' => 'Sofort',
          'description' => language::translate(__CLASS__.':description_sofort', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => ($currency_code != 'EUR') ? language::translate(__CLASS__.':error_only_eur_is_supported_for_this_option', 'Only currency EUR is supported for this option') : '',
        ];
      }

      if (in_array('wechat', preg_split('#\s*,\s*#', $this->settings['payment_methods'])) && in_array($customer['country_code'], ['CN'])) {
        $options[] = [
          'id' => 'wechat',
          'icon' => 'images/payment/wechat.png',
          'name' => 'WeChat',
          'description' => language::translate(__CLASS__.':description_wechat', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          'error' => false,
        ];
      }

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

      // Workaround because Stripe does not support negative values
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
          'default_value' => 'images/payment/cards.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
          'function' => 'text()',
        ],
        [
          'key' => 'publishable_key',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_publishable_key', 'Publishable Key'),
          'description' => language::translate(__CLASS__.':description_publishable_key', 'Your publishable key obtained by Stripe.'),
          'function' => 'text()',
        ],
        [
          'key' => 'secret_key',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_secret_key', 'Secret Key'),
          'description' => language::translate(__CLASS__.':description_secret_key', 'Your secret key obtained by Stripe.'),
          'function' => 'text()',
        ],
        [
          'key' => 'payment_methods',
          'default_value' => 'alipay, card, sepa_debit, sofort, ban, eps, giropay, ideal, p24, wechat',
          'title' => language::translate(__CLASS__.':title_payment_methods', 'Payment Methods'),
          'description' => language::translate(__CLASS__.':description_payment_methods', 'A comma separated list of payment methods e.g. card, ideal'),
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
