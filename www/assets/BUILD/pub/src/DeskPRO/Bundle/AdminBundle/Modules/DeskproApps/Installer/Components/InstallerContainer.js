import PropTypes from 'prop-types';
import React from 'react';
import MarkdownIt from 'markdown-it';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { ScreenInstallerError } from './ScreenInstallerError';
import { ScreenInstallerLoading } from './ScreenInstallerLoading';
import { ScreenConfirmInstall } from './ScreenConfirmInstall';
import { InstallerErrors } from '../InstallerErrors';


/**
 *
 * @param {*} manifest
 * @returns Promise
 */
function getReadme(manifest) {
  const assets = manifest.assets;
  let readmeDownloadUrl = null;
  for (let i = 0; i < assets.length; i++) {
    if (assets[i].path === 'docs/ADMIN_README.md') {
      readmeDownloadUrl = assets[i].blob.download_url;
    }
  }

  if (!readmeDownloadUrl) {
    return Promise.resolve(null);
  }

  return api.sendGet(readmeDownloadUrl)
    .then((resp) => {
      const md = new MarkdownIt();
      return md.render(resp.data);
    });
}

export class InstallerContainer extends React.Component {
  static propTypes = {
    app:                    PropTypes.string.isRequired,
    installType:            PropTypes.string.isRequired,
    loadInstance:           PropTypes.func.isRequired,
    createInstance:         PropTypes.func.isRequired,
    loadPackage:            PropTypes.func.isRequired,
    loadInstaller:          PropTypes.func.isRequired,
    renderInstallerBundled: PropTypes.func.isRequired,
    renderInstallerDefault: PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.initState();
  }

  componentDidMount()  {
    this.loadInitialState()
      .catch(error => ({ route: 'error', error }))
      .then(state => this.setState(state))
    ;
  }

  initState()  {
    this.state = {
      error:             null,
      errorType:         null,
      route:             'loading',
      readme:            null,
      appManifest:       null,
      installerManifest: null,
      packageManifest:   null
    };
  }

  loadInitialState()  {
    const { installType, loadPackage } = this.props;

    if (installType === 'update') {
      return this.loadAppManifests()
        .then(
          state => ({ ...state, route: 'settings' })
        )
        .then(
          state => loadPackage(state.appManifest.name).then(packageManifest => getReadme(packageManifest))
            .then(readme => ({ ...state, readme }))
        )
      ;
    }

    if (installType === 'install') {
      return this.loadPackageManifest()
        .then(state => ({ ...state, route: 'confirm-install' }))
        .then(state => getReadme(state.packageManifest).then(readme => ({ ...state, readme })))
      ;
    }

    const error = new Error('unexpected install action');
    error.deskpro = { type: InstallerErrors.UNEXPECTED_INSTALL_TYPE, installType };
    return Promise.reject(error);
  }

  loadPackageManifest()  {
    const { app, loadPackage } = this.props;

    return loadPackage(app)
      .then(
        /* eslint-disable no-shadow */
        packageManifest => ({ packageManifest })
      )
      .catch((error) => {
        if (typeof error === 'object') {
          error.deskpro = { type: InstallerErrors.LOAD_MANIFEST_FAIL_PACKAGE, app };
        }
        return {
          route:     'error',
          error,
          errorType: InstallerErrors.LOAD_MANIFEST_FAIL_PACKAGE
        };
      })
    ;
  }

  /**
   * @param {boolean} [createInstanceFirst]
   */
  loadAppManifests(createInstanceFirst)  {
    const { app, loadInstance, loadInstaller, createInstance } = this.props;
    const action = createInstanceFirst ? createInstance : loadInstance;

    let appManifest;
    return action(app)
      .then((manifest) => {
        appManifest = manifest;
        return loadInstaller(manifest);
      })
      .then(installerManifest => ({ installerManifest, appManifest }))
      .catch((error) => {
        if (typeof error === 'object') {
          error.deskpro = { type: InstallerErrors.LOAD_MANIFEST_FAIL_APP, app, createInstanceFirst };
        }

        return { route: 'error', error, errorType: InstallerErrors.LOAD_MANIFEST_FAIL_APP };
      })
    ;
  }

  renderScreen()  {
    const { route } = this.state;

    if (route === 'error') {
      const { error } = this.state;
      if (error) {
        return <ScreenInstallerError error={error} />;
      }
    }

    if (route === 'confirm-install') {
      const { packageManifest } = this.state;
      return (<ScreenConfirmInstall
        onConfirm={() => this.loadAppManifests(true).then(state => this.setState({ ...state, route: 'settings' }))}
        packageManifest={packageManifest}
      />
      );
    }

    if (route === 'settings') {
      const { installType, renderInstallerBundled, renderInstallerDefault } = this.props;
      const { appManifest, installerManifest, packageManifest } = this.state;
      if (installerManifest) { // we have an installer
        return renderInstallerBundled({ installType, appManifest, installerManifest });
      }
      return renderInstallerDefault({ installType, appManifest, packageManifest });
    }

    if (route === 'loading') {
      return <ScreenInstallerLoading />;
    }

    const error = new Error('unknown installer route');
    return <ScreenInstallerError error={error} />;
  }

  render() {
    const { readme } = this.state;

    const hrStyle = {
      marginLeft:   15,
      marginRight:  15,
      border:       0,
      borderBottom: '1px dotted #aaa',
      width:        'auto'
    };

    const sectionStyle = {
      margin: 15
    };

    return (
      <div>
        {this.renderScreen()}
        {readme && (
          <div>
            <hr style={hrStyle} />
            <section
              style={sectionStyle}
              dangerouslySetInnerHTML={{ __html: readme }}
            />
          </div>
        )}
      </div>
    );
  }
}
