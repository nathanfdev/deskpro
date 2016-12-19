import { config } from './config.js';

function url(relative) {
  return config.baseUrl + relative;
}

exports.url = url;
