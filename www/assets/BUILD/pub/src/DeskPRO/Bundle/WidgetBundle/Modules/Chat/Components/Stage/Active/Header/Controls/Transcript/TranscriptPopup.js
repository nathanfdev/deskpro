import PropTypes from 'prop-types';
import React from 'react';

export class TranscriptPopup extends React.Component {

  static propTypes = {
    onClose:  PropTypes.func,
    children: PropTypes.any
  };

  render() {
    const { onClose, children } = this.props;

    return (
      <div className="dpdesignportal-popover dpdesignportal-popover-request-transcript">
        <div className="dpdesignportal-popover-close" onClick={onClose}>
          <i className="fa fa-times" />
        </div>

        {children}
      </div>
    );
  }
}
