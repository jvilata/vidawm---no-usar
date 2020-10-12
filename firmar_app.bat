echo PASSWORD: &&Denia2020
jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore c:\quasar\vidawm.p12 C:\quasar\vidawm\dist\cordova\android\apk\release\app-release-unsigned.apk vidawm

zipalign -v 4 C:\quasar\vidawm\dist\cordova\android\apk\release\app-release-unsigned.apk C:\quasar\vidawm\dist\cordova\android\apk\release\vida.apk