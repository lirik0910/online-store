<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Model\Base\Setting;
use App\SimpleHtmlDom as simple_html_dom;


class CalculateController
{
    public function checkMethod(Request $request){

        //var_dump($request->post()); die;
        $action = $request->post('action');
        switch ($action){
            case 'parse_btc_network_status':
                return $this->parse_btc_network_status($request);
            case 'parse_btc_courses_calc':
                return $this->parse_btc_courses_calc($request);

        }
        //return $this->$action;

    }

    public function parse_btc_courses_calc(Request $request, $die = 0){
        $source = $request->post('src');// $_GET['src'];
        //var_dump($source); die;
        //$CryptoTickerWidget = new CryptoTickerWidget();

        // $uah $rur
        //------------
        $url['USD / UAH'] = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&date='.date('Ymd').'&json';
        $json = $this->get_data($url['USD / UAH']);
        $decoded  = json_decode($json, true);
        $uah = $decoded[0]['rate'];
        $data['USD / UAH'] = $uah;


        $url['USD / RUR'] = 'https://www.cbr-xml-daily.ru/daily_json.js' ;
        $json = $this->get_data($url['USD / RUR']);
        $decoded  = json_decode($json, true);
        $rur = $decoded['Valute']['USD']['Value'];
        $data['USD / RUR'] = $rur;


        $url['EUR / UAH'] = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=EUR&date='.date('Ymd').'&json';
        $json = $this->get_data($url['EUR / UAH']);
        $decoded  = json_decode($json, true);
        $euruah = $decoded[0]['rate'];


        $calc = $this->parse_btc_course_calculated();

        $calcBCH = json_decode(Setting::where('title', 'others_crypth')->first()['value'])['BCH'];
        $calcLTC = json_decode(Setting::where('title', 'others_crypth')->first()['value'])['LTC'];
        $calcDASH = json_decode(Setting::where('title', 'others_crypth')->first()['value'])['DASH'];
        //var_dump($calc, $euruah, $uah); die;
        $data['base']['BTC / USD'] = $calc;
        $data['base']['BTC / EUR'] = $calc / ($euruah / $uah) ;
        $data['base']['BTC / UAH'] = $calc * $uah;
        $data['base']['BTC / RUR'] = $calc * $rur;

        $data['base']['BCH / USD'] = $calcBCH;
        $data['base']['BCH / EUR'] = $calcBCH / ($euruah / $uah) ;
        $data['base']['BCH / UAH'] = $calcBCH * $uah;
        $data['base']['BCH / RUR'] = $calcBCH * $rur;

        $data['base']['LTC / USD'] = $calcLTC;
        $data['base']['LTC / EUR'] = $calcLTC / ($euruah / $uah) ;
        $data['base']['LTC / UAH'] = $calcLTC * $uah;
        $data['base']['LTC / RUR'] = $calcLTC * $rur;

        $data['base']['DASH / USD'] = $calcDASH;
        $data['base']['DASH / EUR'] = $calcDASH / ($euruah / $uah) ;
        $data['base']['DASH / UAH'] = $calcDASH * $uah;
        $data['base']['DASH / RUR'] = $calcDASH * $rur;

        if($source){
            foreach ($data['base'] as $key => $value) {
                ?>
                <li><span class="btc-table__currency"><?php echo $key ?></span> <span class="btc-table__total"><?php echo round($value,2) ?></span></li>
                <?php
            }
        }

        if ($request->post('action') != 'calc_btc_profit' && $die != 1)
            die();
        else return  $data;
    }

    public function parse_btc_network() {
        //$CryptoTickerWidget = new CryptoTickerWidget();
        $url  = 'https://chain.api.btc.com/v3/block/latest?_ga=2.243435013.1001709445.1506057444-713996762.1506057444';
        $json = $this->get_data($url );
        $decoded  = json_decode($json, true);

        $network['difficulty'] = $decoded['data']['difficulty'] ;
        $network['reward_block'] = $decoded['data']['reward_block']  ;


        return $network;

    }

