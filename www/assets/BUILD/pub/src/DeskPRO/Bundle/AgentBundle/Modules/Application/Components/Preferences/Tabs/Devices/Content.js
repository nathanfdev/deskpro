import React, { PropTypes } from 'react';
import QRCode from 'qrcode.react';
import Loader from 'react-loader';

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
