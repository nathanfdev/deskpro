const express               = require('express');
const webpack               = require('webpack');
const cors                  = require('cors');
const webpackDevMiddleware  = require('webpack-dev-middleware');
const webpackHotMiddleware  = require('webpack-hot-middleware');
const config   = require('../webpack.config');

const app      = express();
const compiler = webpack(config);

const assetServerHostname = process.env.ASSET_SERVER_HOSTNAME || 'localhost';
const assetServerPort = process.env.ASSET_SERVER_PORT || 9666;

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
app.listen(assetServerPort, '0.0.0.0', (err) => {
  if (err) {
    throw new gutil.PluginError('webpack-dev-server', err);
  }

  console.log('[webpack-dev-server]', `http://${assetServerHostname}:${assetServerPort}/`);
  console.log('[webpack-dev-server]', 'In your config.paths.php, ensure these lines exist: ');
  console.log('[webpack-dev-server]', '$PATHS_CONFIG[\'asset_paths\'] = [\r\n' +
      '  \'assets_root\' => [\r\n' +
      '      \'type\'    => \'url\',\r\n' +
      '      \'value\'   => \'http://' + assetServerHostname + ':' + assetServerPort + '/\',\r\n' +
      '  ],\r\n' +
      '  \'app_assets\' => [\r\n' +
      '      \'type\'    => \'url\',\r\n' +
      '      \'value\'   => \'http://' + assetServerHostname + ':' + assetServerPort + '/pub/build/\'\r\n' +
      '  ],\r\n' +
      '];');
});

return app;