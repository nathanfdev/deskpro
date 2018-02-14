const express               = require('express');
const webpack               = require('webpack');
const cors                  = require('cors');
const webpackDevMiddleware  = require('webpack-dev-middleware');
const webpackHotMiddleware  = require('webpack-hot-middleware');
const config   = require('../webpack.config');

const app      = express();
const compiler = webpack(config);
app.use(webpackDevMiddleware(compiler, {
  publicPath:         config.output.publicPath,
  hot:                true,
  historyApiFallback: true,
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
app.listen(9666, '0.0.0.0', (err) => {
  if (err) {
    throw new gutil.PluginError('webpack-dev-server', err);
  }

  console.log('[webpack-dev-server]', 'http://localhost:9666/');
  console.log('[webpack-dev-server]', 'In your config.paths.php, ensure these lines exist: ');
  console.log('[webpack-dev-server]', '\r\n$PATHS_CONFIG[\'asset_paths\'][\'app_assets\'] = [' +
    '\r\n    \'type\' => \'url\',' +
    '\r\n    \'value\' => \'http://localhost:9666/pub/build/\'' +
    '\r\n];'
  );
});

return app;