import PropTypes from 'prop-types';
import React from 'react';
import { SingleForm } from './Form/SingleForm';
import Loader from 'react-loader';

export class Content extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    settings:       PropTypes.object.isRequired,
    settingsLoaded: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, settings, settingsLoaded } = this.props;

    return (
      <Loader loaded={settingsLoaded}
        opacity={0}
        width={3}
      >

        <div className="user-signature-settings">
          <h1>Signature</h1>

          <SingleForm dispatch={dispatch} settings={settings} />
        </div>
      </Loader>
    );
  }
}
