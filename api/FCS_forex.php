<?php

/*================================================
Author: FCSAPI
Description: fcsapi github data
Author URL: http://fcsapi.com/
Version: 1.0
================================================*/


/*================================================
  [ Table of contents ]
  ================================================

#1.	set_access_key()
#2.	set_output_type()
#3.	get_symbols_list()
#4.	get_profile()
#5.	get_converter()
#6.	get_latest_price()
#7.	get_last_candle()
#8.	get_base_prices()
#9.	get_history()
#10. get_pivot_points()
#11. get_moving_averages()
#12. get_technical_indicator()
#13. get_search_query()
#14. get_search_query()

  ================================================
  [ End table content ]
  ================================================*/

namespace FCS;
require_once(__DIR__.'/helper.php');

class FCS_forex {
	private $api_key 		= '';
	private $output 		= ''; // default is json
	private $output_type 	= 'array'; // default is json
	private $basic_url 	= "https://fcsapi.com/api-v3/forex";
	private $api_message = "API Key is empty, please set your API Key in config.php";

	function __construct(){
		if(defined("FCS_KEY")){
			$this->api_key = FCS_KEY;
		}
		if(empty(FCS_KEY)){
			$this->api_key = false; // API can access by IP, set in your profile
		}
	}

	/*================================================
		set your access key
		$forex->set_access_key('your_access_key');
	================================================*/
	public function set_access_key($key=''){
		$this->api_key = $key;
	}


	/*======================================
		==== 0 ====
		Fet your API response format.
		Default: JSON
		Valid Values: JSON, JSONP, object, XML, serialize and array
	======================================*/
	public function set_output_type($output_type=''){
		$output_type = strtolower($output_type);
		if($output_type == 'json' ||  $output_type == 'jsonp' ||  
			$output_type == 'object' ||   $output_type == 'xml' ||  
			$output_type == 'serialize' || $output_type == 'array' )
		{
			$this->output_type  = $output_type;
			if($output_type != "json" && $output_type != "array")
				$this->output = $output_type;
		}
		else{
			return 'Your output value is wrong.<br>Valid Values are: JSON, JSONP, object, XML, serialize and array';
		}
	}


	/*======================================
		==== 1 ====
		Return All symbols list of forex of ids and symbol
		Example: 1:EUR/USD, 2:USD/JPY, 3:AUD/USD etc

		=> $forex->get_symbols_list();
	======================================*/
	public function get_symbols_list(){
		if(!$this->check_api_key())
			return $this->api_message;

		$params 	= array();
		$params['type'] = 'forex';
		
		$link = $this->basic_url."/list";
		return $this->response($link,$params);
	}


	/*======================================
		==== 2 ====
		Get the details or profile of Currency, i.e: country, full name, id, website etc
		Accepted Parameters: id, symbol

		Format:
			1) $forex->get_profile(ids);
			2) $forex->get_profile(symbol);
		Example:
			1) $forex->get_profile('1,2,3');
			2) $forex->get_profile('CHF,USD,JPY,GBP');
			3) $forex->get_profile('CHF/USD,JPY/GBP');
	======================================*/
	public function get_profile($symbol){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol);
		$params 	= array();
		$params[$symbol_id] = $symbol; // ID/symbol

