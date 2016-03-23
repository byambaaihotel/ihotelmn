var username; //хэрэглэгчийн нэрийг хадаглах хувьсагч
function fb_login(){
    FB.login(function(response) {
        checkLoginState();
        if (response.status === 'connected') {
            sign_in(response.authResponse.userID);// fb ээс хэрэглэгчийн дугаарыг аваад нэвтрэх фунцыг дуудах.
        } else if (response.status === 'not_authorized') {
        } else {
            console.log("not internet");
        }
    });
}
function fb_logout(){
    FB.logout(function(response) {
    });
}
function fb_sign_up(){
    FB.login(function(response) {
        checkLoginState();
        if (response.status === 'connected') {
            sign_up(response.authResponse.userID);// Бүртгүүлэх функц
        } else if (response.status === 'not_authorized') {
        } else {
        }
    },{scope: 'email,user_likes'});
}
function sign_up() {//хэрэглэгчийн ID болон нэрээр нь бүртгэх функц
    FB.api('/me?fields=email,id,name', function(response) {
        var username = response.name;
        if (response.email!=null) {
            var $this = $("#fblogin");
            $this.after('<div id="loading"></div>');
            $this.addClass('disabled');
            $.post("user.php", {
                email: response.email,
                username: response.name,
                password: response.id,
                g_recaptcha_response: $('#recaptcha1').val(),
                facebook: 1 
            }, function (result) {
                if(result==1){
                    $('#loading').remove();
                    $('#login-success').fadeIn(500);
                    $('#login-success p').text('Бүртгэл амжилттай боллоо. Та и-мэйл рүүгээ орж бүртгэлээ идэвхижүүлнэ үү!');
                }
                else if(result == 2020){
                    $('#loading').remove();
                    $this.removeClass('disabled');
                    $('#login-alert').fadeIn(500);
                    $('#login-alert p').text('Бүртгэлтэй имэйл хаяг байна!');
                }
                else{
                    $('#loading').remove();
                    $this.removeClass('disabled');
                    $('#login-alert').fadeIn(500);
                    $('#login-alert p').text('Та өмнө нь бүртгэгдсэн эсвэл бүртгэлийн мэдээллээ буруу бөглөсөн байна!');
                }
            });
        }
        else{
            $('#loading').remove();
            $this.removeClass('disabled');
            $('#login-alert').fadeIn(500);
            $('#login-alert p').text('Таны фэйсбүүк хаяг заавал имэйл хаягтай байх ёстой!');
        }
    });
}
function sign_in(social_id) {
    FB.api('/me?fields=email,id,name', function(response) { //хэрэглэгчийн нэр болон ID ийгаар нэвтрэх функц
        $('.alert').hide(0);
        var $this = $("#fblogin");
        $this.after('<div id="loading"></div>');
        $this.addClass('disabled');
        $.post("login.php", {
            email: response.email,
            password: response.id,
            facebook: 1,
            g_recaptcha_response: $('#recaptcha1').val()
        },
        function (result) {
            if(result==1){
                window.location.replace("index.php");
            }
            else{
               sign_up(); 
            }
        });
    });
}
function statusChangeCallback(response) { 
    if (response.status === 'connected') {
    } else if (response.status === 'not_authorized') {
    } else {
    }
}

function checkLoginState() {
    FB.getLoginStatus(function(response) {
        statusChangeCallback(response);
    });
}

window.fbAsyncInit = function() { //developers.facebook.com -д бүртгэлтэй эсэхийг шалгах
    FB.init({                     // шалгаж буй хэсэг.
        appId      : '427522467457084',
    cookie     : true,   
    xfbml      : true, 
    version    : 'v2.5' 
    });

    FB.getLoginStatus(function(response) {
        statusChangeCallback(response);
    });

};

(function(d, s, id) { //интернетэд холбогдсон эсэхийг шалгах.
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
    console.log(fjs.parentNode.insertBefore(js, fjs));
}(document, 'script', 'facebook-jssdk'));

