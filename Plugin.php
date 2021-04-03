<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}


/**
 * 高德天气插件
 *
 * @package Wiather
 * @author Wibus
 * @version 3.1.0
 * @link https://blog.iucky.cn
 */
class Wiather_Plugin implements Typecho_Plugin_Interface{
	
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate(){
        
		Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
		return _t('插件已启用，请先进入插件设置界面保存一次设置~');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
		return _t('插件已禁用，感谢您的支持~！');
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
        
        
                // 插件信息与更新检测
        function check_update($version)
        {

            echo "<style>.info{text-align:center; margin:20px 0;} .info > *{margin:0 0 15px} .buttons a{background:#467b96; color:#fff; border-radius:4px; padding: 8px 10px; display:inline-block;}.buttons a+a{margin-left:10px}</style>";
            echo "<div id='tip'></div>";
            echo "<div class='info'>";
            echo "<h2>一款使用高德天气API的 Typecho插件 (" . $version . ")</h2>";

            echo "<h3>最新版本：<span style='padding: 2px 4px; background-image: linear-gradient(90deg, rgba(73, 200, 149, 1), rgba(38, 198, 218, 1)); background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; color: rgba(255, 255, 255, 1); border-width: 0.25em 0' id='ver'>获取中...</span>&nbsp;&nbsp;当前版本：<span id='now'>".$version. "</span></h3>";
            echo "<h3 style='color: rgba(255, 153, 0, 1)' id='description'></h3>";
            echo "<p>By: <a href='https://blog.iucky.cn'>Wibus</a></p>";
            echo "<p><span class='buttons'><a href='https://blog.iucky.cn/system/159.html'>插件说明</a></span>
            <span id='btn' class='buttons'><a id='description'>获取更新说明</a></span></p>";
            echo "</div>";

        }
        check_update("3.1.0");




		// 天气信息
        $form->addInput(WiatherForm::Weather());

         // 天气弹窗样式
		 $assets = new Typecho_Widget_Helper_Form_Element_Radio(
            'assets',
            array(
                '0' => _t('原生alret弹窗'),
				'1' => _t('handsome弹窗'),
				'2' => _t('Sweetalert2'),
            ),
            '0',
            _t('天气弹窗样式'),
            _t('非handsome主题请不要选择第二项，否则无法正常使用')
        );
		$form->addInput($assets);

		//设置handsome提醒的图标
		$handsomeico = new Typecho_Widget_Helper_Form_Element_Radio(
            'handsomeico',
            array(
                '' => _t('不显示'),
                'info' => _t('info'),
				'success' => _t('success'),
				'warning' => _t('warning'),
            ),
            'info',
            _t('handsome自带提醒的侧边图标'),
            _t('若无启用handsome自带提醒，则此项可直接无视')
        );
		$form->addInput($handsomeico);

		//设置Sweetalert2提醒的图标
		$sweetico = new Typecho_Widget_Helper_Form_Element_Radio(
            'sweetico',
            array(
				'' => _t('不显示'),
                'info' => _t('info'),
				'success' => _t('success'),
				'warning' => _t('warning'),
				'error' => _t('error'),
            ),
            'success',
            _t('sweet自带提醒的侧边图标'),
            _t('若无启用sweet提醒，则此项可直接无视')
        );
		$form->addInput($sweetico);

		echo "<script src='https://api.iucky.cn/plugins/update/wiather.js'></script>";
		
		
    }
    
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    

    /**
     * 为footer加css文件
     * @return void
     */
    public static function footer(){

        // 天气信息
		WiatherHead::Weather();
    }
    
    
}
/** 设置模块 */ 
class WiatherForm{

	// 天气信息
	static function Weather(){
		
		$result = new Typecho_Widget_Helper_Form_Element_Text('Weather', NULL, _t('9dea1edbf46b6e2d0c52c77858b4db3b'), _t('天气信息'), _t('填写申请了的高德API（默认的API也可以用）'));
		
		return $result;
	}
}
class WiatherHead{
	
	// 天气信息
	static function Weather(){
	    
	    // 获取配置信息
		$type = Typecho_Widget::widget('Widget_Options')->plugin('Wiather')->Weather;
		
		if(!empty($type)){
		    
		    // 定位 
		    $location = Wiather_ClientInfo::Location($type);
		    
		    echo '<style>
    		        div#Weather{
    		            height: 30px;
    		        }
    		        div#Weather>div {
                        display: inline-block;
                        font-size: 1.1em;
                        margin: 0 5px;
                    }
                    .w-ico>svg {
                        margin: -5px 0;
                    }
                    .w-weather {
                        background-color: #5dbfe7;
                        color: white;
                        width: 46px;
                        border-radius: 50px;
                    }
                    .w-info {
                        color: white;
                        background-color: #5dbfe7;
                        border-radius: 3px;
                    }
		        </style>';
		        
		    if($location -> status == 1){
		        
		        // 天气 
		        $weather = Wiather_ClientInfo::Weather($type);
		    
    		    // 获取地理位置并截取余两位
    		    $city = mb_substr($location->city,0,2);
    		    
    		    $temperature = $weather->temperature.'°';
    		    
    		    $weatherInfo = $weather->weather;
    		    
    		    $arrIco = [
    		        'sunny'       =>    ['晴'],
    		        
    		        'cloud'       =>    ['少云','晴间多云','多云'],
    		        
    		        'cloudy'      =>    ['阴'],
    		      
    		        'gale'        =>    ['有风','平静','微风','和风','清风','强风/劲风','疾风','大风','烈风','风暴','狂爆风','飓风','热带风暴'],
    		        
    		        'smog'        =>    ['霾','中度霾','重度霾','严重霾'],
    		      
    		        'rain'        =>    ['阵雨','小雨','中雨','大雨','毛毛雨/细雨','雨','小雨-中雨'],
    		        
    		        'thunderRain' =>    ['雷阵雨','雷阵雨并伴有冰雹'],

                    'rainStorm'   =>    ['暴雨','大暴雨','特大暴雨','强阵雨','强雷阵雨','极端降雨','大雨-暴雨','暴雨-大暴雨','大暴雨-特大暴雨'],
                    
                    'sleet'       =>    ['雨雪天气','雨夹雪','阵雨夹雪','冻雨'],
                    
                    'snow'        =>    ['雪','阵雪','小雪','中雪','大雪','暴雪','小雪-中雪','中雪-大雪','大雪-暴雪'],
                    
                    'dust'        =>    ['浮尘','扬沙','沙尘暴','强沙尘暴'],
                    
                    'tornado'     =>    ['龙卷风'],
                    
                    'fog'         =>    ['雾','浓雾','强浓雾','轻雾','大雾','特强浓雾'],
                    
                    'heat'        =>    ['热'],
                    
                    'cold'        =>    ['冷'],
                    
                    'unknown'     =>    ['未知'],
    		        ];
    		    
    		    if(in_array($weatherInfo,$arrIco['sunny'])){
    		        
    		        $ico = WiaIco::Weather("sunny");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['cloud'])){
    		        
    		        $ico = WiaIco::Weather("cloud");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['cloudy'])){
    		        
    		        $ico = WiaIco::Weather("cloudy");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['gale'])){
    		        
    		        $ico = WiaIco::Weather("gale");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['smog'])){
    		        
    		        $ico = WiaIco::Weather("smog");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['rain'])){
    		        
    		        $ico = WiaIco::Weather("rain");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['thunderRain'])){
    		        
    		        $ico = WiaIco::Weather("thunderRain");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['rainStorm'])){
    		        
    		        $ico = WiaIco::Weather("rainStorm");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['sleet'])){
    		        
    		        $ico = WiaIco::Weather("sleet");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['snow'])){
    		        
    		        $ico = WiaIco::Weather("snow");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['dust'])){
    		        
    		        $ico = WiaIco::Weather("dust");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['tornado'])){
    		        
    		        $ico = WiaIco::Weather("tornado");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['fog'])){
    		        
    		        $ico = WiaIco::Weather("fog");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['heat'])){
    		        
    		        $ico = WiaIco::Weather("heat");
    		        
    		    }elseif(in_array($weatherInfo,$arrIco['cold'])){
    		        
    		        $ico = WiaIco::Weather("cold");
    		        
    		    }else{
    		        
    		        $ico = WiaIco::Weather("unknown");
				}
				
				$options = Helper::options();
				$assets = $options->plugin('Wiather')->assets;
				$handsomeico = $options->plugin('Wiather')->handsomeico;
				$sweetico = $options->plugin('Wiather')->sweetico;
			   
				if ($assets == 0) { 
					//echo '<script type="text/javascript" src="/usr/plugins/Wiather/static/libs/jquery-3.5.1.min.js"></script>';
					echo '<script type="text/javascript">
						$(function(){
						
								$(".dropdown.wrapper").after("<div id=\"Weather\"><div class=\"w-city\">'. $city .'</div><div class=\"w-ico\">'. $ico .'</div><div class=\"w-temperature\">'. $temperature .'</div><div class=\"w-weather\">'. $weatherInfo .'</div></div>");
								
								if($(".app-aside-folded").length>0){
									 $("div#Weather").css("display","none");
								}
								
								$("div#Weather").click(function(){
										alert("\n您的IP是：'.Wiather_ClientInfo::GetUserIP().'\n\n您的操作系统是：'.Wiather_ClientInfo::GetOS().'\n\n您使用的浏览器是：'.Wiather_ClientInfo::GetUserBrowser().'\n\n您所在的位置是：'.$location->province.$location->city.'\n\n当前天气情况：'.$weatherInfo.$weather->winddirection.'风'.$temperature.'C");
								})
						  });
						</script>';
				} elseif($assets == 1) {
					echo '
							<script>
							$(function(){
						
								$(".dropdown.wrapper").after("<div id=\"Weather\"><div class=\"w-city\">'. $city .'</div><div class=\"w-ico\">'. $ico .'</div><div class=\"w-temperature\">'. $temperature .'</div><div class=\"w-weather\">'. $weatherInfo .'</div></div>");
								
								if($(".app-aside-folded").length>0){
									 $("div#Weather").css("display","none");
								}
								
							
							$("div#Weather").click(function(){
							$.message({
								message: "当前天气情况：'.$weatherInfo.$weather->winddirection.'风'.$temperature.'C<br />您的IP是：'.Wiather_ClientInfo::GetUserIP().'<br /> 您的操作系统是：'.Wiather_ClientInfo::GetOS().' <br /> 您使用的浏览器是：'.Wiather_ClientInfo::GetUserBrowser().' <br />",
								title: "'.$location->province.$location->city.' 天气情况",
								type: "'.$handsomeico.'",
								autoHide: !1,
								time: "5000"
							});})
						});
							</script>';
				}elseif ($assets == 2) {
					echo '
					<!-- 引入Sweetalert2 -->
					<link rel="stylesheet" href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" />
					<script type="text/javascript" src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>';

					echo '<script type="text/javascript">
						$(function(){
						
								$(".dropdown.wrapper").after("<div id=\"Weather\"><div class=\"w-city\">'. $city .'</div><div class=\"w-ico\">'. $ico .'</div><div class=\"w-temperature\">'. $temperature .'</div><div class=\"w-weather\">'. $weatherInfo .'</div></div>");
								
								if($(".app-aside-folded").length>0){
									 $("div#Weather").css("display","none");
								}
								
								$("div#Weather").click(function(){
										swal("'.$location->province.$location->city.' 天气情况","当前天气情况：'.$weatherInfo.$weather->winddirection.'风'.$temperature.'C\n\n您的IP是：'.Wiather_ClientInfo::GetUserIP().'\n 您的操作系统是：'.Wiather_ClientInfo::GetOS().' \n 您使用的浏览器是：'.Wiather_ClientInfo::GetUserBrowser().'\n", "'.$sweetico.'");
								})
						  });
						</script>';
				}
    		   
    			echo '<script type="text/javascript">
    			        console.log("\n %c Wiather天气插件 - by Wibus https://blog.iucky.cn","color:#fff; background: linear-gradient(to right , #7A88FF, #d27aff); padding:5px; border-radius: 10px;");
    			        </script>';
    				
		    }else{
		        
		        $info = '错误，天气信息！您的API配置有误，请确认API是否正确！';
		        
		        echo '<script type="text/javascript">
    		        $(function(){
    		                
                            $(".dropdown.wrapper.vertical-wrapper").after("<div id=\"Weather\"><div class=\"w-info\">'. $info .'</div></div>");
                      });
    				</script>';
		    }
		}
	}
	
	
	// END
}
/**
 * 获取客户端浏览器信息
 * @param  null
 * @author  wibus
 * @return string
 */
class Wiather_ClientInfo{
	// 返回系统信息
	public static function GetOS($user_agent=null) {
		$userAgent 	= strtolower($user_agent ? : $_SERVER['HTTP_USER_AGENT']);
		$os = "";
		$os_array = array(
		    		'/windows nt 10.0/i' 	=> 	'Windows 10',
					'/windows nt 6.3/i'     =>  'Windows 8.1',
					'/windows nt 6.2/i'     =>  'Windows 8',
					'/windows nt 6.1/i'     =>  'Windows 7',
					'/windows nt 6.0/i'     =>  'Windows Vista',
					'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
					'/windows nt 5.1/i'     =>  'Windows XP',
					'/windows xp/i'         =>  'Windows XP',
					'/windows nt 5.0/i'     =>  'Windows 2000',
					'/windows me/i'         =>  'Windows ME',
					'/win98/i'              =>  'Windows 98',
					'/win95/i'              =>  'Windows 95',
					'/win16/i'              =>  'Windows 3.11',
					'/macintosh|mac os x/i' =>  'Mac OS X',
					'/mac_powerpc/i'        =>  'Mac OS 9',
					'/linux/i'              =>  'Linux',
					'/ubuntu/i'             =>  'Ubuntu',
					'/iphone/i'             =>  'iPhone',
					'/ipod/i'               =>  'iPod',
					'/ipad/i'               =>  'iPad',
					'/android/i'            =>  'Android',
					'/blackberry/i'         =>  'BlackBerry',
					'/webos/i'              =>  'Mobile'
		     	);
		foreach ($os_array as $regex => $value) {
			if ( preg_match($regex, $userAgent) ) {
				$os = $value;
			}
		}
		return $os;
	}
	
