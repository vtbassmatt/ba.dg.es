var App = function() {
    
    var onLogin = function(uid, token) {
        // logged in
        // call login_helper.php with FB UID and token in order to get
        // the user ID
        var loginHelperUrl = "login_helper.php?uid="
                           + uid
                           + "&token="
                           + encodeURIComponent(token);
        //$('#debugout').append(loginHelperUrl);
        $.ajax({
            url: loginHelperUrl,
            dataType: 'json',
            success: function(data) {
                if(data.error) {
                    $('#errors').append("<p>Whoops, had some trouble logging in: " + data.error + "</p>");
                } else {
                    $('#main-top').text(data.name + "'s badges");
                }
            },
            error: function(jqxhr, status, err) {
                $('#errors').append("<p>Login error: " + err + " - " + status + "</p>");
            }
        });
        
        // remove the instructions
        $("#instructions").empty().append("If you haven't connected to a badge-granting site (the buttons below), that's your next step.  You may have to click each button again after you grant access.");
        
        // add a Foursquare button
        $("#networks").append("<img id=\"button_4sq\" class=\"button\" src=\"http://playfoursquare.s3.amazonaws.com/press/logo/connect-white.png\"/>");
        $("#button_4sq").click(function() {
            $.ajax({
                url: "retrieve/retrieve_4sq_badges.php",
                dataType: "json",
                success: function(data) {
                    if(data.error) {
                        $('#errors').append("<p>Whoops, had some trouble getting Foursquare badges: " + data.error + "</p>");
                    } else {
                        if(data.status && data.status === 'requireauth') {
                            top.location.href = "oauth/auth_4sq.php";
                        } else if(data.status && data.status === 'success') {
                            window.setTimeout(loadAndShowBadges, 10);
                        } else {
                            alert("Something happened: " + data);
                        }
                        // 4sq badges retrieved
                    }
                },
                error: function(jqxhr, status, err) {
                    $('#errors').append("<p>Foursquare error: (" + err + ") - " + status + "</p>");
                }
            });
        });
        
        // add all the badges you've earned
        window.setTimeout(loadAndShowBadges, 10);
    };
    
    var loadAndShowBadges = function() {
        // remove all badges
        setLoading(true);
        $("#badges").empty();
        
        $.ajax({
            url: "getbadges.php",
            dataType: "json",
            success: function(data) {
                setLoading(false);
                if(data.error) {
                    $('#errors').append("<p>Problem loading badges: " + data.error + "</p>");
                } else {
                    if(data.results.length > 0) {
                        $("#instructions").empty();
                    }
                    for(var i = 0; i < data.results.length; i++) {
                        $("#badges").append('<img class="badge-icon" src="'
                            + data.results[i]['url']  + '" alt="'
                            + data.results[i]['name'] + '" />');
                    }
                }
            },
            error: function(jqxhr, status, err) {
                setLoading(false);
                $('#errors').append("<p>Badge load error: (" + err + ") - " + status + "</p>");
            }
        });
    };
    
    var setLoading = function(loadState) {
        if(loadState) {
            $("#loading").css("display", "block");
        } else {
            $("#loading").css("display", "none");
        }
    };
    
    // expose our API
    return {
        onLogin: onLogin,
        loadAndShowBadges : loadAndShowBadges,
        setLoading : setLoading
    };

}();