import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TransferListButton extends React.Component {

  static propTypes = {
    title:    PropTypes.string,
    icon:     PropTypes.string,
    help:     PropTypes.string,
    disabled: PropTypes.bool,
    onClick:  PropTypes.func
  };

  static defaultProps = {
    onClick: () => {}
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { title, icon, help, disabled } = this.props;

    return (
      <a className={classNames('voice-list-button', { disabled })} onClick={this.onClick}>
        <span className="voice-list-button-item-title">
          <i className={classNames('ui', 'icon', icon)} />
          {title}

          <span className="voice-list-button-item-help">
            {help}
          </span>
        </span>
      </a>
    );
  }
}

export default TransferListButton;
