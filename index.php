<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>ba.dg.es</title>
    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.min.js" type="text/javascript"></script>
    <script src="js/app.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="badges.css" />
  </head>
  <body>
    <h1 id="title">ba.dg.es</h1>
    <div id="loading"><img id="pleasewait" src="img/18-1.gif"/></div>
    <div id="main">
        <div id="main-top">This will be easy</div>
        <div id="badges"></div>
        <div id="instructions">Click the Facebook Login button in the upper right to get started.</div>
    </div>

    <div id="networks"></div>
    <div id="fblogin"><fb:login-button autologoutlink="true"></fb:login-button></div>
    
    <div id="fb-root"></div>
    <div id="debugout"></div>
    <div id="errors"></div>
    <script>
      
      window.fbAsyncInit = function() {
        FB.init({appId: '244004622279297', status: true, cookie: true,
                 xfbml: true});
        
        // handle logins when the user clicks the Login button
        FB.Event.subscribe('auth.login', function(response) {
            if(response.session) {
                App.onLogin(response.session.uid, response.session.access_token);
            } else {
                // not logged in
            }
        });
        
        // handle logins when the user has already allowed the app
        FB.getLoginStatus(function(response) {
            if(response.session) {
                App.onLogin(response.session.uid, response.session.access_token);
            } else {
                // not logged in
            }
        });
      };
      (function() {
        var e = document.createElement('script');
        e.type = 'text/javascript';
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
    
    <div id="footer">
        <span id="likebtn"><fb:like></fb:like></span>
        <span id="footerright">
            Created by and &copy;2011 <a href="http://github.com/vtbassmatt">Matt Cooper</a> |
            <a href="wtf.html">wtf</a> |
            <a href="about.html">about</a> |
            <a href="privacy.html">privacy</a>
        </span>
    </div>
  </body>
</html>