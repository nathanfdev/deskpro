import PropTypes from 'prop-types';
import React from 'react';
import BannedMessage from './BannedMessage';

export class ChatBeginSimple extends React.Component {

  static propTypes = {
    banned:   PropTypes.bool,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    this.props.onSubmit();
  }

  render() {
    const { banned } = this.props;

    return (
      <div className="dpdesignportal-collect-user-info-waiting">
        {banned ? <BannedMessage /> : <div className="spinner"><i /></div>}
      </div>
    );
  }
}
