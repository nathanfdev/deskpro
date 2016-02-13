import { ApiRepository } from './Repository/ApiRepository';
import { AbstractRepository } from './Repository/AbstractRepository';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

let repositoriesConfig;

/**
 * Map of record name to its' repository object
 * @type {{}}
 */
let repositories = {};

/**
 * Loads repositories configuration object
 *
 * @param config
 */
export function loadRepositoriesConfig(config) {
  repositoriesConfig = config;
}

/**
 * Returns record repository
 *
 * @param record
 */
export function repository(record) {
  if (!repositoriesConfig.hasOwnProperty(record)) {
    throw `No repository defined for the ${record} record`;
  }

  if (!repositories.hasOwnProperty(record)) {
    const config = normalizeConfig(repositoriesConfig[record]);
    if (config.hasOwnProperty('repository')) {
      repositories[record] = config['repository'];
    } else {
      if (!config.hasOwnProperty('type')) {
        throw `${record} repository config must have either "repository" or "type" property`;
      }

      switch (config['type']) {
        case 'api':
          repositories[record] = createApiRepository(config, record);
          break;
        case 'factory':
          repositories[record] = createRepositoryFromFactory(config, record);
          break;
        default:
          throw new Error(`Unknown repository type ${config['type']} in the ${record} record definition`);
      }
    }
  }

  return repositories[record];
}

/**
 * @param config
 * @param record
 * @returns {ApiRepository}
 */
function createApiRepository(config, record) {
  if (!config.hasOwnProperty('url')) {
    throw new Error(`${record} repository config must have "url" option`);
  }

  const url = config['url'];
  const allowAll = config.hasOwnProperty('allowAll') ? config['allowAll'] : false;
  const repositoryClass = config.hasOwnProperty('repositoryClass') ? config['repositoryClass'] : ApiRepository;

  return new repositoryClass(api, url, allowAll);
}

/**
 * @param config
 * @param record
 * @returns {*}
 */
function createRepositoryFromFactory(config, record) {
  if (!config.hasOwnProperty('factory')) {
    throw new Error(`${record} repository config must define its factory`);
  }

  return config.factory();
}

/**
 * Normalizes repository config
 *
 * This is used to shorten configs in the following ways
 *
 * - allow passing string type instead of config object: 'value' => {type: 'value'}
 *
 * @param config
 * @returns {{}}
 */
function normalizeConfig(config) {
  if (typeof config === 'string') {
    return {type: config};
  } else {
    return config;
  }
}