		$link = $this->basic_url."/profile";
		return $this->response($link, $params);
	}

	/*======================================
		==== 3 ====
		You can convert Base to quote currency.
		Accepted Parameters: amount id, symbol
			ie: Input : 200 EUR => 240.4451 USD reponse.

		Format:
			1) $forex->get_converter(amount, ID);
			2) $forex->get_converter(amount, 'From/To');
			3) $forex->get_converter(amount, 'From', 'To');
		Example:
			1) $forex->get_converter(200, 1);
			2) $forex->get_converter(200, 'EUR/USD');
			3) $forex->get_converter(200, 'EUR', 'USD');
	======================================*/
	public function get_converter($amount='200',$pair_one='',$pair_two=''){
		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($pair_one))
			return false;

		$params 	= array();

		if(!empty($pair_two)){
			$params['pair1'] = $pair_one;
			$params['pair2'] = $pair_two;
		}
		else{
			$symbol_id = $this->check_symbol_id($pair_one);
			$params[$symbol_id] = $pair_one;
		}
		$params['amount'] = $amount;

		$link = $this->basic_url."/converter";
		return $this->response($link,$params);
	}


	/*======================================
		==== 4 ====
		Get Forex Latest Price By id or symbol

		Format:
			1) $forex->get_latest_price(ids);
			2) $forex->get_latest_price(symbols);
		Example:
			1) $forex->get_latest_price('1,2');
			2) $forex->get_latest_price('EUR/USD,USD/JPY');
	======================================*/
	public function get_latest_price($symbol){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol);
		$params 	= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol

		$link = $this->basic_url."/latest";
		return $this->response($link,$params);
	}

	/*======================================
		Get all quote prices

			1) $forex->get_base_prices("USD");
			2) $forex->get_base_prices("JPY");
			2) $forex->get_base_prices('JPY','crypto');
	======================================*/
	public function get_base_prices($symbol,$type="forex",$time=false){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;
		$type = empty($type) ? "forex" : $type;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$params 	= array();
		$params['symbol'] 	= $symbol;
		$params['type'] 		= $type;
		if($time)
			$params['time'] 		= 1;

		$link = $this->basic_url."/base_latest";
		return $this->response($link , $params);
	}


	/*======================================
		Get Candle price by time period
		Accepted Parameters: id/symbol, period

		Default period : 1h
		Period Valid Values: 5m, 15m, 30m, 1h, 4h, 5h, 1d, 1w, month
		Format:
			1) $forex->get_last_candle(ids,period);
			2) $forex->get_last_candle(symbols,period);
		Example:
			1) $forex->get_last_candle('1,2,3','1d');
			2) $forex->get_last_candle('EUR/USD,USD/JPY','1d');
	======================================*/
	public function get_last_candle($symbol,$period='1h'){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol);
		
		$params 	= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol
		$params['period'] 		= $period;

		$link = $this->basic_url."/candle";
		return $this->response($link,$params);
	}


	/*================================================
		Accepted Parameters: id/ symbol, from_date, to_date
		Get Spcific currency history data

		Period Valid Values: 5m, 15m, 30m, 1h,2h,4h,5h, 1d, 1w, month
		Format:
			1) $forex->get_history(ids, period);
			2) $forex->get_history(symbol, period);
			3) $forex->get_history(symbol, period, from_date, to_date);
		example:
			1) $forex->get_history('1', '1h');
			2) $forex->get_history('EUR/USD', '1h');
			3) $forex->get_history('EUR/USD', '1h', '2019-07-01', '2019-12-31');
	================================================*/
	public function get_history($data){
		$symbol 		= empty($data['id']) 			? "" 		: $data['id'];
		$symbol 		= is_array($symbol) 		? implode(",", $symbol) : $symbol;

		$period 		= empty($data['period']) 	? "1h" 	: $data['period'];
		$limit 			= empty($data['limit']) 		? "1" 	: $data['limit'];
		$from 			= empty($data['from']) 		? "" 		: $data['from'];
		$to 				= empty($data['to']) 			? "" 		: $data['to'];

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol); // id or symbol

		$params 	= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol
		$params['period'] 		= $period;
		$params['level'] 		= $limit;


		if(!empty($from) && !empty($to)){
			$params['from'] 		= $from;
			$params['to'] 		= $to;
		}

		$link = $this->basic_url."/history";
		return $this->response($link, $params);
	}

	/*================================================
		Get pivot points
		In financial markets, a pivot point is a support/resistance level is a helper that is used by traders as a possible indicator of market movement.


		Default period 1h
		Period Valid Values: 5m, 15m, 30m, 1h, 2h, 4h, 5h, 1d, 1w, month
		Format:
			1) $forex->get_pivot_points(ids,period);
			2) $forex->get_pivot_points(symbol,period);
		example:
			1) $forex->get_pivot_points('1', '1h');
			2) $forex->get_pivot_points('EUR/USD', '1h');
	================================================*/
	public function get_pivot_points($symbol,$period='1h'){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol);
		$params 		= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol
		$params['period'] 		= $period;

		$link 	= $this->basic_url."/pivot_points";
		return $this->response($link , $params);
	}


	/*================================================
		Get moving averages forex, Moving Average (MA) is a trend indicator. 

		Default period 1h
		Period Valid Values: 5m, 15m, 30m, 1h, 5h, 1d, 1w, month
		Format:
			1) $forex->get_moving_averages(ids,period);
			2) $forex->get_moving_averages(symbol,period);
		example:
			1) $forex->get_moving_averages('1', '1h');
			2) $forex->get_moving_averages('EUR/USD', '1h');
	================================================*/
	public function get_moving_averages($symbol,$period='1h'){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;

		$symbol_id = $this->check_symbol_id($symbol);
		$params 		= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol
		$params['period'] 		= $period;

		$link = $this->basic_url."/ma_avg";
		return $this->response($link, $params);
	}


	/*================================================
		Top Indicators signals

		Default period 1h
		Period Valid Values: 5m, 15m, 30m, 1h, 5h, 1d, 1w, month
		Format:
			1) $forex->get_technical_indicator(ids,period);
			2) $forex->get_technical_indicator(symbol,period);
		example:
			1) $forex->get_technical_indicator('1', '1h');
			2) $forex->get_technical_indicator('EUR/USD', '1h');
	================================================*/
	public function get_technical_indicator($symbol,$period='1h'){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;
		$symbol_id = $this->check_symbol_id($symbol);
		$params 		= array();
		$params[$symbol_id] 	= $symbol; // ID/symbol
		$params['period'] 		= $period;

		$link = $this->basic_url."/indicators";
		return $this->response($link, $params);
	}

	/*================================================
		Economy Calendar
		$forex->	get_economy_calendar('USD,JPY');
		$forex->	get_economy_calendar('USD','2021-02-01','2021-02-10');
	================================================*/
	public function get_economy_calendar($symbol,$from='',$to=''){
		$symbol = is_array($symbol) ? implode(",", $symbol) : $symbol;

		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($symbol))
			return false;
		$params 		= array();
		$params['symbol'] 	= $symbol;
		$params['from'] 		= $from;
		$params['to'] 			= $to;

		$link = $this->basic_url."/economy_cal";
		return $this->response($link,$params);
	}


	/*================================================
		Search API
		$forex->	get_search_query('BTC');
	================================================*/
	public function get_search_query($search,$strict=0){
		$search = urlencode($search);
		if(!$this->check_api_key())
			return $this->api_message;

		if(empty($search))
			return false;
		$params 		= array();
		$params['s'] 		= $search;
		$params['strict'] 	= $strict;

		$link = $this->basic_url."/search";
		return $this->response($link, $params);
	}



	/*================================================
		check api key
	================================================*/
	private function check_api_key(){
		$temp_key = strtolower($this->api_key);
		if($this->api_key === false)
			return true;

		if(empty($this->api_key) || $temp_key == 'api_key')
			return false;
		else
			return true;
	}


	/* where type id or symbol check */
	private function check_symbol_id($txt){
		$type = 'id';
		if(preg_replace("/[^A-Za-z?!]/",'',$txt))
			$type = 'symbol';

		return $type;
	}

	private function response($url, $params){
		if(!empty($this->api_key))
			$params['access_key'] = $this->api_key;
		if(!empty($this->output))
			$params['output'] = $this->output;
		
		$respone = fcs_curl($url, $params);
		if($this->output_type == "array"){
			$decode 	= json_decode($respone,true);
			if(!empty($decode))
				return $decode;
		}
		return $respone;
	}
}



?>