	// 获取用户客户端浏览器信息
	public static function GetUserBrowser() {
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			return 'error!';
		}
		if ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') == false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE)) {
			return 'Internet Explorer 11.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0') != false) {
			return 'Internet Explorer 10.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0') != false) {
			return 'Internet Explorer 9.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') != false) {
			return 'Internet Explorer 8.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') != false) {
			return 'Internet Explorer 7.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') != false) {
			return 'Internet Explorer 6.0';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') != false) {
			return 'Edge';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], '360SE') != false) {
			return '360SE';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser') != false) {
			return 'QQ浏览器';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') != false) {
			return 'Firefox';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') != false) {
			return 'Chrome';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') != false) {
			return 'Safari';
		}
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') != false) {
			return 'Opera';
		}
		
		//微信浏览器
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessage') != false) {
			return 'MicroMessage';
		}
	}
	
	// 获取用户真实 IP
	public static function GetUserIP() {
		static $realip;
		if (isset($_SERVER)) {
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$realip = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$realip = $_SERVER["REMOTE_ADDR"];
			}
		} else {
			if (getenv("HTTP_X_FORWARDED_FOR")) {
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			} else if (getenv("HTTP_CLIENT_IP")) {
				$realip = getenv("HTTP_CLIENT_IP");
			} else {
				$realip = getenv("REMOTE_ADDR");
			}
		}
		return $realip;
	}
	
	//获得天气信息
	public static function Weather($key) {
		//$key = '你在高德申请的秘钥';
		$Weather = $Weather ? : STATIC::Location($key);
		//调用方法获得 Ip 定位信息;
		$city = $Weather->adcode;
		//获得adcode;
		$WeatherInfo = $WeatherInfo ? : STATIC::WeatherInfo($key, $city);
		//已经获取了天气信息;
		return $WeatherInfo;
	}
	
	//定位信息
	public static function Location($key) {
		$ip = $ip ? : STATIC::GetUserIP($realip);
		$ch = curl_init("http://restapi.amap.com/v3/ip?key=".$key."&ip=" . $ip);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 请求的数据不直接发送到浏览器
		$result = curl_exec($ch);
		//执行请求,直接发送到浏览器
		// $city = json_decode($result)->adcode;
		$info = json_decode($result);
		return $info;
	}
	
	//天气信息
	public static function WeatherInfo($key, $city) {
		$ch = curl_init("http://restapi.amap.com/v3/weather/weatherInfo?key=" . $key ."&city=" . $city ."&extensions=base");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		$info = json_decode($result)->lives[0];
		return $info;
	}
}
/*
 * 图标文件
 * JQ方式插入前端
 */

class WiaIco{
    
