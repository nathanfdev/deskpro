import React from 'react';
import PropTypes from 'prop-types';
import QRCode from 'qrcode.react';
import Loader from '@deskpro/react-loader';

export class Content extends React.Component {
  static propTypes = {
    token: PropTypes.string.isRequired
  };

  render() {
    return (
      <div>
        <Loader loaded={Boolean(this.props.token)}>
          <QRCode value={this.props.token} />
        </Loader>
      </div>
    );
  }
}
