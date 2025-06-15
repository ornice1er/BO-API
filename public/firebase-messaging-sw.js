importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');


firebase.initializeApp({
    apiKey: 'your-api-key',
    projectId: 'your-project-id',
    messagingSenderId: 'your-sender-id',
    appId: 'your-app-id',
  });
  const messaging = firebase.messaging();
  messaging.setBackgroundMessageHandler(function(payload) {
    const title = payload.notification.title;
    const options = {
      body: payload.notification.body,
    };
    return self.registration.showNotification(title, options);
  });