
/* JVILATA. 10-4-2020.
  Aquí voy a ir poniendo todas las funciones js que necesite utilizar para los temas
  específicos de dispositos moviles, cordova.
*/

// guarda un stream binario en un archivo local del dispositivo y lo visualiza
function openBlobFile (fileName, blob, dataType) {
  var folder = cordova.file.externalRootDirectory + 'Download' // carpeta Download
  window.resolveLocalFileSystemURL(folder, function (dirEntry) {
    // console.log('file system open: ' + dirEntry.name)
    createFile(dirEntry, fileName, blob, dataType)
  }, onErrorLoadFs)
}
function createFile (dirEntry, fileName, blob, dataType) {
  // Creates a new file
  dirEntry.getFile(fileName, { create: true, exclusive: false }, function (fileEntry) {
    writeFile(fileEntry, blob, dataType)
  }, onErrorCreateFile)
}
function writeFile (fileEntry, dataObj, dataType) {
  // Create a FileWriter object for our FileEntry
  fileEntry.createWriter(function (fileWriter) {
    fileWriter.onwriteend = function () {
      // console.log('Successful file write...', fileEntry.toURL())
      cordova.plugins.fileOpener2.open(fileEntry.toURL(), dataType,
        {
          error: function (e) {
            console.log('Error status: ' + e.status + ' - Error message: ' + e.message)
          },
          success: function () {
            console.log('file opened successfully')
          }
        })
    }

    fileWriter.onerror = function (error) {
      console.log('Failed file write: ' + error)
    }
    fileWriter.write(dataObj)
  })
}
function onErrorLoadFs (error) {
  console.log(error)
}
function onErrorCreateFile (error) {
  console.log(error)
}

export { openBlobFile }
