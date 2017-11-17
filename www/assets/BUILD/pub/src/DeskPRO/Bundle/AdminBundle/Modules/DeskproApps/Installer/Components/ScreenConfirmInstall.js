import React from 'react';
import PropTypes from 'prop-types';
import { AppInfo } from './AppInfo';

const ScreenConfirmInstall = ({ packageManifest, onConfirm }) =>  // eslint-disable-line class-methods-use-this, no-unused-vars
   (
     <AppInfo
       iconUrl={packageManifest.icon_url}
       description={packageManifest.manifest.description}
       title={packageManifest.manifest.title}
       version={packageManifest.manifest.appVersion}
     >
       <button className={'btn btn-install btn-success'} onClick={onConfirm}>Install App</button>
     </AppInfo>

  );

ScreenConfirmInstall.propTypes = {
  packageManifest: PropTypes.object.isRequired,
  onConfirm:       PropTypes.func.isRequired
};

export { ScreenConfirmInstall };