    // 天气图标
    public static function Weather($name){
        
        $arr = [
            // 晴
            'sunny' => '<svg t=\"1592308687474\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"3861\" width=\"22px\" height=\"22px\"><path d=\"M278.09 385.6c19.52-122.18 126.5-216.43 253.5-216.43 141.55 0 256.71 115.16 256.71 256.71q0 4.62-0.17 9.24a260.36 260.36 0 0 1 48.77 10.69 263.62 263.62 0 0 1 29.91 11.69 334.1 334.1 0 0 0 1.49-31.62 336.7 336.7 0 0 0-672.54-24.12 281 281 0 0 1 30-8.87 286.74 286.74 0 0 1 52.33-7.29z\" fill=\"#ffb948\" p-id=\"3862\" data-spm-anchor-id=\"a313x.7781069.0.i2\" class=\"selected\"></path><path d=\"M138.83 727.94m-15 0a15 15 0 1 0 30 0 15 15 0 1 0-30 0Z\" fill=\"#48BCFF\" p-id=\"3863\"></path><path d=\"M967.15 539.16a260.85 260.85 0 0 0-100.35-82 263.62 263.62 0 0 0-29.91-11.69 260.36 260.36 0 0 0-48.77-10.69 264.1 264.1 0 0 0-82.08 3 40 40 0 1 0 15.33 78.52 183.66 183.66 0 0 1 35.11-3.38 182 182 0 0 1 87.39 341.63h-645.7A206.7 206.7 0 0 1 85.55 670.92c0-96.06 65.2-178.5 158.54-200.46a207.19 207.19 0 0 1 47.39-5.46c103 0 190.75 76.86 204.16 178.77A40 40 0 0 0 575 633.34 286 286 0 0 0 291.48 385q-6.7 0-13.39 0.32a286.74 286.74 0 0 0-52.31 7.28 281 281 0 0 0-30 8.87A285.62 285.62 0 0 0 5.55 670.92a285.94 285.94 0 0 0 166 259.61l0.53 0.25 0.27 0.12 0.29 0.13 0.14 0.06a39.67 39.67 0 0 0 6.52 2.25l0.62 0.14 1.22 0.27c0.48 0.1 1 0.18 1.45 0.26l0.4 0.07a39.7 39.7 0 0 0 6 0.47h664.62c1.31 0 2.62-0.08 3.93-0.21h0.15c0.48 0 1-0.11 1.43-0.18l0.74-0.1 1.09-0.2 1.1-0.22 0.83-0.19 1.41-0.36 0.57-0.16q0.9-0.27 1.78-0.57l0.22-0.07a39.76 39.76 0 0 0 4.57-1.94 262 262 0 0 0 147-235.44 259.9 259.9 0 0 0-51.28-155.75z\" fill=\"#48BCFF\" p-id=\"3864\"></path><path d=\"M295.42 489.79c-100 0-181.27 81.32-181.27 181.27a15 15 0 0 0 30 0c0-83.41 67.86-151.27 151.27-151.27a15 15 0 0 0 0-30z\" fill=\"#48BCFF\" p-id=\"3865\"></path></svg>',
            
            // 晴
            'sunny01' => '<svg t=\"1592309417224\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"6930\" width=\"26px\" height=\"26px\"><path d=\"M486.9 514.8c-11.2 0-22.2 1.5-32.5 4.2 41.2 10.8 74.3 41.6 88.3 81.4 16.3-10.9 35.2-18.2 55.6-20.7-22-38.8-63.6-64.9-111.4-64.9zM326.9 451.8c-11.4 0-22.4 2-32.5 5.7 32.4 11.7 56.8 40.3 62.3 75.1 4.9-2.9 10-5.5 15.3-7.7 13.9-5.9 29.2-9.4 45.1-10-13.5-36.8-48.8-63.1-90.2-63.1zM740.6 594.6c-1.1-0.6-2.1-1.1-3.2-1.7 1.1 0.6 2.2 1.1 3.2 1.7 39.9-35.2 65.1-86.6 65.1-144 0-105.9-85.9-191.8-191.8-191.8-11.1 0-22 1-32.5 2.8 90.5 15.4 159.3 94.2 159.3 189.1 0 49.6-18.8 94.7-49.7 128.7-4-0.4-8.1-0.6-12.2-0.6-11.2 0-22.1 1.5-32.5 4.2 10.3 2.7 20.1 6.6 29.2 11.7 39.5 21.8 66.3 63.8 66.3 112.2 0 70.7-57.3 128-128 128h65c70.7 0 128-57.3 128-128 0.1-48.4-26.7-90.5-66.2-112.3z m-36.7-13.4c0.7 0.1 1.5 0.3 2.2 0.5-0.7-0.2-1.4-0.3-2.2-0.5z m-11.1-1.7c0.7 0.1 1.3 0.1 2 0.2-0.7 0-1.3-0.1-2-0.2z m3.8 0.5c0.6 0.1 1.2 0.2 1.9 0.3-0.7-0.1-1.3-0.2-1.9-0.3z m3.7 0.6c0.6 0.1 1.3 0.2 1.9 0.3-0.7-0.1-1.3-0.2-1.9-0.3z m36.1 11.8c-0.7-0.4-1.5-0.7-2.2-1.1 0.7 0.4 1.5 0.7 2.2 1.1z m-3.4-1.7c-0.7-0.3-1.4-0.7-2.1-1 0.6 0.4 1.3 0.7 2.1 1z m-3.4-1.5c-0.8-0.3-1.6-0.7-2.4-1 0.8 0.3 1.6 0.7 2.4 1z m-3.4-1.4c-1.1-0.4-2.2-0.9-3.3-1.3 1.1 0.5 2.2 0.9 3.3 1.3z m-4.5-1.7c-0.8-0.3-1.5-0.5-2.3-0.8 0.8 0.3 1.6 0.6 2.3 0.8z m-3.7-1.2c-0.7-0.2-1.3-0.4-2-0.6 0.7 0.1 1.3 0.4 2 0.6z m-3.6-1.1c-0.7-0.2-1.3-0.4-2-0.6 0.7 0.2 1.4 0.4 2 0.6z m-3.5-1c-0.8-0.2-1.7-0.4-2.5-0.6 0.8 0.2 1.6 0.4 2.5 0.6z\" fill=\"#2EA7E0\" p-id=\"6931\"></path><path d=\"M675.6 594.6c-9.1-5-18.9-9-29.2-11.7 10.4-2.7 21.3-4.2 32.5-4.2 4.1 0 8.2 0.2 12.2 0.6 30.8-34 49.7-79.2 49.7-128.7 0-89.7-61.6-165-144.8-186-83.2 21-144.8 96.3-144.8 186 0 22.7 4 44.5 11.2 64.7-3.8-0.3-7.6-0.5-11.4-0.5-5 0-9.9 0.3-14.7 0.8 6.2 0.7 12.2 1.8 18.1 3.3 10.4-2.7 21.3-4.2 32.5-4.2 47.8 0 89.4 26.2 111.4 65-20.4 2.5-39.3 9.7-55.6 20.7-14-39.8-47.1-70.6-88.3-81.4-6 1.6-11.8 3.5-17.3 5.9-5.3 2.2-10.4 4.8-15.3 7.7-1-6.1-2.5-12.1-4.6-17.7-16 0.6-31.2 4.1-45.1 10-5.3 2.2-10.4 4.8-15.3 7.7-4.7-29.3-22.6-54.2-47.4-68.3-29.4 16.4-49.3 47.8-49.3 83.9 0 15.6 3.7 30.3 10.3 43.4-43.6 20.3-73.8 64.6-73.8 115.8 0 67.1 51.8 122.2 117.6 127.3H614c70.7 0 128-57.3 128-128-0.2-48.2-26.9-90.3-66.4-112.1z\" fill=\"#F7F8F8\" p-id=\"6932\"></path><path d=\"M562.2 437.8m-16 0a16 16 0 1 0 32 0 16 16 0 1 0-32 0Z\" fill=\"#3E3A39\" p-id=\"6933\"></path><path d=\"M530.2 464.4m-16 0a16 16 0 1 0 32 0 16 16 0 1 0-32 0Z\" fill=\"#EF6676\" p-id=\"6934\"></path><path d=\"M701.9 464.4m-16 0a16 16 0 1 0 32 0 16 16 0 1 0-32 0Z\" fill=\"#EF6676\" p-id=\"6935\"></path><path d=\"M669.9 437.8m-16 0a16 16 0 1 0 32 0 16 16 0 1 0-32 0Z\" fill=\"#3E3A39\" p-id=\"6936\"></path><path d=\"M641.3 448.4h-50.6c-3.9 0-6.7 3.7-5.8 7.5 1.8 7.5 6.3 14 12.3 18.4 5.3 3.8 11.8 6.1 18.8 6.1s13.5-2.3 18.8-6.1c6.1-4.4 10.5-10.9 12.3-18.4 1-3.8-1.9-7.5-5.8-7.5z\" fill=\"#3E3A39\" p-id=\"6937\"></path><path d=\"M616 464.4c-7 0-13.3 3.2-17.5 8.2-0.8 0.9-0.5 2.3 0.5 3 4.9 3.1 10.7 4.9 17 4.9s12.1-1.8 17-4.9c1-0.6 1.3-2 0.5-3-4.2-5-10.4-8.2-17.5-8.2z\" fill=\"#E4847F\" p-id=\"6938\"></path><path d=\"M864 285.2h-13.6v-13.6c0-3.4-2.8-6.2-6.2-6.2s-6.2 2.8-6.2 6.2v13.6h-13.6c-3.4 0-6.2 2.8-6.2 6.2s2.8 6.2 6.2 6.2H838v13.6c0 3.4 2.8 6.2 6.2 6.2s6.2-2.8 6.2-6.2v-13.6H864c3.4 0 6.2-2.8 6.2-6.2s-2.8-6.2-6.2-6.2zM154.7 303.8c2.4 2.4 6.3 2.4 8.8 0l9.6-9.6 9.6 9.6c2.4 2.4 6.3 2.4 8.8 0 2.4-2.4 2.4-6.3 0-8.8l-9.6-9.6 9.6-9.6c2.4-2.4 2.4-6.3 0-8.8-2.4-2.4-6.3-2.4-8.8 0l-9.6 9.6-9.6-9.6c-2.4-2.4-6.3-2.4-8.8 0-2.4 2.4-2.4 6.3 0 8.8l9.6 9.6-9.6 9.6c-2.5 2.5-2.5 6.4 0 8.8zM765 849.2c-11.6 0-21 9.4-21 21s9.4 21 21 21 21-9.4 21-21-9.4-21-21-21z m0 32c-6.1 0-11-4.9-11-11s4.9-11 11-11 11 4.9 11 11-4.9 11-11 11zM168 451.4c0-11.6-9.4-21-21-21s-21 9.4-21 21 9.4 21 21 21 21-9.4 21-21z m-21 11c-6.1 0-11-4.9-11-11s4.9-11 11-11 11 4.9 11 11-4.9 11-11 11zM315.2 330.7c2.3 0 4.2-1.9 4.2-4.2v-13.4c0-2.3-1.9-4.2-4.2-4.2s-4.2 1.9-4.2 4.2v13.4c0 2.4 1.9 4.2 4.2 4.2zM315.2 370.8c2.3 0 4.2-1.9 4.2-4.2v-13.4c0-2.3-1.9-4.2-4.2-4.2s-4.2 1.9-4.2 4.2v13.4c0 2.3 1.9 4.2 4.2 4.2zM331.1 361.8c0.8 0.8 1.9 1.2 3 1.2s2.1-0.4 3-1.2c1.6-1.6 1.6-4.3 0-5.9l-9.4-9.4c-1.6-1.6-4.3-1.6-5.9 0-1.6 1.6-1.6 4.3 0 5.9l9.3 9.4zM302.8 333.4c0.8 0.8 1.9 1.2 3 1.2s2.1-0.4 3-1.2c1.6-1.6 1.6-4.3 0-5.9l-9.4-9.4c-1.6-1.6-4.3-1.6-5.9 0-1.6 1.6-1.6 4.3 0 5.9l9.3 9.4zM302.8 346.4l-9.4 9.4c-1.6 1.6-1.6 4.3 0 5.9 0.8 0.8 1.9 1.2 3 1.2s2.1-0.4 3-1.2l9.4-9.4c1.6-1.6 1.6-4.3 0-5.9-1.7-1.6-4.4-1.6-6 0zM324.6 334.6c1.1 0 2.1-0.4 3-1.2l9.4-9.4c1.6-1.6 1.6-4.3 0-5.9-1.6-1.6-4.3-1.6-5.9 0l-9.4 9.4c-1.6 1.6-1.6 4.3 0 5.9 0.8 0.8 1.9 1.2 2.9 1.2zM324.4 339.9c0 2.3 1.9 4.2 4.2 4.2H342c2.3 0 4.2-1.9 4.2-4.2 0-2.3-1.9-4.2-4.2-4.2h-13.4c-2.3 0-4.2 1.9-4.2 4.2zM288.5 344.1h13.4c2.3 0 4.2-1.9 4.2-4.2 0-2.3-1.9-4.2-4.2-4.2h-13.4c-2.3 0-4.2 1.9-4.2 4.2 0 2.3 1.9 4.2 4.2 4.2z\" fill=\"#036EB8\" p-id=\"6939\"></path><path d=\"M432.9 232.8m-8.9 0a8.9 8.9 0 1 0 17.8 0 8.9 8.9 0 1 0-17.8 0Z\" fill=\"#036EB8\" p-id=\"6940\"></path><path d=\"M827 579.9m-8.9 0a8.9 8.9 0 1 0 17.8 0 8.9 8.9 0 1 0-17.8 0Z\" fill=\"#036EB8\" p-id=\"6941\"></path><path d=\"M185.2 626.2c-3.3 4.5-2.3 10.7 2.1 14 1.8 1.3 3.9 1.9 5.9 1.9 3.1 0 6.1-1.4 8.1-4.1 11.7-16 27.4-28.8 45.3-37.2 2.5-1.1 4.3-3.2 5.2-5.8s0.7-5.4-0.5-7.8c-6.1-12.1-9.3-25.2-9.3-38.8 0-38 25.4-71.9 61.8-82.5 5.3-1.6 8.3-7.1 6.8-12.4-1.6-5.3-7.1-8.3-12.4-6.8-21.5 6.3-40.9 19.7-54.6 37.6-14.1 18.6-21.6 40.7-21.6 64.1 0 13.5 2.5 26.5 7.3 38.8-17.2 9.7-32.4 23-44.1 39zM318 452.5c0 5.5 4.5 10 10 10 42.5 0 78.3 30.4 84.9 72.4 0.5 3.3 2.6 6.1 5.6 7.5 3 1.4 6.5 1.2 9.4-0.5 4.5-2.7 9.2-5.1 14.1-7.1 14.6-6.2 30-9.3 46-9.3 46.9 0 89.2 27.9 107.9 70.3 1.3 2.8 2.4 5.7 3.4 8.7 1 2.9 3.3 5.2 6.3 6.2 0.3 0.1 0.7 0.2 1 0.3 0.1 0 0.2 0 0.3 0.1 0.3 0.1 0.6 0.1 0.9 0.1H609c0.4 0 0.9 0 1.3-0.1 0.1 0 0.2 0 0.3-0.1 0.3-0.1 0.7-0.1 1-0.2 0.1 0 0.3-0.1 0.4-0.1l0.9-0.3c0.1-0.1 0.3-0.1 0.4-0.2 0.4-0.2 0.8-0.4 1.2-0.7 19.4-13 42.1-19.9 65.6-19.9 19.9 0 39.6 5.1 56.9 14.6 15.1 8.3 28.4 20.1 38.6 34 2 2.7 5 4.1 8.1 4.1 2 0 4.1-0.6 5.9-1.9 4.5-3.3 5.4-9.5 2.2-14-9.2-12.7-20.7-23.8-33.6-32.7C795.7 556 817 504.8 817 451.3c0-111.3-90.5-201.8-201.8-201.8-54.9 0-106.3 21.7-144.6 61-3.9 4-3.8 10.3 0.2 14.1 4 3.9 10.3 3.8 14.1-0.2 34.5-35.5 80.8-55 130.3-55C715.5 269.4 797 351 797 451.2c0 50-20.6 97.7-56.7 131.9-0.2-0.1-0.5-0.2-0.7-0.3-0.5-0.2-1-0.5-1.5-0.7-0.8-0.3-1.5-0.7-2.3-1-0.5-0.2-1-0.4-1.5-0.7-0.8-0.3-1.5-0.6-2.3-1l-1.5-0.6-2.4-0.9-1.5-0.6c-0.8-0.3-1.7-0.6-2.5-0.9-0.5-0.2-0.9-0.3-1.4-0.5-1-0.3-2.1-0.7-3.1-1-0.3-0.1-0.5-0.2-0.8-0.2-1.3-0.4-2.7-0.7-4-1.1-0.3-0.1-0.7-0.2-1-0.2-1-0.2-2-0.5-3-0.7l-1.5-0.3c-0.9-0.2-1.7-0.4-2.6-0.5-0.5-0.1-1.1-0.2-1.6-0.3-0.8-0.1-1.6-0.3-2.4-0.4-0.6-0.1-1.1-0.2-1.7-0.3l-2.4-0.3c-0.6-0.1-1.1-0.2-1.7-0.2l-2.4-0.3c-0.6-0.1-1.1-0.1-1.7-0.2-0.8-0.1-1.7-0.1-2.5-0.2-0.5 0-1.1-0.1-1.6-0.1-0.9-0.1-1.8-0.1-2.8-0.1-0.5 0-0.9 0-1.4-0.1-1.4 0-2.8-0.1-4.1-0.1-23.4 0-46.1 5.8-66.3 16.9-22.2-48.9-71.3-80.9-125.7-80.9h-4.2c-0.7 0-1.5 0-2.2 0.1-0.4 0-0.8 0-1.1 0.1-0.7 0-1.4 0.1-2.1 0.1-0.4 0-0.7 0-1.1 0.1-0.8 0.1-1.7 0.1-2.5 0.2-0.2 0-0.4 0-0.7 0.1-1.1 0.1-2.1 0.2-3.1 0.4-0.3 0-0.6 0.1-0.8 0.1-0.8 0.1-1.5 0.2-2.3 0.3-0.4 0.1-0.7 0.1-1.1 0.2l-2.1 0.3c-0.4 0.1-0.7 0.1-1.1 0.2-0.7 0.1-1.5 0.3-2.2 0.4-0.3 0.1-0.6 0.1-0.9 0.2-1 0.2-2.1 0.4-3.1 0.7-0.2 0-0.3 0.1-0.5 0.1-0.9 0.2-1.7 0.4-2.6 0.6-0.3 0.1-0.7 0.2-1 0.3l-2.1 0.6c-0.4 0.1-0.7 0.2-1.1 0.3l-2.1 0.6c-0.3 0.1-0.6 0.2-1 0.3-0.9 0.3-1.9 0.6-2.8 0.9-0.1 0-0.2 0.1-0.2 0.1h-0.1c-7.1-19.6-10.6-40.3-10.6-61.4 0-36.4 10.7-71.6 31-101.7a9.99 9.99 0 0 0-2.7-13.9 9.99 9.99 0 0 0-13.9 2.7c-22.6 33.4-34.5 72.4-34.5 112.8 0 13.5 1.3 26.8 3.9 39.8-5.5-8.5-12.2-16.3-20-23-19.2-16.5-43.8-25.6-69.1-25.6-5.9 0.2-10.4 4.6-10.4 10.2zM793.2 646.8c-5.1 2.2-7.4 8.1-5.1 13.2 6.6 15 9.9 30.9 9.9 47.4 0 65.1-52.9 118-118 118H296.3c-64.9 0-117.8-52.8-117.8-117.8 0-12.7 2-25.3 6-37.3 1.7-5.2-1.1-10.9-6.3-12.6-5.2-1.7-10.9 1.1-12.6 6.3-4.7 14-7 28.7-7 43.6 0 76 61.8 137.8 137.8 137.8H680c76.1 0 138-61.9 138-138 0-19.3-3.9-38-11.6-55.5-2.2-5-8.1-7.3-13.2-5.1z\" fill=\"#036EB8\" p-id=\"6942\"></path></svg>',
            
            // 多云 没小太阳
            'cloud' => '<svg t=\"1592309733851\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"11226\" width=\"24px\" height=\"24px\"><path d=\"M901.659 658.236c-17.813 99.541-101.732 175.717-204.347 180.842v0.496h-361.3v-0.558C189.961 833.179 73.248 712.33 73.248 563.688c0-131.794 91.754-241.832 214.343-269.19 46.836-64.183 122.066-106.014 207.174-106.014 83.92 0 158.227 40.681 205.192 103.344 13.342-2.559 27.068-4.024 41.149-4.024 120.934 0 218.97 98.814 218.97 220.707 0 57.865-22.27 110.363-58.417 149.725zM494.765 240.212c-50.29 0-96.326 18.214-132.089 48.391 93.999 5.395 175.095 58.616 220.166 135.778 30.83-16.707 66.066-26.224 103.522-26.224 2.801 0 5.522 0.318 8.298 0.423-21.964-90.845-103.007-158.368-199.897-158.368z m191.599 213.122c-90.7 0-218.682 64.581-218.682 156.001 0-64.284 27.146-112.437 70.659-152.774-37.393-67.716-109.04-113.58-191.381-113.58-120.934 0-218.97 98.814-218.97 220.708 0 118.168 92.209 214.364 208.022 220.151v0.557h361.301v-0.557c85.571-5.704 153.279-77.28 153.279-164.975-0.001-91.42-73.528-165.531-164.228-165.531z m54.742-110.354c-3.542 0-7.229 0.311-10.875 0.534 8.754 20.046 14.78 41.504 18.303 63.802 72.096 21.497 128.518 79.362 148.645 152.541 5.237-16.176 8.154-33.408 8.154-51.347 0-91.419-73.527-165.53-164.227-165.53zM467.682 609.335\" fill=\"#7ECEF4\" p-id=\"11227\"></path></svg>',
            
            // 多云 有小太阳
            'muchCloud' => '<svg t=\"1592374399716\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"2786\" width=\"24px\" height=\"24px\"><path d=\"M830.81 561.6l-8.66-10.11c-21.48-25.08-52.7-39.46-85.65-39.46-10.24 0-20.3 1.36-29.9 4.05l-15.29 4.28-1.22-15.83c-4.42-57.32-30.87-109.78-74.46-147.71l-9.41-8.18 7.76-9.76c29.9-37.63 74.56-59.22 122.52-59.22 86.26 0 156.44 70.18 156.44 156.44 0 44.45-19.03 86.96-52.2 116.63l-9.93 8.87z m-94.31-75.65c35.91 0 70.14 13.87 95.87 38.45 22.06-23.96 34.5-55.51 34.5-88.31 0-71.89-58.48-130.37-130.37-130.37-35.91 0-69.6 14.52-94.14 40.23 39.48 38.13 64.67 87.64 72.14 141.75a137.37 137.37 0 0 1 22-1.75zM598.49 226.88l4.39-2.83c6.96-4.49 16.24-2.5 20.74 4.46l24.67 38.2c4.49 6.96 2.5 16.24-4.46 20.74l-4.39 2.83c-6.96 4.49-16.24 2.5-20.74-4.46l-24.67-38.2c-4.5-6.96-2.5-16.24 4.46-20.74zM768.35 188.25l5.19 0.56c8.24 0.88 14.2 8.27 13.32 16.51l-4.84 45.22c-0.88 8.24-8.27 14.2-16.51 13.32l-5.19-0.56c-8.24-0.88-14.2-8.27-13.32-16.51l4.84-45.22c0.88-8.24 8.27-14.2 16.51-13.32zM925.87 264.57l3.69 3.69c5.86 5.86 5.86 15.36 0 21.21l-32.16 32.16c-5.86 5.86-15.36 5.86-21.21 0l-3.69-3.69c-5.86-5.86-5.86-15.36 0-21.21l32.16-32.16c5.85-5.86 15.35-5.86 21.21 0zM985.3 415.23l0.36 5.21c0.57 8.26-5.68 15.42-13.94 15.99l-45.37 3.1c-8.26 0.57-15.42-5.68-15.99-13.94l-0.36-5.21c-0.57-8.26 5.68-15.42 13.94-15.99l45.37-3.1c8.27-0.57 15.43 5.67 15.99 13.94zM952.01 577.22l-3.13 4.18c-4.97 6.63-14.37 7.97-21 3l-36.38-27.3c-6.63-4.97-7.97-14.37-3-21l3.13-4.18c4.97-6.63 14.37-7.97 21-3l36.38 27.29c6.63 4.98 7.97 14.38 3 21.01z\" fill=\"#F9C626\" p-id=\"2787\"></path><path d=\"M736.53 486.37c-7.6 0-15.05 0.6-22.42 1.86-16.46-119.27-119.94-210.68-241.75-210.68-49.84 0-97.74 14.97-138.79 43.36l-0.67 0.45c-0.22 0.15-0.37 0.22-0.6 0.37-20.49 14.3-38.59 31.66-53.79 51.48-0.15 0.15-0.22 0.22-0.3 0.37-0.07 0.15-0.15 0.22-0.22 0.37-5.59 7.3-10.73 14.97-15.42 22.72-11.99-2.23-24.36-3.35-36.8-3.35C116.18 393.32 27 482.5 27 592.09c0 10.95 0.89 21.75 2.61 32.18 15.72 96.55 98.19 166.58 196.16 166.58h483.05c71 0 133.35-45.15 155.33-112.49l1.04-2.09v-0.6c6.41-16.17 9.68-33.23 9.68-50.88 0.01-76.29-62.05-138.42-138.34-138.42z m104.45 182l-0.97 2.31v0.6c-18.77 56.55-71.37 94.39-131.19 94.39H225.76c-85.6 0-157.64-61.16-171.27-145.5-1.56-9.09-2.31-18.55-2.31-28.09 0-95.73 77.85-173.58 173.58-173.58 9.31 0 18.62 0.74 27.71 2.23-16.99 46.56-16.02 98.19 4.02 145.35 2.01 4.77 6.63 7.67 11.55 7.67 1.64 0 3.28-0.3 4.92-1.04 6.41-2.68 9.39-10.06 6.63-16.46-23.54-55.5-16.91-118.08 17.73-167.55 0.15-0.22 0.3-0.37 0.45-0.6 12.81-18.18 28.91-33.52 47.68-45.59 0.22-0.15 0.37-0.3 0.6-0.37l1.04-0.67c36.58-25.33 79.57-38.74 124.27-38.74 113.61 0 209.42 88.58 218.21 201.74l1.19 15.27 14.68-4.1c9.68-2.76 19.82-4.1 30.1-4.1 62.43 0 113.16 50.81 113.16 113.24 0 15.13-2.91 29.81-8.72 43.59z\" fill=\"#59AFF7\" p-id=\"2788\"></path></svg>',
            
            // 阴
            'cloudy' => '<svg t=\"1592377851344\" class=\"icon\" viewBox=\"0 0 1445 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"2840\" width=\"24px\" height=\"24px\"><path d=\"M1109.666554 691.563981m-332.436019 0a332.436019 332.436019 0 1 0 664.872038 0 332.436019 332.436019 0 1 0-664.872038 0Z\" fill=\"#91E0FC\" p-id=\"2841\"></path><path d=\"M362.292146 361.554502v-14.559241a344.56872 344.56872 0 1 1 638.180095 184.417061A317.876777 317.876777 0 0 1 537.003047 953.630332a332.436019 332.436019 0 1 1-203.829384-594.50237z\" fill=\"#AAE9FF\" p-id=\"2842\"></path></svg>',
            
            // 有风
	       'gale' => '<svg t=\"1592382631281\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"9706\" width=\"24px\" height=\"24px\"><path d=\"M804.2 441.6H133.4c-17.6 0-32-14.4-32-32s14.4-32 32-32h670.8c17.6 0 32 14.4 32 32s-14.4 32-32 32zM807.3 614.4H475.8c-15.3 0-27.8-12.5-27.8-27.8v-8.3c0-15.3 12.5-27.8 27.8-27.8h331.6c15.3 0 27.8 12.5 27.8 27.8v8.3c0 15.3-12.5 27.8-27.9 27.8z\" fill=\"#72AEFD\" p-id=\"9707\"></path><path d=\"M798.3 441.6c-17.7 0-32-14.3-32-32s14.3-32 32-32c32.5 0 59-26.5 59-59s-26.5-59-59-59c-17.7 0-32-14.3-32-32s14.3-32 32-32c67.8 0 123 55.2 123 123s-55.2 123-123 123zM797.3 790.7c-17.7 0-32-14.3-32-32s14.3-32 32-32c30.9 0 56.1-25.2 56.1-56.1s-25.2-56.1-56.1-56.1c-17.7 0-32-14.3-32-32s14.3-32 32-32c66.2 0 120.1 53.9 120.1 120.1 0.1 66.2-53.8 120.1-120.1 120.1zM354.5 614.4H160c-17.6 0-32-14.4-32-32s14.4-32 32-32h194.5c17.6 0 32 14.4 32 32s-14.4 32-32 32zM544 317.5H288c-17.6 0-32-14.4-32-32s14.4-32 32-32h256c17.6 0 32 14.4 32 32s-14.4 32-32 32zM544 767.1H352c-17.6 0-32-14.4-32-32s14.4-32 32-32h192c17.6 0 32 14.4 32 32s-14.4 32-32 32z\" fill=\"#72AEFD\" p-id=\"9708\"></path></svg>',
	       
	        // 雾霾
	        'smog' => '<svg t=\"1592385129492\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"3833\" width=\"24px\" height=\"24px\"><path d=\"M202.666667 213.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3834\"></path><path d=\"M522.666667 213.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3835\"></path><path d=\"M842.666667 213.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3836\"></path><path d=\"M202.666667 853.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3837\"></path><path d=\"M522.666667 853.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3838\"></path><path d=\"M842.666667 853.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3839\"></path><path d=\"M96 533.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3840\"></path><path d=\"M949.333333 533.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z\" fill=\"#9EB5C7\" p-id=\"3841\"></path><path d=\"M657.066667 364.8c-55.466667 0-102.4 27.733333-132.266667 68.266667-29.866667-40.533333-78.933333-68.266667-132.266667-68.266667-91.733333 0-164.266667 74.666667-164.266666 164.266667s74.666667 164.266667 164.266666 164.266666c55.466667 0 102.4-27.733333 132.266667-68.266666 29.866667 40.533333 78.933333 68.266667 132.266667 68.266666 91.733333 0 164.266667-74.666667 164.266666-164.266666s-74.666667-164.266667-164.266666-164.266667zM390.4 629.333333c-55.466667 0-100.266667-44.8-100.266667-100.266666s44.8-100.266667 100.266667-100.266667 100.266667 44.8 100.266667 100.266667-44.8 100.266667-100.266667 100.266666z m266.666667 0c-55.466667 0-100.266667-44.8-100.266667-100.266666s44.8-100.266667 100.266667-100.266667 100.266667 44.8 100.266666 100.266667-44.8 100.266667-100.266666 100.266666z\" fill=\"#9EB5C7\" p-id=\"3842\"></path></svg>',
            
            // 雨天
            'rain' => '<svg t=\"1592299130507\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"2899\" width=\"22px\" height=\"22px\"><path d=\"M703.043726 396.214567a22.378321 22.378321 0 0 1-22.378321-22.378321c0-121.405588-98.768318-220.173907-220.170709-220.173906s-220.173907 98.768318-220.173907 220.173906a22.378321 22.378321 0 1 1-44.756642 0c0-146.082483 118.848066-264.930549 264.930549-264.930548 146.079286 0 264.927352 118.848066 264.927351 264.930548a22.378321 22.378321 0 0 1-22.378321 22.378321z\" fill=\"#009FE8\" p-id=\"2900\"></path><path d=\"M807.777466 722.129237H213.032025c-102.195398 0-185.337255-83.141856-185.337254-185.337254s83.141856-185.337255 185.337254-185.337255a22.378321 22.378321 0 1 1 0 44.756642c-77.515307 0-140.580613 63.065305-140.580612 140.580613s63.065305 140.580613 140.580612 140.580612h594.745441c77.515307 0 140.580613-63.065305 140.580612-140.580612s-63.065305-140.580613-140.580612-140.580613a22.378321 22.378321 0 1 1 0-44.756642c102.195398 0 185.337255 83.141856 185.337254 185.337255s-83.141856 185.337255-185.337254 185.337254z\" fill=\"#009FE8\" p-id=\"2901\"></path><path d=\"M807.777466 396.214567h-179.148051a22.378321 22.378321 0 1 1 0-44.756642h179.148051a22.378321 22.378321 0 1 1 0 44.756642zM185.148637 916.705542a22.378321 22.378321 0 0 1-13.727501-40.063589l142.342106-110.373076a22.378321 22.378321 0 1 1 27.423034 35.367338l-142.342106 110.373076a22.282414 22.282414 0 0 1-13.695533 4.696251zM392.947332 916.705542a22.378321 22.378321 0 0 1-13.727501-40.063589l142.342106-110.373076a22.378321 22.378321 0 1 1 27.423034 35.367338l-142.342106 110.373076a22.282414 22.282414 0 0 1-13.695533 4.696251zM600.746027 916.705542a22.378321 22.378321 0 0 1-13.727501-40.063589l142.342106-110.373076a22.378321 22.378321 0 1 1 27.423034 35.367338l-142.342106 110.373076a22.282414 22.282414 0 0 1-13.695533 4.696251z\" fill=\"#009FE8\" p-id=\"2902\"></path></svg>',
            
            // 太阳雨
            'sunRain' => '<svg t=\"1592309128577\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"6627\" width=\"24px\" height=\"24px\"><path d=\"M343.309474 941.810526l8.892631-43.92421h14.95579l-15.36 67.368421h-14.282106l-11.317894-41.364211-11.183158 41.364211h-14.95579l-15.494736-67.368421h15.225263l8.757894 43.92421 11.856843-43.92421h10.778947zM415.124211 936.286316h-26.273685v16.707368h30.989474v11.991579H373.221053v-67.368421h46.618947v11.991579h-30.989474v14.686316h26.273685zM462.551579 951.915789h-21.153684l-3.772632 13.473685h-15.76421l21.692631-67.368421h16.707369l21.692631 67.368421h-15.629473zM444.631579 939.924211h14.282105L452.042105 916.210526zM529.381053 909.877895H512v55.107368h-15.898947v-55.107368h-17.51579v-11.991579h50.79579zM591.629474 964.985263h-15.494737v-26.947368h-24.791579v26.947368h-15.629474v-67.368421h15.629474v28.025263h24.791579v-27.755789h15.494737zM644.176842 936.286316h-26.947368v16.707368h30.989473v11.991579H602.273684v-67.368421h46.618948v11.991579h-31.124211v14.686316h26.947368zM671.528421 939.250526v25.734737h-15.629474v-67.368421h25.734737A28.698947 28.698947 0 0 1 700.631579 902.736842a17.515789 17.515789 0 0 1 6.871579 14.686316 13.473684 13.473684 0 0 1-2.829474 8.892631 18.458947 18.458947 0 0 1-8.08421 6.197895 14.416842 14.416842 0 0 1 8.892631 5.793684 17.111579 17.111579 0 0 1 2.694737 10.105264v4.311579a24.926316 24.926316 0 0 0 0.808421 5.928421 8.353684 8.353684 0 0 0 2.694737 4.850526v0.943158h-16.572632a8.353684 8.353684 0 0 1-2.425263-5.12 40.421053 40.421053 0 0 1 0-6.736842v-4.042106a9.431579 9.431579 0 0 0-2.56-7.275789 9.701053 9.701053 0 0 0-7.410526-2.56z m0-11.991579h10.24a10.24 10.24 0 0 0 7.141053-2.155789 7.949474 7.949474 0 0 0 2.56-6.197895 9.431579 9.431579 0 0 0-2.56-6.602105 10.374737 10.374737 0 0 0-7.27579-2.425263h-10.105263z\" fill=\"#3F4A5A\" p-id=\"6628\"></path><path d=\"M687.157895 215.578947m-134.736842 0a134.736842 134.736842 0 1 0 269.473684 0 134.736842 134.736842 0 1 0-269.473684 0Z\" fill=\"#F8C963\" p-id=\"6629\"></path><path d=\"M619.789474 471.578947c0 45.002105-67.368421 76.934737-67.368421 134.736842a67.368421 67.368421 0 0 0 134.736842 0c0-57.802105-67.368421-89.734737-67.368421-134.736842z\" fill=\"#84D7E8\" p-id=\"6630\"></path><path d=\"M458.105263 323.368421m-188.631579 0a188.631579 188.631579 0 1 0 377.263158 0 188.631579 188.631579 0 1 0-377.263158 0Z\" fill=\"#EFEFEF\" p-id=\"6631\"></path><path d=\"M282.947368 431.157895m-107.789473 0a107.789474 107.789474 0 1 0 215.578947 0 107.789474 107.789474 0 1 0-215.578947 0Z\" fill=\"#EFEFEF\" p-id=\"6632\"></path><path d=\"M660.210526 363.789474m-107.789473 0a107.789474 107.789474 0 1 0 215.578947 0 107.789474 107.789474 0 1 0-215.578947 0Z\" fill=\"#EFEFEF\" p-id=\"6633\"></path><path d=\"M754.526316 471.578947m-67.368421 0a67.368421 67.368421 0 1 0 134.736842 0 67.368421 67.368421 0 1 0-134.736842 0Z\" fill=\"#EFEFEF\" p-id=\"6634\"></path><path d=\"M282.947368 431.157895h471.578948v107.789473H282.947368z\" fill=\"#EFEFEF\" p-id=\"6635\"></path><path d=\"M377.263158 606.315789c0 26.947368-40.421053 46.214737-40.421053 80.842106a40.421053 40.421053 0 0 0 80.842106 0c0-34.627368-40.421053-53.894737-40.421053-80.842106zM458.105263 363.789474c0 26.947368-40.421053 46.214737-40.421052 80.842105a40.421053 40.421053 0 0 0 80.842105 0c0-34.627368-40.421053-53.894737-40.421053-80.842105z\" fill=\"#84D7E8\" p-id=\"6636\"></path></svg>',
            
            // 雷雨
            'thunderRain' => '<svg t=\"1592385356910\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"4710\" width=\"26px\" height=\"26px\"><path d=\"M549.088 652.608l-12.896 63.2h71.744l-156.224 200.768 29.408-144.256h-75.52l24.416-119.744h119.072z m-330.88 142.848l54.304-54.592c6.912-6.944 18.08-6.944 24.992 0s6.912 18.176 0 25.12L243.2 820.576a17.504 17.504 0 0 1-24.96 0 17.792 17.792 0 0 1 0-25.12z m501.12-54.592c6.88-6.944 18.08-6.944 24.992 0s6.912 18.176 0 25.12l-54.304 54.592a17.536 17.536 0 0 1-24.96 0 17.792 17.792 0 0 1 0-25.12l54.304-54.592z\" fill=\"#43A3FB\" p-id=\"4711\"></path><path d=\"M243.776 664.256c-68.608 0-124.416-55.52-124.416-123.808 0-67.904 55.232-123.232 123.328-123.808 9.12-118.56 108.96-212.256 230.368-212.256 81.92 0 156.576 42.56 198.24 112.064a176.384 176.384 0 0 1 45.248-5.952c98.016 0 177.728 79.328 177.728 176.864s-79.712 176.864-177.728 176.864H243.776z\" fill=\"#C0DCFA\" p-id=\"4712\"></path></svg>',
            
            // 暴雨
            'rainStorm' => '<svg t=\"1592385561488\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"5614\" width=\"24px\" height=\"24px\"><path d=\"M798.4 262.4c-28.5 0-55.7 5.8-80.4 16.3-16.2-100.3-103.2-177-208.1-177-92 0-170.1 58.9-199 141-24.6-10-51.5-15.6-79.7-15.6-116.5 0-210.9 94.4-210.9 210.9s94.4 210.9 210.9 210.9c5.3 0 10.5-0.2 15.7-0.6v0.6h647.8c64.8-34.5 108.9-102.7 108.9-181.2 0-113.4-91.8-205.3-205.2-205.3zM410.3 751.3c-6.5 0-64.4 72.2-64.4 107.8s28.8 63.1 64.4 63.1c35.6 0 64.4-27.5 64.4-63.1s-57.2-107.8-64.4-107.8zM188.9 751.3c-6.5 0-64.4 72.2-64.4 107.8s28.8 63.1 64.4 63.1c35.6 0 64.4-27.5 64.4-63.1s-57.2-107.8-64.4-107.8zM853 751.3c-6.5 0-64.4 72.2-64.4 107.8s28.8 63.1 64.4 63.1 64.4-27.5 64.4-63.1-57.2-107.8-64.4-107.8zM631.6 751.3c-6.5 0-64.4 72.2-64.4 107.8s28.8 63.1 64.4 63.1c35.6 0 64.4-27.5 64.4-63.1s-57.2-107.8-64.4-107.8z\" fill=\"#9FDAFF\" p-id=\"5615\"></path></svg>',
            
            // 雨夹雪
            'sleet' => '<svg t=\"1592385683641\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"7758\" data-spm-anchor-id=\"a313x.7781069.0.i12\" width=\"22px\" height=\"22px\"><path d=\"M194.293 813.523c-13.606-1.807-26.471 7.612-28.411 21.354l-12.999 92.02c-1.941 13.74 7.63 26.451 21.388 28.394 1.183 0.169 2.363 0.253 3.545 0.253 12.323 0 23.094-9.065 24.866-21.624l12.999-92.002c1.942-13.742-7.63-26.453-21.388-28.395z m547.585 0c-13.657-1.807-26.486 7.612-28.429 21.354l-12.998 92.02c-1.94 13.74 7.631 26.451 21.389 28.394 1.197 0.169 2.38 0.253 3.545 0.253 12.34 0 23.109-9.065 24.866-21.624l12.997-92.002c1.958-13.742-7.63-26.453-21.37-28.395z m274.82-548.057C994.35 138.572 873.482 53.678 747.111 76.028c-46.95 8.333-89.018 30.17-122.548 63.443a229.261 229.261 0 0 0-59.969-7.956c-80.252 0-154.31 42.135-196.057 109.49-25.523-16.982-55.437-26.098-86.7-26.098-86.802 0-157.432 70.951-157.432 158.158 0 9.015 0.759 17.944 2.262 26.739C53.253 427.372 3.775 497.731 3.775 578.354c0 103.516 81.604 181.572 189.826 181.572 13.066 0 23.65-10.651 23.65-23.769 0-13.134-10.584-23.785-23.65-23.785-81.248 0-142.492-57.615-142.492-134.019 0-66.63 44.972-123.924 109.39-139.285a23.58 23.58 0 0 0 15.227-11.598 23.845 23.845 0 0 0 1.722-19.108c-3.781-11.344-5.705-23.211-5.705-35.298 0-60.991 49.394-110.604 110.098-110.604 29.152 0 56.652 11.344 77.416 31.956 5.521 5.453 13.336 7.934 20.933 6.449 7.596-1.401 14.045-6.466 17.252-13.539 29.693-65.769 95.311-108.274 167.155-108.274 101.149 0 183.462 82.7 183.462 184.323 0 10.466-2.971 31.686-4.118 39.164a23.847 23.847 0 0 0-0.305 3.815c0 11.732 8.475 21.591 19.817 23.465 63.692 15.023 109.777 73.432 109.777 139.386 0 78.936-63.929 143.168-142.492 143.168-13.065 0-23.667 10.651-23.667 23.785 0 13.116 10.602 23.769 23.667 23.769 104.662 0 189.811-85.569 189.811-190.722 0-22.175-3.92-43.705-11.135-63.83 80.32-49.666 123.969-145.182 107.284-239.909zM887.891 462.825c-22.539-33.532-55.414-59.859-94.408-73.655 0.996-8.694 1.891-18.434 1.891-25.794 0-88.182-49.251-165.011-121.562-204.205 23.618-18.546 51.308-30.945 81.537-36.315 100.645-17.911 196.95 49.815 214.743 150.899 13.06 74.179-20.303 148.969-82.201 189.07zM657.37 720.307h-50.003l29.611-29.744c6.161-6.194 6.161-16.238 0-22.418a15.724 15.724 0 0 0-22.317 0l-51.913 52.162h-62.516l111.12-111.649c6.162-6.195 6.162-16.224 0-22.418a15.748 15.748 0 0 0-22.316 0L477.927 697.88v-62.824l51.909-52.141c6.16-6.179 6.16-16.223 0-22.401-6.162-6.194-16.155-6.194-22.301 0l-29.608 29.741v-50.233c0-8.763-7.057-15.853-15.768-15.853s-15.767 7.09-15.767 15.853v50.233l-29.609-29.741c-6.162-6.194-16.139-6.194-22.316 0-6.146 6.18-6.146 16.224 0 22.401l51.926 52.158v62.808L335.283 586.24a15.75 15.75 0 0 0-22.316 0c-6.146 6.194-6.146 16.223 0 22.418l111.121 111.649h-62.531l-51.898-52.161c-6.178-6.195-16.154-6.195-22.315 0-6.146 6.179-6.146 16.223 0 22.418l29.601 29.743H266.95c-8.712 0-15.768 7.091-15.768 15.853 0 8.761 7.056 15.852 15.768 15.852h49.986l-29.595 29.728c-6.146 6.195-6.146 16.223 0 22.418a15.708 15.708 0 0 0 11.158 4.643c4.033 0 8.068-1.553 11.157-4.643l51.882-52.146h62.514L312.966 863.626c-6.146 6.195-6.146 16.223 0 22.418a15.737 15.737 0 0 0 11.159 4.643c4.033 0 8.068-1.553 11.157-4.643l111.11-111.641v62.824l-51.926 52.158c-6.146 6.195-6.146 16.224 0 22.401 6.178 6.195 16.154 6.195 22.316 0l29.609-29.752v50.262c0 8.762 7.056 15.835 15.767 15.835 8.71 0 15.768-7.073 15.768-15.835v-50.263l29.608 29.753a15.628 15.628 0 0 0 11.143 4.659c4.034 0 8.067-1.555 11.158-4.659 6.16-6.179 6.16-16.206 0-22.401l-51.909-52.157v-62.825l111.109 111.641a15.741 15.741 0 0 0 11.158 4.644c4.035 0 8.069-1.554 11.158-4.644 6.162-6.195 6.162-16.223 0-22.418L500.268 752.01h62.531l51.862 52.128a15.746 15.746 0 0 0 11.159 4.644c4.034 0 8.068-1.555 11.158-4.644 6.161-6.194 6.161-16.223 0-22.418L607.4 752.01h49.97c8.711 0 15.769-7.091 15.769-15.852 0-8.762-7.058-15.851-15.769-15.851z\" fill=\"#1296db\" p-id=\"7759\" data-spm-anchor-id=\"a313x.7781069.0.i11\" class=\"\"></path></svg>',
	    
	         // 雪
	        'snow' => '<svg t=\"1592310022218\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"21174\" width=\"20px\" height=\"20px\"><path d=\"M788.48 925.9008c-16.7936 10.6496-39.3216 5.7344-49.9712-11.0592l-509.952-803.4304c-10.6496-16.7936-5.7344-39.3216 11.0592-49.9712 16.7936-10.6496 39.3216-5.7344 49.9712 11.0592l510.1568 803.4304c10.4448 16.7936 5.5296 39.3216-11.264 49.9712z\" fill=\"#A1E4FF\" p-id=\"21175\"></path><path d=\"M785.8176 54.6816c16.9984 10.24 22.528 32.5632 12.288 49.5616L308.224 920.1664c-10.24 16.9984-32.5632 22.528-49.5616 12.288-16.9984-10.24-22.528-32.5632-12.288-49.5616L736.256 67.1744c10.24-16.9984 32.5632-22.528 49.5616-12.4928z\" fill=\"#A1E4FF\" p-id=\"21176\"></path><path d=\"M1024 501.9648c0 19.8656-16.1792 36.0448-36.0448 36.0448H36.2496C16.384 538.0096 0.2048 521.8304 0.2048 501.9648c0-19.8656 16.1792-36.0448 36.0448-36.0448h951.7056c19.8656-0.2048 36.0448 16.1792 36.0448 36.0448z\" fill=\"#A1E4FF\" p-id=\"21177\"></path><path d=\"M395.4688 504.6272a133.5296 127.1808 90 1 0 254.3616 0 133.5296 127.1808 90 1 0-254.3616 0Z\" fill=\"#A1E4FF\" p-id=\"21178\"></path><path d=\"M523.4688 583.4752c-20.6848 0-37.6832-16.9984-37.6832-37.6832v-82.7392c0-20.6848 16.9984-37.6832 37.6832-37.6832 20.6848 0 37.6832 16.9984 37.6832 37.6832v82.7392c0 20.8896-16.9984 37.6832-37.6832 37.6832z\" fill=\"#FFFFFF\" p-id=\"21179\"></path><path d=\"M598.6304 504.6272c0 21.7088-17.8176 39.5264-39.5264 39.5264h-71.4752c-21.7088 0-39.5264-17.8176-39.5264-39.5264 0-21.7088 17.8176-39.5264 39.5264-39.5264H559.104c21.9136 0 39.5264 17.8176 39.5264 39.5264z\" fill=\"#FFFFFF\" p-id=\"21180\"></path><path d=\"M559.3088 287.1296c0 45.8752-34.4064 82.944-34.4064 82.944s-34.4064-37.0688-34.4064-82.944 15.36-46.6944 34.4064-46.6944c18.8416 0 34.4064 0.8192 34.4064 46.6944z\" fill=\"#A6F2FF\" p-id=\"21181\"></path><path d=\"M414.72 142.5408c-20.2752 31.744-54.4768 44.8512-54.4768 44.8512s-1.4336-38.2976 18.6368-70.0416 28.672-26.8288 38.5024-19.8656c9.8304 7.168 17.408 13.312-2.6624 45.056zM235.52 219.7504c35.6352-7.3728 68.8128 8.8064 68.8128 8.8064s-24.7808 28.2624-60.416 35.6352c-35.6352 7.3728-38.2976-2.4576-40.5504-14.7456-2.2528-12.288-3.4816-22.3232 32.1536-29.696zM786.2272 256.6144c-34.816-10.8544-56.9344-41.1648-56.9344-41.1648s34.4064-12.9024 69.2224-2.048 32.768 20.6848 29.4912 32.768-6.9632 21.2992-41.7792 10.4448zM650.0352 109.3632c20.8896 31.1296 20.48 69.4272 20.48 69.4272s-34.6112-12.288-55.5008-43.4176-13.5168-37.6832-3.8912-44.8512c9.6256-6.9632 17.8176-12.288 38.912 18.8416zM843.9808 610.304c-4.9152-37.888 12.4928-71.4752 12.4928-71.4752s25.1904 27.648 30.1056 65.536c4.9152 37.888-4.5056 39.936-16.384 41.5744-11.6736 1.4336-21.2992 2.048-26.2144-35.6352zM916.2752 419.4304c-18.432 32.768-52.0192 47.9232-52.0192 47.9232s-3.4816-38.0928 14.9504-71.0656c18.432-32.768 27.2384-28.2624 37.4784-21.9136 10.24 6.3488 18.2272 12.288-0.4096 45.056zM609.8944 835.9936c26.624-26.0096 62.8736-30.3104 62.8736-30.3104s-6.9632 37.6832-33.5872 63.488c-26.624 26.0096-33.792 19.0464-41.7792 9.8304-8.192-9.216-14.336-16.9984 12.4928-43.008zM803.0208 782.336c-35.4304 9.0112-69.0176-5.9392-69.0176-5.9392s23.552-29.2864 58.9824-38.0928c35.4304-9.0112 38.2976 0.8192 41.1648 12.9024 2.6624 12.288 4.096 22.3232-31.1296 31.1296zM245.1456 750.592c36.0448 4.7104 62.6688 30.9248 62.6688 30.9248s-31.9488 18.6368-67.9936 14.1312-35.6352-14.7456-34.2016-27.2384c1.4336-12.4928 3.4816-22.528 39.5264-17.8176zM408.7808 870.8096c-29.2864-22.528-40.3456-59.1872-40.3456-59.1872s36.4544 0 65.7408 22.7328c29.2864 22.528 24.1664 31.1296 17.2032 41.3696-6.9632 10.0352-13.312 17.8176-42.5984-4.9152zM189.44 394.6496c10.4448 36.4544-1.6384 72.704-1.6384 72.704S158.72 444.416 148.2752 407.7568c-10.4448-36.4544-1.4336-40.1408 9.8304-43.8272 11.4688-3.4816 20.8896-5.7344 31.3344 30.72zM150.528 600.4736c7.9872-37.2736 35.2256-62.464 35.2256-62.464s14.5408 35.0208 6.7584 72.2944c-7.9872 37.2736-17.408 35.84-29.0816 33.1776-11.6736-2.8672-20.8896-5.7344-12.9024-43.008z\" fill=\"#A6EAFF\" p-id=\"21182\"></path><path d=\"M490.2912 721.3056c0-45.8752 34.4064-82.944 34.4064-82.944s34.4064 37.0688 34.4064 82.944-15.36 46.6944-34.4064 46.6944-34.4064-0.8192-34.4064-46.6944zM349.3888 380.928c39.1168 20.48 55.296 69.4272 55.296 69.4272s-47.104 15.7696-86.016-4.5056-32.9728-35.4304-24.3712-53.248 15.9744-31.9488 55.0912-11.6736zM697.344 635.0848c-41.1648-15.9744-62.6688-62.6688-62.6688-62.6688s44.8512-21.0944 86.016-5.12c41.1648 15.9744 36.6592 31.3344 30.3104 50.176-6.5536 18.6368-12.6976 33.5872-53.6576 17.6128zM319.6928 566.8864c40.96-16.9984 86.2208 2.8672 86.2208 2.8672S385.024 617.0624 344.064 634.0608c-40.96 16.9984-47.104 2.2528-53.8624-16.1792-6.7584-18.6368-11.264-33.9968 29.4912-50.9952zM719.0528 437.6576c-41.1648 16.1792-86.2208-4.3008-86.2208-4.3008s21.7088-46.8992 62.8736-63.0784c41.1648-16.1792 47.3088-1.4336 53.6576 17.2032 6.5536 18.432 10.8544 33.9968-30.3104 50.176z\" fill=\"#BDF3FF\" p-id=\"21183\"></path></svg>',
	        
	         // 雪
	        'snow01' => '<svg t=\"1592309905144\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"16193\" width=\"20px\" height=\"20px\"><path d=\"M756.775877 436.349215m-208.813981 0a208.813981 208.813981 0 1 0 417.627962 0 208.813981 208.813981 0 1 0-417.627962 0Z\" fill=\"#91E0FC\" p-id=\"16194\"></path><path d=\"M285.864348 227.535234v-10.080675a217.454559 217.454559 0 1 1 403.226997 116.64781 200.173402 200.173402 0 0 1-293.779669 267.857934 208.813981 208.813981 0 1 1-128.168581-374.425069z\" fill=\"#AAE9FF\" p-id=\"16195\"></path><path d=\"M568.123246 527.075289l30.242025-7.200482a4.320289 4.320289 0 0 0 4.320289-5.760385 5.760386 5.760386 0 0 0-5.760385-2.880193l-40.3227 10.080675-31.682121-17.281157 34.562314-18.721254 40.322699 10.080675h1.440097a5.760386 5.760386 0 0 0 5.760386-4.320289 4.320289 4.320289 0 0 0-4.32029-5.760386l-30.242025-7.200482 34.562315-17.281157a4.320289 4.320289 0 0 0 1.440096-7.200482 5.760386 5.760386 0 0 0-7.200482-1.440096l-36.002411 18.721253 8.640579-27.361832a4.320289 4.320289 0 0 0-4.320289-5.760386 5.760386 5.760386 0 0 0-5.760386 2.880193l-11.520771 37.442507-33.122218 18.721254v-37.442507l30.242025-27.361832a4.320289 4.320289 0 0 0 0-7.200482 5.760386 5.760386 0 0 0-7.200482 0L519.159968 446.42989v-38.882603a5.760386 5.760386 0 0 0-10.080675 0V446.42989l-21.601446-20.16135a5.760386 5.760386 0 0 0-7.200482 0 4.320289 4.320289 0 0 0 0 7.200482l30.242024 27.361832v37.442507L475.957075 479.552108l-11.520771-37.442507a5.760386 5.760386 0 0 0-5.760386-2.880193 4.320289 4.320289 0 0 0-4.320289 5.760385l8.640579 27.361832-36.002411-18.721253a5.760386 5.760386 0 0 0-7.200482 1.440096 4.320289 4.320289 0 0 0 1.440097 7.200482l36.00241 18.721254-30.242025 7.200482a4.320289 4.320289 0 0 0-4.320289 5.760386 5.760386 5.760386 0 0 0 5.760386 2.880193l40.322699-10.080675 34.562314 17.281157-30.242024 15.84106-40.3227-8.640578a5.760386 5.760386 0 0 0-5.760386 2.880193 4.320289 4.320289 0 0 0 2.880193 5.760385l30.242025 7.200482-40.3227 21.601447a4.320289 4.320289 0 0 0-2.880193 5.760385 5.760386 5.760386 0 0 0 4.32029 2.880193h2.880192l40.3227-21.601446-8.640578 27.361832a4.320289 4.320289 0 0 0 4.320289 5.760385 5.760386 5.760386 0 0 0 5.760385-2.880192l11.520772-37.442507 30.242025-15.841061v33.122218l-30.242025 27.361832a4.320289 4.320289 0 0 0 0 7.200482 5.760386 5.760386 0 0 0 7.200482 0l21.601446-20.16135v43.202893a5.760386 5.760386 0 0 0 10.080675 0v-43.202893l21.601446 20.16135a5.760386 5.760386 0 0 0 7.200483 0 4.320289 4.320289 0 0 0 0-7.200482L519.159968 545.796543v-33.122218l30.242025 15.841061 11.520771 37.442507a5.760386 5.760386 0 0 0 5.760386 2.880192 4.320289 4.320289 0 0 0 4.320289-5.760385l-8.640578-27.361832 40.322699 21.601446h2.880193l2.880193-2.880193a4.320289 4.320289 0 0 0 0-4.320289L605.565753 547.236639z\" fill=\"#D9F5FF\" p-id=\"16196\"></path><path d=\"M467.316497 786.292645l30.242025-7.200482a4.320289 4.320289 0 0 0 4.320289-5.760386 5.760386 5.760386 0 0 0-5.760386-2.880193l-40.322699 10.080675-31.682122-17.281157 34.562314-18.721253 40.3227 10.080675h1.440097a5.760386 5.760386 0 0 0 5.760385-4.32029 4.320289 4.320289 0 0 0-4.320289-5.760385l-30.242025-7.200482 34.562314-17.281158a4.320289 4.320289 0 0 0 1.440097-7.200482 5.760386 5.760386 0 0 0-7.200482-1.440096l-36.002411 18.721253 8.640579-27.361832a4.320289 4.320289 0 0 0-4.32029-5.760385 5.760386 5.760386 0 0 0-5.760385 2.880193l-11.520772 37.442507-33.122217 18.721253v-37.442507l30.242024-27.361832a4.320289 4.320289 0 0 0 0-7.200482 5.760386 5.760386 0 0 0-7.200482 0L418.353219 705.647245v-38.882603a5.760386 5.760386 0 0 0-10.080675 0V705.647245l-21.601447-20.16135a5.760386 5.760386 0 0 0-7.200482 0 4.320289 4.320289 0 0 0 0 7.200483l30.242025 27.361831v37.442507L375.150326 738.769463l-11.520771-37.442507a5.760386 5.760386 0 0 0-5.760386-2.880193 4.320289 4.320289 0 0 0-4.320289 5.760386l8.640578 27.361832-36.00241-18.721254a5.760386 5.760386 0 0 0-7.200482 1.440097 4.320289 4.320289 0 0 0 1.440096 7.200482l36.002411 18.721253-30.242025 7.200482a4.320289 4.320289 0 0 0-4.320289 5.760386 5.760386 5.760386 0 0 0 5.760385 2.880193l40.3227-10.080675 34.562314 17.281157-30.242025 15.841061-40.322699-8.640579a5.760386 5.760386 0 0 0-5.760386 2.880193 4.320289 4.320289 0 0 0 2.880193 5.760386l30.242024 7.200482-40.322699 21.601446a4.320289 4.320289 0 0 0-2.880193 5.760386 5.760386 5.760386 0 0 0 4.320289 2.880193h2.880193l40.3227-21.601447-8.640579 27.361832a4.320289 4.320289 0 0 0 4.320289 5.760386 5.760386 5.760386 0 0 0 5.760386-2.880193l11.520772-37.442507 30.242024-15.84106v33.122217l-30.242024 27.361832a4.320289 4.320289 0 0 0 0 7.200482 5.760386 5.760386 0 0 0 7.200482 0l21.601446-20.16135v43.202893a5.760386 5.760386 0 0 0 10.080675 0v-43.202893l21.601446 20.16135a5.760386 5.760386 0 0 0 7.200482 0 4.320289 4.320289 0 0 0 0-7.200482L418.353219 805.013898v-33.122217l30.242024 15.84106 11.520772 37.442507a5.760386 5.760386 0 0 0 5.760385 2.880193 4.320289 4.320289 0 0 0 4.32029-5.760386l-8.640579-27.361832 40.3227 21.601447h2.880193l2.880193-2.880193a4.320289 4.320289 0 0 0 0-4.32029L504.759004 806.453995zM654.529031 944.703251l30.242025-7.200482a4.320289 4.320289 0 0 0 4.320289-5.760386 5.760386 5.760386 0 0 0-5.760385-2.880193l-40.3227 10.080675-31.682121-17.281157 34.562314-18.721253 40.3227 10.080675h1.440096a5.760386 5.760386 0 0 0 5.760386-4.32029 4.320289 4.320289 0 0 0-4.32029-5.760385l-30.242024-7.200482 34.562314-17.281157a4.320289 4.320289 0 0 0 1.440096-7.200483 5.760386 5.760386 0 0 0-7.200482-1.440096l-36.00241 18.721253 8.640578-27.361831a4.320289 4.320289 0 0 0-4.320289-5.760386 5.760386 5.760386 0 0 0-5.760386 2.880193l-11.520771 37.442507-33.122218 18.721253v-37.442507l30.242025-27.361832a4.320289 4.320289 0 0 0 0-7.200482 5.760386 5.760386 0 0 0-7.200482 0L605.565753 864.057851v-38.882603a5.760386 5.760386 0 0 0-10.080675 0V864.057851l-21.601446-20.161349a5.760386 5.760386 0 0 0-7.200482 0 4.320289 4.320289 0 0 0 0 7.200482l30.242025 27.361832v37.442506L562.362861 897.180069l-11.520772-37.442507a5.760386 5.760386 0 0 0-5.760385-2.880193 4.320289 4.320289 0 0 0-4.32029 5.760386l8.640579 27.361832-36.002411-18.721254a5.760386 5.760386 0 0 0-7.200482 1.440097 4.320289 4.320289 0 0 0 1.440097 7.200482l36.00241 18.721253-30.242025 7.200483a4.320289 4.320289 0 0 0-4.320289 5.760385 5.760386 5.760386 0 0 0 5.760386 2.880193l40.322699-10.080675 34.562315 17.281157-30.242025 15.841061-40.3227-8.640579a5.760386 5.760386 0 0 0-5.760386 2.880193 4.320289 4.320289 0 0 0 2.880193 5.760386l30.242025 7.200482-40.3227 21.601446a4.320289 4.320289 0 0 0-2.880193 5.760386 5.760386 5.760386 0 0 0 4.32029 2.880193h2.880192l40.3227-21.601447-8.640578 27.361832a4.320289 4.320289 0 0 0 4.320289 5.760386 5.760386 5.760386 0 0 0 5.760386-2.880193l11.520771-37.442507 30.242025-15.84106v33.122217l-30.242025 27.361832a4.320289 4.320289 0 0 0 0 7.200482 5.760386 5.760386 0 0 0 7.200482 0l21.601446-20.16135v43.202893a5.760386 5.760386 0 0 0 10.080675 0v-43.202893l21.601447 20.16135a5.760386 5.760386 0 0 0 7.200482 0 4.320289 4.320289 0 0 0 0-7.200482L605.565753 963.424504v-33.122217l30.242025 15.84106 11.520771 37.442507a5.760386 5.760386 0 0 0 5.760386 2.880193 4.320289 4.320289 0 0 0 4.320289-5.760386l-8.640578-27.361832 40.322699 21.601447h2.880193l2.880193-2.880193a4.320289 4.320289 0 0 0 0-4.320289L691.971538 964.864601z\" fill=\"#9BE5FF\" p-id=\"16197\"></path></svg>',
	        
	       // 沙尘
	       'dust' => '<svg t=\"1592386154500\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"14260\" width=\"24px\" height=\"24px\"><path d=\"M128.085333 426.538667a85.333333 85.290667 90 1 0 170.581334 0 85.333333 85.290667 90 1 0-170.581334 0Z\" fill=\"#FFE97C\" p-id=\"14261\"></path><path d=\"M469.248 426.538667a42.709333 42.666667 0 1 0 85.418667 0 42.709333 42.666667 0 1 0-85.418667 0Z\" fill=\"#FFE97C\" p-id=\"14262\"></path><path d=\"M725.290667 426.538667a85.333333 85.290667 90 1 0 170.581333 0 85.333333 85.290667 90 1 0-170.581333 0Z\" fill=\"#FFE97C\" p-id=\"14263\"></path><path d=\"M211.328 639.872m-42.666667 0a42.666667 42.666667 0 1 0 85.333334 0 42.666667 42.666667 0 1 0-85.333334 0Z\" fill=\"#FFE97C\" p-id=\"14264\"></path><path d=\"M509.909333 639.872m-85.333333 0a85.333333 85.333333 0 1 0 170.666667 0 85.333333 85.333333 0 1 0-170.666667 0Z\" fill=\"#FFE97C\" p-id=\"14265\"></path><path d=\"M808.533333 639.872m-42.666666 0a42.666667 42.666667 0 1 0 85.333333 0 42.666667 42.666667 0 1 0-85.333333 0Z\" fill=\"#FFE97C\" p-id=\"14266\"></path><path d=\"M211.328 213.205333m-42.666667 0a42.666667 42.666667 0 1 0 85.333334 0 42.666667 42.666667 0 1 0-85.333334 0Z\" fill=\"#FFE97C\" p-id=\"14267\"></path><path d=\"M509.909333 213.205333m-85.333333 0a85.333333 85.333333 0 1 0 170.666667 0 85.333333 85.333333 0 1 0-170.666667 0Z\" fill=\"#FFE97C\" p-id=\"14268\"></path><path d=\"M808.533333 213.205333m-42.666666 0a42.666667 42.666667 0 1 0 85.333333 0 42.666667 42.666667 0 1 0-85.333333 0Z\" fill=\"#FFE97C\" p-id=\"14269\"></path><path d=\"M896 789.333333a21.333333 21.333333 0 0 0-21.333333-21.333333h-725.333334a21.333333 21.333333 0 0 0 0 42.666667h725.333334a21.333333 21.333333 0 0 0 21.333333-21.333334z\" fill=\"#8ECDFF\" p-id=\"14270\"></path><path d=\"M875.776 768.042667l1.066667 0.085333 1.066666 0.128 1.066667 0.170667 1.024 0.256 1.024 0.256 0.981333 0.341333 0.981334 0.384 0.938666 0.426667 0.896 0.469333 0.896 0.512 0.853334 0.554667 0.853333 0.597333 0.810667 0.64 0.768 0.682667 0.725333 0.725333 0.725333 0.725333 0.682667 0.768 0.64 0.810667 0.597333 0.853333 0.554667 0.853334 0.512 0.896 0.469333 0.896 0.426667 0.938666 0.384 0.981334 0.341333 0.981333 0.256 1.024 0.256 1.024 0.170667 1.066667 0.128 1.066666 0.085333 1.066667 0.042667 1.109333-0.042667 1.109334-0.085333 1.066666-0.128 1.066667-0.170667 1.066667-0.256 1.024-0.256 1.024-0.341333 0.981333-0.384 0.981333-0.426667 0.938667-0.469333 0.896-0.512 0.896-0.554667 0.853333-0.597333 0.853334-0.64 0.810666-0.682667 0.768-0.725333 0.725334-0.725333 0.725333-0.768 0.682667-0.810667 0.64-0.853333 0.597333-0.853334 0.554667-0.896 0.512-0.896 0.469333-0.938666 0.426667-0.981334 0.384-0.981333 0.341333-1.024 0.256-1.024 0.256-1.066667 0.170667-1.066666 0.128-1.066667 0.085333L874.666667 810.666667h-725.333334l-1.109333-0.042667-1.066667-0.085333-1.066666-0.128-1.066667-0.170667-1.024-0.256-1.024-0.256-0.981333-0.341333-0.981334-0.384-0.938666-0.426667-0.896-0.469333-0.896-0.512-0.853334-0.554667-0.853333-0.597333-0.810667-0.64-0.768-0.682667-0.725333-0.725333-0.725333-0.725334-0.682667-0.768-0.64-0.810666-0.597333-0.853334-0.554667-0.853333-0.512-0.896-0.469333-0.896-0.426667-0.938667-0.384-0.981333-0.341333-0.981333-0.256-1.024-0.256-1.024-0.170667-1.066667-0.128-1.066667-0.085333-1.066666L128 789.333333l0.042667-1.109333 0.085333-1.066667 0.128-1.066666 0.170667-1.066667 0.256-1.024 0.256-1.024 0.341333-0.981333 0.384-0.981334 0.426667-0.938666 0.469333-0.896 0.512-0.896 0.554667-0.853334 0.597333-0.853333 0.64-0.810667 0.682667-0.768 0.725333-0.725333 0.725333-0.725333 0.768-0.682667 0.810667-0.64 0.853333-0.597333 0.853334-0.554667 0.896-0.512 0.896-0.469333 0.938666-0.426667 0.981334-0.384 0.981333-0.341333 1.024-0.256 1.024-0.256 1.066667-0.170667 1.066666-0.128 1.066667-0.085333L149.333333 768h725.333334l1.109333 0.042667z\" fill=\"#FFB28E\" p-id=\"14271\"></path><path d=\"M853.333333 874.666667a21.333333 21.333333 0 0 0-21.333333-21.333334h-42.666667a21.333333 21.333333 0 0 0 0 42.666667h42.666667a21.333333 21.333333 0 0 0 21.333333-21.333333z\" fill=\"#8ECDFF\" p-id=\"14272\"></path><path d=\"M833.109333 853.376l1.066667 0.085333 1.066667 0.128 1.066666 0.170667 1.024 0.256 1.024 0.256 0.981334 0.341333 0.981333 0.384 0.938667 0.426667 0.896 0.469333 0.896 0.512 0.853333 0.554667 0.853333 0.597333 0.810667 0.64 0.768 0.682667 0.725333 0.725333 0.725334 0.725334 0.682666 0.768 0.64 0.810666 0.597334 0.853334 0.554666 0.853333 0.512 0.896 0.469334 0.896 0.426666 0.938667 0.384 0.981333 0.341334 0.981333 0.256 1.024 0.256 1.024 0.170666 1.066667 0.128 1.066667 0.085334 1.066666 0.042666 1.109334-0.042666 1.109333-0.085334 1.066667-0.128 1.066666-0.170666 1.066667-0.256 1.024-0.256 1.024-0.341334 0.981333-0.384 0.981334-0.426666 0.938666-0.469334 0.896-0.512 0.896-0.554666 0.853334-0.597334 0.853333-0.64 0.810667-0.682666 0.768-0.725334 0.725333-0.725333 0.725333-0.768 0.682667-0.810667 0.64-0.853333 0.597333-0.853333 0.554667-0.896 0.512-0.896 0.469333-0.938667 0.426667-0.981333 0.384-0.981334 0.341333-1.024 0.256-1.024 0.256-1.066666 0.170667-1.066667 0.128-1.066667 0.085333L832 896h-42.666667l-1.109333-0.042667-1.066667-0.085333-1.066666-0.128-1.066667-0.170667-1.024-0.256-1.024-0.256-0.981333-0.341333-0.981334-0.384-0.938666-0.426667-0.896-0.469333-0.896-0.512-0.853334-0.554667-0.853333-0.597333-0.810667-0.64-0.768-0.682667-0.725333-0.725333-0.725333-0.725333-0.682667-0.768-0.64-0.810667-0.597333-0.853333-0.554667-0.853334-0.512-0.896-0.469333-0.896-0.426667-0.938666-0.384-0.981334-0.341333-0.981333-0.256-1.024-0.256-1.024-0.170667-1.066667-0.128-1.066666-0.085333-1.066667L768 874.666667l0.042667-1.109334 0.085333-1.066666 0.128-1.066667 0.170667-1.066667 0.256-1.024 0.256-1.024 0.341333-0.981333 0.384-0.981333 0.426667-0.938667 0.469333-0.896 0.512-0.896 0.554667-0.853333 0.597333-0.853334 0.64-0.810666 0.682667-0.768 0.725333-0.725334 0.725333-0.725333 0.768-0.682667 0.810667-0.64 0.853333-0.597333 0.853334-0.554667 0.896-0.512 0.896-0.469333 0.938666-0.426667 0.981334-0.384 0.981333-0.341333 1.024-0.256 1.024-0.256 1.066667-0.170667 1.066666-0.128 1.066667-0.085333L789.333333 853.333333h42.666667l1.109333 0.042667zM810.666667 874.666667v0.554666-1.109333 0.554667z\" fill=\"#FFB28E\" p-id=\"14273\"></path><path d=\"M725.76 874.666667a21.333333 21.333333 0 0 0-21.333333-21.333334H192a21.333333 21.333333 0 0 0 0 42.666667h512.426667a21.333333 21.333333 0 0 0 21.333333-21.333333z\" fill=\"#8ECDFF\" p-id=\"14274\"></path><path d=\"M705.493333 853.376l1.109334 0.085333 1.066666 0.128 1.024 0.170667 1.066667 0.256 0.981333 0.256 1.024 0.341333 0.938667 0.384 0.938667 0.426667 0.938666 0.469333 0.896 0.512 0.853334 0.554667 0.853333 0.597333 0.810667 0.64 0.768 0.682667 0.725333 0.725333 0.725333 0.725334 0.64 0.768 0.64 0.810666 0.597334 0.853334 0.554666 0.853333 0.512 0.896 0.469334 0.896 0.426666 0.938667 0.384 0.981333 0.341334 0.981333 0.298666 1.024 0.256 1.024 0.170667 1.066667 0.128 1.066667 0.085333 1.066666 0.042667 1.109334-0.042667 1.109333-0.085333 1.066667-0.128 1.066666-0.170667 1.066667-0.256 1.024-0.298666 1.024-0.341334 0.981333-0.384 0.981334-0.426666 0.938666-0.469334 0.896-0.512 0.896-0.554666 0.853334-0.597334 0.853333-0.64 0.810667-0.64 0.768-0.725333 0.725333-0.725333 0.725333-0.768 0.682667-0.810667 0.64-0.853333 0.597333-0.853334 0.554667-0.896 0.512-0.938666 0.469333-0.938667 0.426667-0.938667 0.384-1.024 0.341333-0.981333 0.256-1.066667 0.256-1.024 0.170667-1.066666 0.128-1.109334 0.085333-1.066666 0.042667H192l-1.109333-0.042667-1.066667-0.085333-1.066667-0.128-1.066666-0.170667-1.024-0.256-1.024-0.256-0.981334-0.341333-0.981333-0.384-0.938667-0.426667-0.896-0.469333-0.896-0.512-0.853333-0.554667-0.853333-0.597333-0.810667-0.64-0.768-0.682667-0.725333-0.725333-0.725334-0.725333-0.682666-0.768-0.64-0.810667-0.597334-0.853333-0.554666-0.853334-0.512-0.896-0.469334-0.896-0.426666-0.938666-0.384-0.981334-0.341334-0.981333-0.256-1.024-0.256-1.024-0.170666-1.066667-0.128-1.066666-0.085334-1.066667L170.666667 874.666667l0.042666-1.109334 0.085334-1.066666 0.128-1.066667 0.170666-1.066667 0.256-1.024 0.256-1.024 0.341334-0.981333 0.384-0.981333 0.426666-0.938667 0.469334-0.896 0.512-0.896 0.554666-0.853333 0.597334-0.853334 0.64-0.810666 0.682666-0.768 0.725334-0.725334 0.725333-0.725333 0.768-0.682667 0.810667-0.64 0.853333-0.597333 0.853333-0.554667 0.896-0.512 0.896-0.469333 0.938667-0.426667 0.981333-0.384 0.981334-0.341333 1.024-0.256 1.024-0.256 1.066666-0.170667 1.066667-0.128 1.066667-0.085333L192 853.333333h512.426667l1.066666 0.042667z\" fill=\"#FFB28E\" p-id=\"14275\"></path></svg>',
	        
	       // 雷雨
	       'thunder' => '<svg t=\"1592378170569\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"3213\" width=\"24px\" height=\"24px\"><path d=\"M321.536 834.048c-8.704 32.768-42.496 52.224-75.264 43.52-32.768-8.704-52.224-42.496-43.52-75.264 8.704-32.768 85.504-81.92 85.504-81.92s41.984 80.896 33.28 113.664zM757.248 906.24c-7.68 28.672-37.376 45.568-66.048 37.888-28.672-7.68-45.568-37.376-37.888-66.048 7.68-28.672 75.264-71.68 75.264-71.68s36.352 70.656 28.672 99.84z\" fill=\"#16A9D4\" p-id=\"3214\"></path><path d=\"M939.008 441.856c0-103.424-88.576-186.88-197.12-186.88s-197.12 83.968-197.12 186.88v186.88h199.68c107.52-1.536 194.56-84.48 194.56-186.88z\" fill=\"#dbdbdb\" p-id=\"3215\" data-spm-anchor-id=\"a313x.7781069.0.i7\" class=\"selected\"></path><path d=\"M814.592 432.64c0-59.392-26.624-112.64-69.12-148.992 0.512-5.632 1.024-11.264 1.024-16.896 0-108.544-88.576-196.096-197.12-196.096S351.744 158.72 351.744 266.752c0 2.048 0 3.584 0.512 5.632-24.576-12.288-52.736-18.944-81.92-18.944-103.424 0-187.392 83.968-187.392 187.392 0 101.376 80.896 183.808 181.248 187.392v0.512h361.984v-0.512c104.96-4.608 188.416-90.112 188.416-195.584z\" fill=\"#bfbfbf\" p-id=\"3216\" data-spm-anchor-id=\"a313x.7781069.0.i5\" class=\"\"></path><path d=\"M524.288 502.272l-16.896 202.24H370.176z\" fill=\"#EDA915\" p-id=\"3217\"></path><path d=\"M459.776 900.608l16.896-202.24h137.216z\" fill=\"#EDA915\" p-id=\"3218\"></path></svg>',
	       
	       // 龙卷风
	       'tornado' => '<svg t=\"1592382795457\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"10623\" width=\"24px\" height=\"24px\"><path d=\"M912 484.8C912 392.7 780.9 318 619.2 318s-292.8 74.6-292.8 166.7c0 71.9 80.1 133 192.2 156.4-41.3 17.5-68.1 45.4-68.1 77 0 53.1 75.6 96.1 168.8 96.1s168.8-43 168.8-96.1c0-31.6-26.8-59.5-68.1-77 6.7-1.4 13.4-2.8 19.9-4.4C841.4 610.6 912 552.4 912 484.8z\" fill=\"#BFDFF7\" p-id=\"10624\"></path><path d=\"M499 546.3c221.4 0 388.4-81.5 388.4-189.7S720.4 167 499 167s-388.4 81.5-388.4 189.7c0 77.2 85.3 140.6 214.5 170.7-67.5 25.5-109.7 66.2-109.7 113.8 0 57.2 60.8 104.4 153.3 127.3-25.8 15.2-42.3 36-42.3 62.4 0 55 71.9 86.1 149.5 91.3 12.5 0.8 23.1-10 23.1-23.7 0-12.4-8.8-22.9-20.1-23.6-72.3-4.4-105.1-30.6-109.4-44-8.3-25.7 65.7-55 129.5-47.4 31 0 61.2-2.3 90.1-6.9 12-1.9 19.9-14.7 17.7-27.8v-0.1c-2.1-12.6-13-20.8-24.5-18.9-26.6 4.2-54.5 6.3-83.2 6.3-137.6 0-240.4-50.1-240.4-94.8-0.1-44.9 102.7-95 240.3-95zM153.8 356.7c0-67.3 141.8-142.3 345.2-142.3s345.2 75 345.2 142.3S702.4 498.9 499 498.9 153.8 424 153.8 356.7z\" p-id=\"10625\"></path><path d=\"M143 566.2m-32.4 0a32.4 32.4 0 1 0 64.8 0 32.4 32.4 0 1 0-64.8 0Z\" fill=\"#FF8B00\" p-id=\"10626\"></path><path d=\"M833.4 134.6m-32.4 0a32.4 32.4 0 1 0 64.8 0 32.4 32.4 0 1 0-64.8 0Z\" fill=\"#FF8B00\" p-id=\"10627\"></path><path d=\"M747.1 630.9m-32.4 0a32.4 32.4 0 1 0 64.8 0 32.4 32.4 0 1 0-64.8 0Z\" fill=\"#FF8B00\" p-id=\"10628\"></path></svg>',
	       
	       // 雾
	       'fog' => '<svg t=\"1592396340693\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"19701\" width=\"26px\" height=\"26px\"><path d=\"M246.79 424.34S92.67 400.1 67.35 534.39C53.41 608.5 87.84 653.66 131.92 680.9c41 25.31 81.94 28.1 81.94 28.1h605.9s30.89-1.5 58.67-12c43.55-16.52 92.24-53.52 80-140.72-14.16-101.46-122.81-97.6-122.81-97.6s3.43-79.16-79.8-104.36c-57.28-17.27-94.39 7.4-94.39 7.4s-66.61-114.12-205.4-106.4c-182.96 10.29-209.24 169.02-209.24 169.02z\" fill=\"#78A4D6\" p-id=\"19702\"></path><path d=\"M945.79 556.48C928.52 465.42 824 470 824 470s4.83-78.62-73.79-104c-47.73-15.44-93.1 8.37-93.1 8.37s-69.4-117-199.71-107.58c-175.75 12.76-202.13 171.93-202.13 171.93S107.68 407.29 79.9 535.46c-30 139 130.75 160.78 130.75 160.78h284s62.21-4.83 87.41-32.5c34.21-37.43 36-92.35-4.29-121.84-36.36-26.6-80.55-20.7-111.55-9-12.55 4.72-13.73 6.54-15.34 6.22-1.18-0.21-4.61-3.65 2.9-9.12 9.44-6.86 20.16-13.41 38.72-17.7 62.85-14.48 122 16.95 138.25 64.89a140.63 140.63 0 0 1-4.08 102.54c-4.4 9.87-6.44 9.65-6.33 13.19 0.11 3.86 4.83 3.32 4.83 3.32H826.3c0.11-0.11 143.7-11.91 119.49-139.76z\" fill=\"#C0E7FA\" p-id=\"19703\"></path><path d=\"M494.56 696.13s62.21-4.83 87.41-32.5c23-25.1 31.21-57.92 22.09-85.81a74.09 74.09 0 0 0-16.2-26.92 14.64 14.64 0 0 0-1.82-1.82c-0.75-0.75-1.5-1.5-2.36-2.25-1.93-1.72-4-3.43-6.11-5a95.79 95.79 0 0 0-43.87-17.7h-0.64c49.55 30.25 32.93 82.16-0.64 100.61-26.49 14.59-84.3 17.27-102.54 13.51-109.29-22.09-105.65-80.12-103.07-102.75 0.64-6.11 4.4-8.15 1.61-9.65s-11 4.29-14.59 7c-40.22 30.25-31.1 72-31.1 72s-60.81 19.95-122.59-12.33C103.28 562.49 98 501.89 97.71 490.63l-1.61 2.57c-1.5 2.68-3.86 6.65-6.44 12.23-1 2.15-1.93 4.4-2.9 6.65-0.32 0.75-0.54 1.5-0.86 2.25-1 2.57-1.82 5.26-2.68 7.94-0.32 1-0.64 2-1 3.11-0.75 2.57-1.39 5.15-1.93 7.83-1 4.08-1.82 8.37-2.57 13.19-0.64 3.86-1 7.51-1.29 11.15 0 0.43-0.11 0.75-0.11 1.18-0.21 3-0.32 5.9-0.32 8.8v3.54c0 2 0.11 4.08 0.21 6.11 6.86 101.68 134.18 118.95 134.18 118.95h284.17zM948.47 579.44a161.72 161.72 0 0 0-2.68-22.63c-0.32-1.82-0.75-3.54-1.07-5.15l-0.32-1.61c-0.21-1.07-0.54-2-0.75-3.11-6.11-22.2-17.8-38-31.53-49.23a131.37 131.37 0 0 0-11.26-8c-0.32-0.21 32.5 95.78-84.3 128.71-41.1 11.58-137.2 13.9-179.24-15.42A140.72 140.72 0 0 1 627 679.29c-4.4 9.87-6.44 9.65-6.33 13.19 0.11 3.86 4.83 3.32 4.83 3.32h201.12c-0.21 0.33 125.71-10.07 121.85-116.36z\" fill=\"#9CDCF8\" p-id=\"19704\"></path><path d=\"M430.95 342.19a81.3 52.45 0 1 0 162.6 0 81.3 52.45 0 1 0-162.6 0Z\" fill=\"#FFFFFF\" p-id=\"19705\"></path><path d=\"M909.61 906a22.26 22.26 0 0 1-22.26 22.27h-748.7A22.26 22.26 0 0 1 116.39 906a22.26 22.26 0 0 1 22.26-22.26h748.7A22.26 22.26 0 0 1 909.61 906zM343.9 765.09a22.26 22.26 0 0 1-22.26 22.27h-183a22.26 22.26 0 0 1-22.26-22.27 22.26 22.26 0 0 1 22.26-22.26h183a22.26 22.26 0 0 1 22.26 22.26zM887.35 742.83H421.51a22.26 22.26 0 0 0 0 44.53h465.84a22.26 22.26 0 0 0 0-44.53z\" fill=\"#9CDCF8\" p-id=\"19706\"></path></svg>',
	       
	       // 热
	       'heat' => '<svg t=\"1592396669112\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"11765\" width=\"24px\" height=\"24px\"><path d=\"M421.4 960S34.1 872.6 207.2 438.8c0 0 39.3 48.2 33.9 71.4 0 0 30.8-109.5 97.3-174.9C395.5 279.1 453.5 121.1 400 64c0 0 265 57.1 294.5 342.7 0 0 33.9-91 103.5-100 0 0-21.4 50 0 125 0 0 219.5 385.5-158.8 515.8 0 0 113.4-132.1-127.1-358.8 0 0-56.7 121.4-90.6 164.2-0.1 0.1-94.7 108.9-0.1 207.1z\" fill=\"#d4237a\" p-id=\"11766\" data-spm-anchor-id=\"a313x.7781069.0.i12\" class=\"selected\"></path></svg>',
	       
	       // 冷
	       'cold' => '<svg t=\"1592396946535\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"25729\" width=\"22px\" height=\"22px\"><path d=\"M505.667 960.947c-15.882 0-28.771-12.88-28.771-28.771V93.254c0-15.892 12.89-28.771 28.771-28.771 15.892 0 28.781 12.879 28.781 28.771v838.922c0 15.891-12.889 28.771-28.781 28.771z\" fill=\"#5CC2E4\" p-id=\"25730\"></path><path d=\"M573.087 894.189c-9.205 0-18.232-4.397-23.804-12.576l-43.616-64.02-43.617 64.02c-8.948 13.139-26.865 16.518-39.98 7.588-13.137-8.949-16.534-26.855-7.586-39.983l67.4-98.925a28.76 28.76 0 0 1 47.555 0l67.402 98.925c8.938 13.128 5.55 31.034-7.578 39.983a28.639 28.639 0 0 1-16.176 4.988zM505.667 287.433a28.778 28.778 0 0 1-23.783-12.573l-67.4-98.926c-8.948-13.127-5.551-31.023 7.586-39.982 13.115-8.938 31.013-5.561 39.98 7.586l43.617 64.012 43.616-64.012c8.949-13.155 26.847-16.533 39.98-7.586 13.128 8.959 16.516 26.855 7.578 39.982l-67.402 98.926a28.783 28.783 0 0 1-23.772 12.573zM933.888 550.257H94.968c-15.883 0-28.762-12.89-28.762-28.771 0-15.892 12.879-28.781 28.762-28.781h838.92c15.883 0 28.772 12.89 28.772 28.781 0 15.881-12.889 28.771-28.772 28.771z\" fill=\"#5CC2E4\" p-id=\"25731\"></path><path d=\"M161.755 617.646c-9.206 0-18.243-4.395-23.804-12.573-8.947-13.137-5.561-31.043 7.575-39.981l64.023-43.616-64.023-43.616c-13.136-8.948-16.522-26.855-7.575-39.981 8.938-13.146 26.845-16.524 39.972-7.576l98.937 67.391c7.862 5.364 12.563 14.261 12.563 23.783s-4.701 18.419-12.563 23.773l-98.937 67.399a28.626 28.626 0 0 1-16.168 4.997zM867.378 617.646a28.652 28.652 0 0 1-16.18-4.998l-98.925-67.399c-7.862-5.354-12.564-14.251-12.564-23.773s4.702-18.419 12.564-23.783l98.925-67.391c13.128-8.948 31.035-5.57 39.982 7.576 8.949 13.126 5.552 31.033-7.585 39.981l-64.013 43.616 64.013 43.616c13.137 8.948 16.534 26.845 7.585 39.981-5.571 8.18-14.606 12.574-23.802 12.574z\" fill=\"#5CC2E4\" p-id=\"25732\"></path><path d=\"M796.067 840.645c-7.368 0-14.718-2.803-20.347-8.424L182.517 239.008c-11.24-11.23-11.24-29.453 0-40.684 11.239-11.239 29.452-11.239 40.692 0l593.204 593.205c11.238 11.238 11.238 29.463 0 40.691-5.609 5.622-12.977 8.425-20.346 8.425z\" fill=\"#5CC2E4\" p-id=\"25733\"></path><path d=\"M701.17 841.121c-13.553 0-25.621-9.619-28.229-23.419L650.64 700.088a28.754 28.754 0 0 1 7.921-25.7 28.741 28.741 0 0 1 25.71-7.93l117.604 22.302c15.625 2.954 25.877 18.006 22.915 33.621-2.964 15.625-17.987 25.896-33.631 22.915l-76.112-14.431 14.431 76.109c2.963 15.616-7.298 30.68-22.915 33.633a28.717 28.717 0 0 1-5.393 0.514zM319.824 364.414a28.89 28.89 0 0 1-5.363-0.505l-117.613-22.301c-15.616-2.953-25.868-18.017-22.904-33.631 2.952-15.605 17.995-25.888 33.62-22.904l76.121 14.431-14.43-76.113c-2.953-15.614 7.309-30.676 22.913-33.63 15.606-2.973 30.679 7.29 33.632 22.904l22.292 117.613a28.78 28.78 0 0 1-28.268 34.136z\" fill=\"#5CC2E4\" p-id=\"25734\"></path><path d=\"M215.247 840.666a28.712 28.712 0 0 1-20.346-8.434c-11.23-11.23-11.23-29.455 0-40.684l593.224-593.205c11.24-11.229 29.452-11.229 40.694 0 11.238 11.229 11.238 29.453 0 40.693L235.594 832.232c-5.611 5.62-12.979 8.434-20.347 8.434z\" fill=\"#5CC2E4\" p-id=\"25735\"></path><path d=\"M310.145 841.109c-1.778 0-3.575-0.168-5.392-0.512-15.607-2.953-25.858-18.007-22.905-33.623l14.431-76.119-76.112 14.431c-15.625 2.962-30.677-7.292-33.641-22.905-2.952-15.615 7.31-30.678 22.915-33.629l117.613-22.294a28.757 28.757 0 0 1 25.709 7.921 28.79 28.79 0 0 1 7.921 25.7l-22.291 117.613c-2.619 13.81-14.698 23.417-28.248 23.417zM691.49 364.414a28.769 28.769 0 0 1-20.347-8.436 28.733 28.733 0 0 1-7.921-25.7l22.302-117.613c2.964-15.614 18.045-25.868 33.63-22.904 15.617 2.954 25.87 18.016 22.905 33.63l-14.43 76.113 76.121-14.431c15.605-2.993 30.667 7.289 33.632 22.904 2.952 15.614-7.3 30.678-22.917 33.631l-117.612 22.301a28.963 28.963 0 0 1-5.363 0.505z\" fill=\"#5CC2E4\" p-id=\"25736\"></path></svg>',
	       
	       // 未知
	       'unknown' => '<svg t=\"1592397061147\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"26236\" width=\"24px\" height=\"24px\"><path d=\"M480.16 764.8C483.264 688.128 576 642.88 576 608a64 64 0 1 0-128 0 32 32 0 0 1-64 0 128 128 0 1 1 256 0c0 70.72-96.544 88.448-95.84 160H544a32 32 0 1 1-63.84-3.2z m-289.6 12.96a224.096 224.096 0 0 1 38.944-418.08C255.68 227.616 372.224 128 512 128c139.808 0 256.32 99.616 282.496 231.712a224.096 224.096 0 0 1 39.04 417.984l-0.064 0.032a32 32 0 0 1-27.456-57.824A160 160 0 0 0 736 416a224 224 0 0 0-448 0 160 160 0 0 0-68.672 304.576 32 32 0 1 1-28.8 57.184zM512 832a32 32 0 1 1 0 64 32 32 0 0 1 0-64z\" fill=\"#1296db\" p-id=\"26237\"></path></svg>',
            
            ];
        
        return $arr[$name];
    }
        
    // END
}
