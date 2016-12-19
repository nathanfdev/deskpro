/**
 * URL to an asset within a bundle.
 * @param string bundle_name
 * @param string ... the asset's relative path
 * @return string the full path to the asset
 */
export function bundleUrl(bundle_name, ...path) {
  return '/pub/static/' + bundle_name + '/' + path.join('/');
}

/**
 * URL to a common asset, shared between all bundles.
 * @param string ... the asset's relative path
 * @return string the full path to the asset
 */
export function commonUrl(...path) {
  return bundleUrl('Common', path.join('/'));
}