    public function parse_others_network_status($die,$currency='BCH', Request $request) {

        $html = new simple_html_dom('');

        if ($request->post('currency'))
            $currency = $request->post('currency');

        switch ($currency) {

            case 'BCH':
                $html = $html->file_get_html( 'https://bitinfocharts.com/ru/bitcoin%20cash/'  );
                break;

            case 'LTC':
                $html = $html->file_get_html( 'https://bitinfocharts.com/ru/litecoin/'  );
                break;

            case 'DASH':
                $html = $html->file_get_html( 'https://bitinfocharts.com/ru/dash/'  );
                break;
        }

        foreach($html->find('#tdid16') as $element) {
            $data['hashrate'] = $element->innertext;
            //$data['hashrate'] = explode(' ', $data['hashrate'])[0]  ;
        }
        foreach($html->find('#tdid15') as $element) {
            $data['difficulty'] = $element->innertext;
            $data['difficulty'] = explode(' ', $data['difficulty'])[0];

            $network['difficulty'] = explode(' ', $data['difficulty'])[0];
            $network['difficulty'] = str_replace(',','', $network['difficulty'])/1000000000000;

        }
        foreach($html->find('#tdid32') as $element) {
            $data['mining'] = $element->innertext;
            //$data['mining'] = explode(' ', $data['mining'])[0];
        }

        foreach($html->find('#tdid13') as $element) {
            $data['reward'] = $element->innertext;
            foreach($element->find('abbr[title="block reward"]') as $elements) {

                $network['reward_block'] = $elements->innertext;
            }
        }

        $t = 86400;
        $R = $network['reward_block'];
        $D = $network['difficulty'] ;
        $H = 1;

        $P =  number_format(($t*$R*$H)/($D*(2**32)), 8)  ;

        $TH = 'T';
        $THT = 'EH';
        if ($currency == 'LTC') {
            $calcLTC = $this->parse_btc_courses_others($currency);
            $data['mining'] = trim(explode(' ', $data['mining'])[0]);
            $P = number_format($data['mining']/$calcLTC, 8)  ;
            $network['p'] = $P;
            $TH = 'MH';
            $THT = 'TH';
        }

        if ($currency == 'DASH') {
            $calcDASH = $this->parse_btc_courses_others($currency);
            $data['mining'] = trim(explode(' ', $data['mining'])[0]);
            $P = number_format($data['mining']/$calcDASH, 8)  ;
            $network['p'] = $P;
            $TH = 'GH';
            $THT = 'TH';
        }


        if ($request->post('action') != 'calc_btc_profit' && $die != 1) {


            ?>
            <div class="network-status--title "><?php _e('Статус сети', 'al') ?></div>
            <div class="network-status--parent">
                <div class="network-status--inner">
                    <div><?php _e('Хэшрейт', 'al') ?></div>
                    <div><?php echo ($data['hashrate'])  ?> <?php //echo $THT ?>/s</div>
                </div>

                <div class="network-status--inner">
                    <div><?php _e('Сложность', 'al') ?></div>
                    <div><?php echo $data['difficulty'] ?></div>
                </div>

                <div class="network-status--inner network-delimiter">
                    <div><?php _e('Добыча', 'al') ?></div>
                    <div>1<?php echo $TH ?> * 24H = <?php echo $P ?> <?php echo $currency ?></div>
                </div>



            </div>

            <?php
            die();
        }  else
            return    $network;

    }

    public function update_devices(Request $request) {
        $cur='';
        if ($request->post('currency')) {
            $cur = $request->post('currency');
        }

        ?>

        <div class="calculator-form--item ">
            <div class="width-60">
                <select id="device" name="device">
                    <option value="hide" ><?php _e('Устройство', 'al') ?></option>
                    <option value="" data-hr="0"><?php _e('Ручной ввод', 'al') ?></option>
                    <?php
                    while (have_rows('устройства', 2319)) {
                        the_row();
                        $allowed = explode(',', get_sub_field('валюта'));
                        if (!in_array($cur, $allowed) )
                            continue;
                        ?>
                        <option data-currency="<?php the_sub_field('валюта') ?>" data-hr="<?php the_sub_field('хешрейт') ?>" data-en="<?php the_sub_field('энергопотребление') ?>" value="<?php the_sub_field('название') ?>"><?php the_sub_field('название') ?></option>
                    <?php } ?>

                </select>
            </div>

            <input type="number"  step="1" class="quantity width-33 quantity-center" id="quantity" name="qty" placeholder="1 <?php _e('шт', 'al') ?>" min="1" readonly>
        </div>

        <div class="calculator-form--item cur-LTC">
            <input type="number" step="0.01" class="quantity width-60 hash" name="hash" placeholder="<?php _e('Введите хешрейт', 'al') ?>"  >

            <div class="width-33 cur-LTC-ul">
                <select id="ghs" name="powers">
                    <?php if ($cur == 'LTC') { ?>
                        <option value="1" selected>MH/s</option>
                    <?php }
                    elseif ($cur == 'DASH') { ?>
                        <option value="1" selected>GH/s</option>
                    <?php } else { ?>
                        <option  value="0.001" selected >TH/s</option>

                    <?php } ?>

                </select>
            </div>
        </div>


        <?php


        die();
    }

