import React from 'react';
import { Installer } from './Installer';

const DEBUG = true;

const configPropsFromWindow = (windowObject) => {
  const { location : locationObject }  = windowObject;
  return {
    apiRoot: `${locationObject.protocol}//${locationObject.host}${windowObject.DP_BASE_URL.replace(/\/+$/i, '')}`
  };
};

const configPropsFromRoute = ({ params }) => {
  if (DEBUG) {
    const args = Array.prototype.slice.call(arguments);
    console.log(' route props ', args[0]);
  }

  if (!params) {
    return {};
  }

  const { app } = params;
  return { app };
};

class InstallerFactory extends React.Component {
  /**
   * @param {Window} windowObj
   * @return {{}}
   */
  static routeFactory(windowObj)  {
    const windowProps = configPropsFromWindow(windowObj);

    return class extends React.Component {
      render() {
        const routeProps = configPropsFromRoute(this.props);
        return <Installer {...routeProps} {...windowProps} />;
      }
    };
  }
}

export { InstallerFactory };
