const express               = require('express');
const https                 = require('https');
const fs                    = require('fs');
const webpack               = require('webpack');
const cors                  = require('cors');
const webpackDevMiddleware  = require('webpack-dev-middleware');
const webpackHotMiddleware  = require('webpack-hot-middleware');
const config   = require('../webpack.config');

const app      = express();
const compiler = webpack(config);

const assetServerHostname = process.env.ASSET_SERVER_HOSTNAME || 'localhost';
const assetServerPort = process.env.ASSET_SERVER_PORT || 9666;

const httpsEnabled = process.env.HTTPS || false;

app.use(webpackDevMiddleware(compiler, {
  publicPath:         config.output.publicPath,
  hot:                true,
  historyApiFallback: true,
  headers: { "Access-Control-Allow-Origin": "*" },
  stats:              {
    colors:       true,
    chunks:       true,
    source:       false,
    chunkOrigins: false,
    reasons:      false,
    cached:       false,
    hash:         false,
    assets:       false,
    version:      false
  }
}));

app.use(webpackHotMiddleware(compiler));
app.use(cors());
let server;
if (httpsEnabled) {
  const privateKey  = fs.readFileSync('sslcert/server.key', 'utf8');
  const certificate = fs.readFileSync('sslcert/server.cert', 'utf8');
  const credentials = {key: privateKey, cert: certificate};
  server = https.createServer(credentials, app);
} else {
  server = app;
}
server.listen(assetServerPort, '0.0.0.0', (err) => {
  if (err) {
    throw new gutil.PluginError('webpack-dev-server', err);
  }

  console.log('[webpack-dev-server]', `http${httpsEnabled ? 's' : ''}://${assetServerHostname}:${assetServerPort}/`);
  console.log('[webpack-dev-server]', 'In your config.paths.php, ensure these lines exist: ');
  console.log('[webpack-dev-server]', '$PATHS_CONFIG[\'asset_paths\'] = [\r\n' +
      '  \'assets_root\' => [\r\n' +
      '      \'type\'    => \'url\',\r\n' +
      '      \'value\'   => \'http' + (httpsEnabled ? 's' : '')  + '://' + assetServerHostname + ':' + assetServerPort + '/\',\r\n' +
      '  ],\r\n' +
      '  \'app_assets\' => [\r\n' +
      '      \'type\'    => \'url\',\r\n' +
      '      \'value\'   => \'http' + (httpsEnabled ? 's' : '')  + '://' + assetServerHostname + ':' + assetServerPort + '/pub/build/\'\r\n' +
      '  ],\r\n' +
      '];');
});

return app;
