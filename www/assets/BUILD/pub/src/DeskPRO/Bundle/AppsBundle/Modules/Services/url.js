import { default as URL } from 'url-parse';

export function changeUrl(baseUrl, newParts) {
  const builder = new URL(baseUrl);
  // switch to https if we host window is using https but oauthProxyEndpoint is not, mostly affects dev environs
  const isWindowHttps = window && typeof window.location === 'object' && window.location.protocol === 'https:';
  if (isWindowHttps && builder.protocol !== 'https') {
    builder.set('protocol', 'https');
  }

  Object.keys(newParts).forEach(partName => builder.set(partName, newParts[partName]));
  return builder.toString();
}

export function appendPathname(baseUrl, append) {
  const builder = new URL(baseUrl);
  let existingPath = builder.pathname;
  if (!existingPath) {
    existingPath = '';
  }
  const pathname = `${existingPath.trim('/')}/${append}`;
  return builder.set('pathname', pathname).toString();
}
