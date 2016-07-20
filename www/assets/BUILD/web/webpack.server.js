const express              = require('express');
const cors                 = require('cors');
const webpack              = require('webpack');
const webpackDevMiddleware = require('webpack-dev-middleware');
const webpackHotMiddleware = require('webpack-hot-middleware');
const webpackConfig        = require('./webpack.config');

const app      = express();
const compiler = webpack(webpackConfig);
app.use(webpackDevMiddleware(compiler, {
  publicPath:         'http://localhost:9666/pub/build/',
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
app.listen(9777, '0.0.0.0', (err) => {
  if (err) {
    throw new console.log('webpack-dev-server', err);
  }
});
