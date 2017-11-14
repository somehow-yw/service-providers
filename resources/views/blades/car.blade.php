﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name='viewport' content='width=device-width,initial-scale=1.0,user-scalable=no'>
    <meta name="renderer" content="webkit">
	<meta name="x5-fullscreen" content="true">
	<meta name="full-screen" content="yes">
	<title>进货单</title>
	<link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
	<script type="text/javascript">
		// 页面路由
		//location.href = '#{{$page_route}}';
	</script>
</head>
<body>
	<div id="app">
		<div style="text-align: center;margin-top: 100px;" onclick="location.reload()">
            <img src="http://img.idongpin.com/Public/service-provider/images/entry.jpg">
            <div id="loadInfo" style="margin-top: 20px; color: #b2b2b2; font-size: 15px;">
                资源加载中...
            </div>
        </div>
	</div>
	<div id="picker_container"></div>
</body>
<script type="text/javascript">
	var version = new Date().getTime(),CONNECT_TIME = 3000,
			staticArr = [{name: 'Public/service-provider/we.js', time: 'Public/service-provider/car/bundle.js'}];
	var	brWidth = document.documentElement.clientWidth,
			brHeight = document.documentElement.clientHeight,
//	brWidth = brWidth>640?640:brWidth;
			size=brWidth/320*16;
	document.getElementsByTagName("html")[0].style.fontSize=size+"px";
	document.getElementsByTagName("html")[0].style.height=brHeight+"px";
	function setCookie(name,value) {
		var exp = new Date();
		exp.setTime(exp.getTime() + 2*60*1000);
		document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	}

	function getCookie(name) {
		var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr=document.cookie.match(reg))
			return unescape(arr[2]);
		else
			return null;
	}
	(function() {
		function staticChange(domain) {
			var v = version || '201705101407';
			for(var i = 0 ; i < staticArr.length ; i++) {
				var script = document.createElement('script');
				script.type = 'application/javascript';
				script.src = domain + staticArr[i].name + '?v='+v;
				document.body.appendChild(script);
				if(staticArr[i].time) {
					var time = staticArr[i].time;
					script.onload = function() {
						var scriptTime = document.createElement('script');
						scriptTime.type = 'application/javascript';
						scriptTime.src = domain + time + '?v='+v;
						document.body.appendChild(scriptTime);
					}
				}
			}
		}

		var domainUrl = '',
				localhost = location.origin + '/',
				imgSuccess = null,
				static1 = 'http://img.idongpin.com/',
				static2 = 'http://cdn2.img.idongpin.com/',
				imgTimeout = false,
				timeout = null;

		function createImgConnect(state) {
			imgError = function() {  //图片没加载成功，启用备用cdn;
				if(!timeout) return;
				clearInterval(timeout);
				timeout = null;
				imgTimeout = true;
				setCookie('domain', static2);
				if(state == 1) {
					staticChange(static2);
				}else if(state == 2) {
					staticChange(localhost);
				}
			};

			imgSuccess = function() {
				clearInterval(timeout);
				imgTimeout = true;
				setCookie('domain', static1);
				if(state == 1) {
					staticChange(static1);
				}else if(state == 2) {
					staticChange(localhost);
				}
			};

			var testImgDom = document.createElement('img');
			testImgDom.src = static1 + 'Public/images/test-connect.png';
			testImgDom.width = 0;
			testImgDom.height = 0;
			testImgDom.onerror = imgError;
			testImgDom.onload = imgSuccess;
			document.body.appendChild(testImgDom);
			timeout = setInterval(function() {
				if(!imgTimeout) {
					imgError();
				}
			}, CONNECT_TIME);
			//更新cookie;
		}
		if(localhost.indexOf('test.') != -1) {
			if(getCookie('domain')) {
				staticChange(localhost);
			}else {
				createImgConnect(2);
			}
		}else if(location.host.indexOf('192.168.') != -1) {
			setCookie('domain', localhost);
			staticChange(localhost);
		}else {
			if(getCookie('domain')) {
				domainUrl = getCookie('domain');
				staticChange(domainUrl);
			}else {
				createImgConnect(1);
			}
		}
	})();
</script>
</html>
