var msalConfig = {
    auth: {

        // Test
        clientId: '3d38f6a9-eb97-4d28-80a8-7b8937333954', //This is your client Id
        authority: 'https://login.microsoftonline.com/e9e12402-b3ab-458f-a106-d7b5007b75fc', //This is your Tenant Id
        redirectUri: 'http://localhost:8082/solarflo/login.php' // Redirect URI

        // Live
        // clientId: '44424624-e29c-463e-88df-62da6796451e', // This is your client ID
        // authority: 'https://login.microsoftonline.com/e9e12402-b3ab-458f-a106-d7b5007b75fc', // This is your Tenant Id
        // redirectUri: 'https://solarcalc.davisandshirtliff.com/login.php' // Redirect URI
        
    },
    cache: {
        cacheLocation: "localStorage",
        storeAuthStateInCookie: true
    }
};

var graphConfig = {
    graphMeEndpoint: "https://graph.microsoft.com/v1.0/me"
};

var requestObj = {
    scopes: ["user.read"]
};

var myMSALObj = new Msal.UserAgentApplication(msalConfig);
myMSALObj.handleRedirectCallback(authRedirectCallBack);

function signIn() {
    myMSALObj.loginPopup(requestObj).then(function (loginResponse) {
        //Successful login
        showWelcomeMessage();
        //Call MS Graph using the token in the response
        acquireTokenPopupAndCallMSGraph();
    }).catch(function (error) {
        //Please check the console for errors
        console.log(error);
    });
}

function signOut() {
    myMSALObj.logout();
}

function acquireTokenPopupAndCallMSGraph() {
    //Always start with acquireTokenSilent to obtain a token in the signed in user from cache
    myMSALObj.acquireTokenSilent(requestObj).then(function (tokenResponse) {
        callMSGraph(graphConfig.graphMeEndpoint, tokenResponse.accessToken, graphAPICallback);
    }).catch(function (error) {
        console.log(error);
        // Upon acquireTokenSilent failure (due to consent or interaction or login required ONLY)
        // Call acquireTokenPopup(popup window) 
        if (requiresInteraction(error.errorCode)) {
            myMSALObj.acquireTokenPopup(requestObj).then(function (tokenResponse) {
                callMSGraph(graphConfig.graphMeEndpoint, tokenResponse.accessToken, graphAPICallback);
            }).catch(function (error) {
                console.log(error);
            });
        }
    });
}

function callMSGraph(theUrl, accessToken, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200)
            callback(JSON.parse(this.responseText));
    }
    xmlHttp.open("GET", theUrl, true); // true for asynchronous
    xmlHttp.setRequestHeader('Authorization', 'Bearer ' + accessToken);
    xmlHttp.send();
}

function graphAPICallback(data) {

    // Check if user is already in the system
    $.ajax({
        url: 'data.php?action=msregister',
        data: data,
        method: 'POST',
        dataType: 'JSON',
        beforeSend: function(){
            $('.login-btn').html('SIGNING / LOGGING IN <i class="fas fa-cog fa-spin"></i>');
        },
        success: function(result, status, xhr){
            // console.log(result);
            // console.log(data);
            $('.login-form input[name=\"email\"]').prop('value', data.userPrincipalName);
            $('.login-form input[name=\"password\"]').prop('value', data.id);
            $('.login-form .login-btn').trigger('click');
        },
        complete : function(xhr, status){
          // console.log(xhr);
        },
        error : function(status){
          // console.log(status);
        }
    });

}

function showWelcomeMessage() {
    // console.log(myMSALObj.getAccount());

    // var divWelcome = document.getElementById('WelcomeMessage');
    // divWelcome.innerHTML = "Welcome " + myMSALObj.getAccount().userName + " to Microsoft Graph API";
    // var loginbutton = document.getElementById('SignIn');
    // loginbutton.innerHTML = 'Sign Out';
    // loginbutton.setAttribute('onclick', 'signOut();');

}

//This function can be removed if you do not need to support IE
function acquireTokenRedirectAndCallMSGraph() {
    //Always start with acquireTokenSilent to obtain a token in the signed in user from cache
    myMSALObj.acquireTokenSilent(requestObj).then(function (tokenResponse) {
        callMSGraph(graphConfig.graphMeEndpoint, tokenResponse.accessToken, graphAPICallback);
    }).catch(function (error) {
        console.log(error);
        // Upon acquireTokenSilent failure (due to consent or interaction or login required ONLY)
        // Call acquireTokenRedirect
        if (requiresInteraction(error.errorCode)) {
            myMSALObj.acquireTokenRedirect(requestObj);
        }
    });
}

function authRedirectCallBack(error, response) {
    if (error) {
        console.log(error);
    } else {
        if (response.tokenType === "access_token") {
            callMSGraph(graphConfig.graphMeEndpoint, response.accessToken, graphAPICallback);
        } else {
            console.log("token type is:" + response.tokenType);
        }
    }
}

function requiresInteraction(errorCode) {
    if (!errorCode || !errorCode.length) {
        return false;
    }
    return errorCode === "consent_required" ||
        errorCode === "interaction_required" ||
        errorCode === "login_required";
}

// Browser check variables
var ua = window.navigator.userAgent;
var msie = ua.indexOf('MSIE ');
var msie11 = ua.indexOf('Trident/');
var msedge = ua.indexOf('Edge/');
var isIE = msie > 0 || msie11 > 0;
var isEdge = msedge > 0;

//If you support IE, our recommendation is that you sign-in using Redirect APIs
//If you as a developer are testing using Edge InPrivate mode, please add "isEdge" to the if check

// can change this to default an experience outside browser use
var loginType = isIE ? "REDIRECT" : "POPUP";

// runs on page load, change config to try different login types to see what is best for your application
if (loginType === 'POPUP') {
    if (myMSALObj.getAccount()) {// avoid duplicate code execution on page load in case of iframe and popup window.
        showWelcomeMessage();
        acquireTokenPopupAndCallMSGraph();
    }
}
else if (loginType === 'REDIRECT') {
    // document.getElementById("SignIn").onclick = function () {
    $('.signin').on('click', function(){
        myMSALObj.loginRedirect(requestObj);
    });        
    // };

    if (myMSALObj.getAccount() && !myMSALObj.isCallback(window.location.hash)) {// avoid duplicate code execution on page load in case of iframe and popup window.
        showWelcomeMessage();
        acquireTokenRedirectAndCallMSGraph();
    }
} else {
    console.error('Please set a valid login type');
}