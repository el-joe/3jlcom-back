
const firebaseConfig = {
    apiKey: "AIzaSyAVbVr22_RrSflA83ASIdf6QBasd4l5HMs",
    authDomain: "jlcom-930ba.firebaseapp.com",
    projectId: "jlcom-930ba",
    storageBucket: "jlcom-930ba.appspot.com",
    messagingSenderId: "637339761601",
    appId: "1:637339761601:web:0e99b0596dcab08d4ccd40",
    measurementId: "G-QCPW8FSL4M"
};

if (!firebase.apps.length) {

    firebase.initializeApp(firebaseConfig);
}

const messaging = firebase.messaging();
messaging.requestPermission()
    .then(function () {
        console.log('Notification permission granted.');

        getRegToken();
    })
    .catch(function (err) {
        console.log('Unable to get permission to notify.', err);
        /*Swal.fire({
            title: 'Allow Notification Permission!',
            icon: 'error',
            showConfirmButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true
        })*/
    });

function getRegToken(argument) {
    messaging.getToken()
        .then(function (currentToken) {

            saveToken(currentToken);
        })
        .catch(function (err) {
            console.log('An error occurred while retrieving token. ', err);

        });
}


function saveToken(currentToken) {

    console.log(currentToken);
    $.ajax({
        url: "updateFCMID",
        method: 'get',
        data: {
            token: currentToken,
            id: 1
        }
    }).done(function (result) {

    });
}

messaging.onMessage(function (payload) {
    notificationTitle = payload.data.title;
    notificationOptions = {
        body: payload.data.body,
        icon: payload.data.icon,
        // image:  payload.data.image,
        data: {
            time: new Date(Date.now()).toString(),

        }
    };
    var notification = new Notification(notificationTitle, notificationOptions);


});




