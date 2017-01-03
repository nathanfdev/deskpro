import { config } from './config';

function url(relative) {
  return config.baseUrl + relative;
}

exports.url = url;
