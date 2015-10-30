import React, { PropTypes } from 'react';
import { SingleForm } from './Form/SingleForm';
import Loader from 'react-loader';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    settings: PropTypes.object.isRequired,
    settingStatus: PropTypes.object.isRequired
  };

  render() {
    const { settings, settingStatus } = this.props;

    return (
      <Loader loaded={settingStatus.get('isDone')}
              opacity={0}
              width={3}>

        <div className="user-signature-settings">
          <h1>Signature</h1>

          <SingleForm settings={settings} />
        </div>
      </Loader>
    );
  }
}
