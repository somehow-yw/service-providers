<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>找冻品网-冻参谋管理后台登录</title>
</head>
<link rel="stylesheet" type="text/css"
      href="/Public/pc/dcanmou-client/icon/iconfont.css">
<style type="text/css">
    *{padding: 0;margin: 0;}
    button {border: none;outline: none}
    body{font-family: '微软雅黑';background: url(/Public/pc/dcanmou-client/img/loginback.png) no-repeat;
        }
    .login{
        width: 800px;
        height: 500px;
        background: #F4FAFF;
        position:absolute;
        left:50%;
        top:50%;
        margin-left: -400px;
        margin-top:-250px;
    }
    .title {
        font-size: 27px;
        font-weight: bold;
        height: 120px;
        line-height: 120px;
        border-bottom: 2px solid #45C8DC;
        background: url(/Public/pc/dcanmou-client/img/logo.png) no-repeat 29px 32px;
    }
    .title div {
        color: #4D4D4D;
        padding-left: 26px;
        display: inline-block;
        -webkit-box-reflect: below -90px -webkit-linear-gradient(top, transparent, transparent 45%, rgba(0, 0, 0, 0.296875))
    }
    .title span {border-left: 2px solid #CDCDCD;margin-left: 210px;}
    .loginpanel {
        margin-top: 50px;
        font-size: 39px;
        color: #333333;
    }
    .loginpanel p {margin-bottom: 60px; letter-spacing: 10px; text-align: center;}
    .loginpanel span {color: #57CBDE;}
    .loginpanel div {border-bottom: 1px solid #CCCCCC;width: 350px;margin-left: 250px;}
    .loginpanel input {
        border: none;
        outline:medium;
        font-size: 23px;
        padding-left: 35px;
        background: #F4FAFF;
    }
    .loginpanel .icon-password {color: #45C8DC;font-size: 26px;}
     .btn {text-align: center;margin-left: 250px; color: #FFFFFF;-webkit-box-shadow:0 0 40px #0CC;  
  -moz-box-shadow:0 0 40px #45C8DC;  
  box-shadow:0 0 40px #45C8DC;  background: #45C8DC;height: 70px;width: 300px;border-radius: 35px;margin-top: 30px;font-size: 24px; letter-spacing: 7px;}
     .withoutcode #help {color: #5CCDDF;text-align: right;font-size: 22px;letter-spacing: 2px;margin-right: 35px;background: #F4FAFF;float: right;}
    #mask {display: none;position: absolute;top:50%;left: 50%;margin-left: -300px;margin-top: -70px ;width: 600px;height: 150px;border-radius: 8px;color: #DDDDDD;background-color: rgba(50, 50, 50, 0.8)}
    #mask p {line-height: 150px;font-size: 25px;letter-spacing: 6px;text-align: center}
    .fail {position: absolute;top:50%;left: 50%;width: 600px;height: 150px;margin-left: -300px;margin-top: -70px ;background-color: rgba(50, 50, 50, 0.5);border-radius: 8px;display: none;}
    .fail p {line-height: 150px;font-size: 25px;letter-spacing: 6px;text-align: center;color: #DDDDDD}
</style>
<body>
<div class="login">
    <div class="title"><span></span>
        <div>服务商后台管理系统</div>
    </div>
    <form class="loginpanel" method="POST">
        <p>欢迎<span>登录!</span></p>
        <div>
            <i class="iconfont icon-password"></i><input
                    placeholder="请输入您的登录码" name="token"/>
            {{csrf_field()}}
        </div>
    </form>
        <button class="btn" id="loginIn">登录</button>
        <p class="withoutcode">
            <button id="help">没有登录码?</button>
        </p>
    <div id="mask">
        <p>请在微信端点击获取登录码</p>
    </div>
    <div class="fail" id="fail">
        <p>登陆凭证错误或失效，请重试</p>
    </div>
</div>
</body>
</html>
<script src="http://libs.baidu.com/jquery/2.1.4/jquery.min.js"></script>
<script>
    var help = document.getElementById('help');
    var mask = document.getElementById('mask');
    var loginIn = document.getElementById('loginIn');
    var fail = document.getElementById('fail');
    loginIn.onclick = function () {
        var token = document.getElementsByName('token')[0].value;
        var _token = document.getElementsByName('_token')[0].value;
        console.log(token, _token);
        $.ajax({
            url: '/user/login',
            type: 'POST',
            data: {token: token, _token: _token},
            dataType: 'json',
            success: function (response) {
                if(response.code == 0 ) {
                    console.log('登录成功');
                    window.location.href = '/';
                }
                else {
                     console.log('登录失败');
                     fail.style.display = "block";
                }
                
            },
        });
    };
    help.onclick = function () {
        mask.style.display = 'block';
        return true;
    };
    mask.onclick = function () {
        mask.style.display = 'none';
        return true;
    };
    fail.onclick =function () {
        fail.style.display = 'none';
        return true;
    }
</script>