    public function parse_btc_network_status(Request $request, $die = 0) {

        $html = new simple_html_dom('');
        $html = $html->file_get_html( 'https://btc.com/'  );
var_dump($html); die;
        foreach($html->find('.indexNetworkStats dt') as $element) {
            if ($element->innertext == 'Hashrate')
                $data['hashrate'] = $element->parent()->find('dd', 0)->innertext;

            if ($element->innertext == 'Next Difficulty Estimated') {
                $text = $element->parent()->find('dd', 0)->innertext;
                $data['expected_difficulty_raw'] = $text;
                preg_match('#\((.*?)\)#', $text, $match);

                $data['expected_difficulty'] = (float)$match[1];
                if ( substr($data['expected_difficulty'], 1) == '-' )
                    $data['expected_difficulty'] = $data['expected_difficulty'] * (-1);
            }

            if ($element->innertext == 'Date to Next Difficulty') {
                $text = $element->parent()->find('dd', 0)->innertext;
                $data['expected_difficulty_date'] = $element->parent()->find('dd', 0)->innertext;

            }


        }
        $network = $this->parse_btc_network();

        $t = 86400;
        $R = $network['reward_block']/1000000000;
        $D = $network['difficulty']/10000000000000 ;
        $H = 1;

        $P =  number_format(($t*$R*$H)/($D*(2**32)), 8)  ;

        if ($request->post('action') != 'calc_btc_profit' && $die != 1) {


            ?>
            <div class="network-status--title "><?php _e('Статус сети', 'al') ?></div>
            <div class="network-status--parent">
                <div class="network-status--inner">
                    <div><?php _e('Хэшрейт', 'al') ?></div>
                    <div><?php echo $data['hashrate'] ?></div>
                </div>

                <div class="network-status--inner">
                    <div><?php _e('Сложность', 'al') ?></div>
                    <div><?php echo $network['difficulty'] ?></div>
                </div>

                <div class="network-status--inner network-delimiter">
                    <div><?php _e('Добыча', 'al') ?></div>
                    <div>1T * 24H = <?php echo $P ?> BTC</div>
                </div>

                <div class="network-status--inner">
                    <div><?php _e('Ожидаемая следующая сложность', 'al') ?></div>
                    <div><?php echo $data['expected_difficulty_raw'] ?></div>
                </div>

                <div class="network-status--inner">
                    <div><?php _e('Дата следующей сложности', 'al') ?></div>
                    <div><?php echo $data['expected_difficulty_date'] ?></div>
                </div>

            </div>

            <?php
            die();
        }  else
            return    $data;


    }

