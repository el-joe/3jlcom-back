importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
 importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');
 const firebaseConfig = {apiKey:'AIzaSyAVbVr22_RrSflA83ASIdf6QBasd4l5HMs',
authDomain:'jlcom-930ba.firebaseapp.com',
projectId:'jlcom-930ba',
storageBucket:'jlcom-930ba.appspot.com',
messagingSenderId:'637339761601',
appId:'1:637339761601:web:0e99b0596dcab08d4ccd40',
measurementId:'G-QCPW8FSL4M',
 };
if (!firebase.apps.length) {
 firebase.initializeApp(firebaseConfig);
 }
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
console.log(payload);
 var title = payload.data.title;
var options = {
body: payload.data.body,
icon: payload.data.icon,
data: {
 time: new Date(Date.now()).toString(),
 click_action: payload.data.click_action
 }
};
return self.registration.showNotification(title, options);
 });
self.addEventListener('notificationclick', function(event) {
 var action_click = event.notification.data.click_action;
event.notification.close();
event.waitUntil(
clients.openWindow(action_click)
 );
});