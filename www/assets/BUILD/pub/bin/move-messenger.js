const path   = require('path');
const fs     = require('fs-extra');
const uglify = require('uglify-js');

const pubDir             = path.join(__dirname, '..');
const messengerBuildDir  = path.join(pubDir, 'build', 'messenger');
const messengerVendorDir = path.join(pubDir, 'node_modules', '@deskpro', 'messenger', 'build');
const messengerStaticDir = path.join(pubDir, 'node_modules', '@deskpro', 'messenger', 'build', 'static', 'js');

if (!fs.existsSync(messengerBuildDir)) {
  fs.mkdirSync(messengerBuildDir);
}

console.log('Cleaning messenger dir');
fs.emptyDirSync(messengerBuildDir);
console.log('Moving messenger files');

console.log('Moving manifest');
const manifest = JSON.parse(fs.readFileSync(path.join(messengerVendorDir, '/asset-manifest.json')));


manifest.entrypoints.main.js = manifest.entrypoints.main.js.map(fname => fname.replace('/static/js', ''));

fs.writeFileSync(path.join(messengerBuildDir, '/asset-manifest.json'), JSON.stringify(manifest));

fs.readdir(messengerStaticDir, (err, files) => {
  const filteredFiles = files.filter(file => path.extname(file) !== '.map');
  console.log('Moving messenger js assets, ' + filteredFiles.length + ' files');
  filteredFiles.forEach(file => {
    const srcPath = path.join(messengerStaticDir, file);
    fs.copyFile(srcPath, path.join(messengerBuildDir, file), err => {
      if (err) throw err;
    });
  });
});

const messengerVendorAssetsDir = path.join(messengerVendorDir, 'assets');

console.log('Moving messenger styles');
fs.copyFile(path.join(messengerVendorAssetsDir, 'styles.css'), path.join(messengerBuildDir, 'styles.css'), err => {
  if (err) throw err;
  console.log('Successfully moved styles');
});

const messengerVendorAudioDir = path.join(messengerVendorAssetsDir, 'audio');
const messengerAudioDir = path.join(messengerBuildDir, 'audio');
console.log('Moving messenger audio assets');
fs.copy(messengerVendorAudioDir, messengerAudioDir);

const messengerVendorImgDir = path.join(messengerVendorAssetsDir, 'img');
const messengerImgDir = path.join(messengerBuildDir, 'img');
fs.copy(messengerVendorImgDir, messengerImgDir);

const loaderFilePath    = `/messenger-loader.js`;
const minLoaderFilePath = `/loader.min.js`;
const widgetBundlePath  = path.join(pubDir, 'node_modules/@deskpro/messenger-loader/dist');

console.log(`Writing messenger_loader`);

const loaderCode = fs.readFileSync(widgetBundlePath + loaderFilePath).toString();

fs.writeFileSync(messengerBuildDir + '/loader.js', loaderCode);

try {
  const loaderCodemin = uglify.minify(loaderCode, { fromString: true }).code;
  fs.writeFileSync(messengerBuildDir + minLoaderFilePath, loaderCodemin);
} catch (e) {
  console.log('Trying to minify:\n');
  console.log(loaderCode);
  console.log('\n\n');
  console.error(e);
  return;
}

console.log(`... done writing messenger_loader`);