    public function calc_btc_profit(Request $request) {

        //profit data

        //$network = parse_btc_network();
        $network = json_decode(stripslashes( $request->post('network')), 1);
        //$network_status =  parse_btc_network_status();
        $network_status =  json_decode(stripslashes( $request->post('status')), 1);
        //$coursers = parse_btc_courses_calc();
        $coursers =  json_decode(stripslashes( $request->post('calc')), 1);
        $source = 'base';
        $days = $request->post('days') ? $request->post('days') : 1;
        $expected_difficulty = $network_status['expected_difficulty']/100+1;

        $powers = $request->post('powers');
        $placements = $request->post('radio');




        $t = 86400;
        $R = $network['reward_block']/1000000000;
        $D = $network['difficulty']/10000000000 ;
        $H = $_GET['hash'] / $powers;

        $currency = $_GET['currency'];
        if ($currency === 'BCH') {
            $network = $this->parse_others_network_status($currency, $request);
            $R = $network['reward_block'] ;
            $D = $network['difficulty']*1000 ;

        }


        $P =  number_format(($t*$R*$H)/($D*(2**32)), 7) * $days;

        if ($currency === 'LTC') {
            $network = $this->parse_others_network_status($currency, $request);
            $calcLTC = $this->parse_btc_courses_others($currency);
            $P = $network['p'] ;
            $TH = 'MH';
            $P =  number_format( $P*$_GET['hash'] , 6) * $days;
        }

        if ($currency === 'DASH') {
            $network = $this->parse_others_network_status($currency, $request);
            $calcLTC = $this->parse_btc_courses_others($currency);
            $P = $network['p'] ;
            $TH = 'GH';
            $P =  number_format( $P*$_GET['hash'] , 6) * $days;
        }


        //costs data

        if ($placements == 2) {
            $energy = $request->post('energy');
            $energy_costs = $request->post('costs');
            $energy_costs = $energy_costs * 24;

        } else {
            $qty = $request->post('qty') ? $request->post('qty') : 1;
            $hosting =  get_field('стоимость_хостинга_usd_в_месяц', 2319);
            $energy_costs = $hosting * $qty;
            $energy = 1;
        }

        $costs['BTC'] = $energy * $energy_costs   * $days / $coursers['data']['base']["$currency / USD"] ;
        $costs['USD'] = $energy * $energy_costs   * $days;
        $costs['RUR'] = $energy * $energy_costs   * $coursers['data']['USD / RUR'] * $days;
        $costs['UAH'] = $energy * $energy_costs   * $coursers['data']['USD / UAH'] * $days;


        ob_start();
        //print_r($network);
        ?>

        <div class="income-table__inner">
            <div class="income-days-title "><?php _e('Расчет', 'al') ?></div>
        </div>

        <div class="income-table__title">
            <div class="income-table__title--item">
                <div class="income-icon income-icon-<?php echo $request->post('currency') == 'BTC' || $request->post('currency') == 'BCH' ? 1 : ''  ?>"><?php echo $request->post('currency') ?></div>
                <div class="income-icon income-icon-2">USD</div>
                <div class="income-icon income-icon-3">RUB</div>
                <div class="income-icon income-icon-4">UAH &#8372;</div>
            </div>
        </div>

        <div class="income-table__inner">
            <div class="income-table__inner-title"><?php _e('Доход', 'al') ?></div>
            <div class="income-table__item">
                <div class="income-icon income-icon-1">BTC</div>
                <div class="income-number"><?php echo number_format($P,6) ?></div>
                <div class="income-icon income-icon-2">USD</div>
                <div class="income-number"><?php echo  number_format($P*$coursers['data']['base']["$currency / USD"] , 2) ?></div>
                <div class="income-icon income-icon-3">RUB</div>
                <div class="income-number"><?php echo number_format($P*$coursers['data']['base']["$currency / RUR"], 2) ?></div>
                <div class="income-icon income-icon-4">UAH</div>
                <div class="income-number"><?php echo number_format($P*$coursers['data']['base']["$currency / UAH"] , 2)?></div>
            </div>
        </div>

        <div class="income-table__inner">
            <div class="income-table__inner-title"><?php _e('Затраты', 'al') ?></div>
            <div class="income-table__item">
                <div class="income-icon income-icon-1">BTC</div>
                <div class="income-number"><?php echo number_format($costs['BTC'], 6) ?></div>
                <div class="income-icon income-icon-2">USD</div>
                <div class="income-number"><?php echo  number_format($costs['USD'], 2) ?></div>
                <div class="income-icon income-icon-3">RUB</div>
                <div class="income-number"><?php echo  number_format($costs['RUR'], 2) ?></div>
                <div class="income-icon income-icon-4">UAH</div>
                <div class="income-number"><?php echo  number_format($costs['UAH'], 2) ?></div>
            </div>
        </div>

        <div class="income-table__inner">
            <div class="income-table__inner-title"><?php _e('Прибыль', 'al') ?></div>
            <div class="income-table__item">
                <div class="income-icon income-icon-1">BTC</div>
                <div class="income-number"><?php echo number_format($P - $costs['BTC'] ,6) ?></div>
                <div class="income-icon income-icon-2">USD</div>
                <div class="income-number"><?php echo  number_format($P*$coursers['data']['base']["$currency / USD"] - $costs['USD']  , 2) ?></div>
                <div class="income-icon income-icon-3">RUB</div>
                <div class="income-number"><?php echo number_format($P*$coursers['data']['base']["$currency / RUR"] - $costs['RUR'] , 2) ?></div>
                <div class="income-icon income-icon-4">UAH</div>
                <div class="income-number"><?php echo number_format($P*$coursers['data']['base']["$currency / UAH"]  - $costs['UAH'], 2) ?></div>
            </div>
        </div>


        <?php

        $result = ob_get_contents();
        ob_clean();

        $P = $labels = array();

        $date = new DateTime();
        foreach (range(0,20) as $key=>$day) {
            $D = $D * $expected_difficulty;
            $P[$key] =  number_format(($t*$R*$H)/($D*(2**32) ) - $costs['BTC']/$days, 7) * 1;


            //$date = $date->modify('+'.$network_status['expected_difficulty_date']);
            $date = $date->modify('+'.$network_status['expected_difficulty_date']);
            $labels[] = $date->format('d.m.y');

        }


        if (!$request->post('hash'))  {
            $result = 0 ;

        }

        echo json_encode(
            array(
                'result' => $result,
                'chart' =>  $P ,
                'chartLabels' =>  $labels
            ));
        die() ;
    }

