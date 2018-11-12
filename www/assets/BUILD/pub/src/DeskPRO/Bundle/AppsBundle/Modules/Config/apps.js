import uuid from 'uuid';
import { AppConfiguration, WidgetConfiguration }  from '../Domain';

/**
 * @param {{}} manifest
 * @param {Number} [bundleUpdatedAt]
 * @param {AppsConfig} config
 * @return {AppConfiguration}
 */
function createAppConfiguration(manifest, bundleUpdatedAt, config) {
  // we need to process a bit the manifest before we can use it
  let props;
  if (config.environment === 'development') {
    props = {
      id:              config.instanceId,
      application_id:  config.applicationId,
      baseUrl:         config.endpoint,
      ...manifest,
      bundleUpdatedAt: bundleUpdatedAt || 0
    };
  } else {
    props = { baseUrl: `${config.endpoint}/file.php/apps/${manifest.application_id}`, ...manifest, bundleUpdatedAt };
  }
  return AppConfiguration.fromAppManifestJS(props);
}

/**
 * @param {Object} targetsMap
 * @param {AppConfiguration} config
 * @param {{}} settings
 * @param target
 */
function populateTargetMap(targetsMap, { config, settings }) {
  for (const target of config.targets) {
    /** @type {string} */ const id = uuid.v4();
    targetsMap[target.target].push(
      new WidgetConfiguration({ id, target: target.target, appConfig: config, appSettings: settings })
    );
  }

  return targetsMap;
}

/**
 * @param {{}} acc
 * @param {AppConfiguration} config
 */
function createTargetMap(acc, { config }) {
  config.targets.forEach((x) => {
    acc[x.target] = [];
  });

  return acc;
}

export function createWidget({ settings, manifest, bundleUpdatedAt }, config, target) {
  /** @type {string} */ const id = uuid.v4();
  const appConfig = createAppConfiguration(manifest, bundleUpdatedAt, config);
  return new WidgetConfiguration({ id, target, appConfig, appSettings: settings });
}

/**
 * @param {Array<{settings:{}, manifest:{}}>} rawManifests
 * @param {AppsConfig} config
 * @returns {function}
 */
export function createWidgetProvider(rawManifests, config) {
  const appsNew = rawManifests.map(({ settings, manifest, bundleUpdatedAt }) => ({ settings, config: createAppConfiguration(manifest, bundleUpdatedAt, config) }));
  const targetsMap = appsNew.reduce(createTargetMap, {});
  appsNew.reduce(populateTargetMap, targetsMap);

  function provider(targetType)  {
    const widgets = targetsMap[targetType];
    if (widgets instanceof Array) {
      return [].concat(widgets);
    }

    return [];
  }

  return provider;
}

