const path    = require('path');
const uglify  = require('uglify-js');
const sass    = require('node-sass');
const babel   = require('babel-core');
const fs      = require('fs');

const pubDir         = path.join(__dirname, '..');
const bowerDir       = path.resolve(pubDir, './bower_components');
const nodeModulesDir = path.resolve(pubDir, './node_modules');

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

  // Refresh precompiled-fontawesome
  console.log('Writing precompiled-fontawesome.css:');
  const faInPath  = `${pubDir}/src/DeskPRO/Bundle/PortalBundle/Resources/style/precompiled-fontawesome.scss`;
  const faOutPath = `${pubDir}/src/DeskPRO/Bundle/PortalBundle/Resources/style/precompiled-fontawesome.css`;
  const faResult  = sass.renderSync({
    file:         faInPath,
    outFile:      faOutPath,
    includePaths: [bowerDir, nodeModulesDir]
  });
  fs.writeFileSync(faOutPath, faResult.css);
  console.log(`... done writing ${faOutPath}`);
}

const widgets = [
  'widget_loader',
  'hit_recorder',
  'embed_loader'
];

for (widget of widgets) {
  refreshWidgetLoader(widget);
}