import PropTypes from 'prop-types';
import React from 'react';
import invariant from 'invariant';
import { Modal } from './Modal';

export class Notification extends Modal {

  static defaultProps = {
    confirmTitle:   'Confirm & Continue',
    cancelTitle:    'Cancel this action',
    confirmVisible: true,
    cancelVisible:  true,
    zIndex:         1005
  };

  renderBody() {
    const { title, children, confirmTitle, cancelTitle } = this.props;

    let style = {};
    if (this.props.zIndex) {
      style.zIndex = this.props.zIndex;
    }

    return (
      <div className="dpw-site-cover" onClick={this.coverClick} style={style}>
        <div className="dpw-friendly-warning">
          <div className="dpw-friendly-warning-intro">A quick tip from DeskPRO</div>

          <div className="dpw-friendly-warning-container">
            <h1>
              {title}
            </h1>
            <p>
              {children}
            </p>

            <span className="dpw-friendly-warning-icon">
              <div className="logo"></div>
            </span>

            <span className="dpw-friendly-warning-more-help">
              <a href="">Need more help?</a>
            </span>

          </div>
          <a href="#" className="dpw-friendly-warning-confirm" onClick={this.confirmClick}>
            {confirmTitle}
          </a>
        </div>
        <a href="#" className="dpw-friendly-warning-cancel" onClick={this.cancelClick}>
          {cancelTitle}
        </a>
      </div>
    );
  }
}