    public function parse_btc_courses($source='coinbase') {
        //$CryptoTickerWidget = new CryptoTickerWidget();

        $url['coinbase'] = 'https://api.coinbase.com/v2/prices/BTC-USD/spot';
        $url['blockchain'] = 'https://blockchain.info/ru/ticker';
        $url['bitstamp'] = 'https://www.bitstamp.net/api/ticker';
        $coinbase_json = $this->get_data($url[$source]);
        $coinbase_decoded = json_decode($coinbase_json, true);

        if ($source == 'coinbase')
            return $coinbase_decoded['data']['amount'];
        if ($source == 'blockchain')
            return $coinbase_decoded['USD']['last'];
        if ($source == 'bitstamp')
            return $coinbase_decoded['last'];
    }

    public function parse_btc_course_calculated() {

        $usdpercent = Setting::where('title', 'usdpercent')->first()['value'];
        //$minmax = Setting::where('title', 'minmax')->first()->value;
        $valuechange = Setting::where('title', 'valuechange')->first()['value'];

        //$CryptoTickerWidget = new CryptoTickerWidget();

        $url['coinbase'] = 'https://api.coinbase.com/v2/prices/BTC-USD/spot';
        $url['blockchain'] = 'https://blockchain.info/ru/ticker';
        $url['bitstamp'] = 'https://www.bitstamp.net/api/ticker';

        $result_parsing['coinbase'] = json_decode($this->get_data($url['coinbase']), true);
        $result_parsing['blockchain'] = json_decode($this->get_data($url['blockchain']), true);
        //$result_parsing['bitstamp'] = json_decode($CryptoTickerWidget->get_data($url['bitstamp']), true);

        $result[] = $result_parsing['coinbase']['data']['amount'];
        $result[] = $result_parsing['blockchain']['USD']['last'];
        //$result[] = $result_parsing['bitstamp']['last'];


        $calc['min'] = min($result);
        $calc['max'] = max($result);
        $calc = ($calc['min'] + $calc['max']) / 2;
        //$calc = $calc[$minmax];

        if ($valuechange != 0)
            if ($usdpercent === 'usd') {
                $calc = $calc + $valuechange;
            } elseif ($usdpercent === 'percent') {
                $calc = $calc + $calc *  $valuechange/100;
            }


        return $calc;

    }


    /**
     * others crypt
     */


    public function parse_btc_courses_others($cur='') {
        //$CryptoTickerWidget = new CryptoTickerWidget();


        $url['BCH']  = 'https://api.coinmarketcap.com/v1/ticker/bitcoin-cash/';
        $url['LTC']  = 'https://api.coinmarketcap.com/v1/ticker/litecoin/';
        $url['DASH']  = 'https://api.coinmarketcap.com/v1/ticker/dash/';
        $url['ETH']  = 'https://api.coinmarketcap.com/v1/ticker/ethereum/';




        foreach ($url as $key => $val) {
            $coinbase_json = $this->get_data($val);
            $coinbase_decoded = json_decode($coinbase_json, true);
            $result[$key] = $coinbase_decoded[0]['price_usd'];
        }


        Setting::where('title', 'other_crypth')->update(['value' > json_encode($result)]);

        //update_option('others_crypth', $result);
        if ($cur)
            return $result[$cur];
        else
            return $result;
    }


    public function get_data($url)
    {
        //var_dump($url); die;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}