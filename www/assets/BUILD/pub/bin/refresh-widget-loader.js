const path    = require('path');
const uglify  = require('uglify-js');
const babel   = require('babel-core');
const fs      = require('fs');

const pubDir         = path.join(__dirname, '..');

function refreshWidgetLoader(loaderFilename) {
  const loaderFilePath    = `/${loaderFilename}.js`;
  const minLoaderFilePath = `/${loaderFilename}.min.js`;
  const widgetBundlePath  = path.join(pubDir, 'src/DeskPRO/Bundle/WidgetBundle');
  const utilCode          = fs.readFileSync(`${widgetBundlePath}/deskpro_loader_util.js`).toString();
  const buildDir          = path.join(pubDir, 'build');

  if (!fs.existsSync(buildDir)) {
    fs.mkdirSync(buildDir);
  }
  console.log(`Writing ${loaderFilePath}`);

  const loaderCode = fs.readFileSync(widgetBundlePath + loaderFilePath).toString()
    .replace('// #include deskpro_loader_util.js', utilCode);

  const transformedLoaderCode = babel.transform(
    loaderCode,
    { presets: ['es2015', 'react', 'stage-0'] }
  ).code;
  fs.writeFileSync(buildDir + loaderFilePath, transformedLoaderCode);

  try {
    const loaderCodemin = uglify.minify(transformedLoaderCode, { fromString: true }).code;
    fs.writeFileSync(path.join(pubDir, 'build') + minLoaderFilePath, loaderCodemin);
  } catch (e) {
    console.log('Trying to minify:\n');
    console.log(transformedLoaderCode);
    console.log('\n\n');
    console.error(e);
    return;
  }

  console.log(`... done writing ${loaderFilename}`);
}

function refreshMessengerLoader()
{  const loaderFilePath    = `/messenger-loader.js`;
  const minLoaderFilePath = `/loader.min.js`;
  const widgetBundlePath  = path.join(pubDir, 'node_modules/@deskpro/messenger-loader/dist');
  const buildDir          = path.join(pubDir, 'build/messenger');

  if (!fs.existsSync(buildDir)) {
    fs.mkdirSync(buildDir);
  }
  console.log(`Writing messenger_loader`);

  const loaderCode = fs.readFileSync(widgetBundlePath + loaderFilePath).toString();

  fs.writeFileSync(buildDir + '/loader.js', loaderCode);

  try {
    const loaderCodemin = uglify.minify(loaderCode, { fromString: true }).code;
    fs.writeFileSync(buildDir + minLoaderFilePath, loaderCodemin);
  } catch (e) {
    console.log('Trying to minify:\n');
    console.log(loaderCode);
    console.log('\n\n');
    console.error(e);
    return;
  }

  console.log(`... done writing messenger_loader`);
}

const widgets = [
  'widget_loader',
  'hit_recorder',
  'embed_loader'
];

for (widget of widgets) {
  refreshWidgetLoader(widget);
}
refreshMessengerLoader();