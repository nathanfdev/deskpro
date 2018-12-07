import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class DeleteButton extends React.Component {

  static propTypes = {
    label:    PropTypes.string,
    iconOnly: PropTypes.bool,
    disabled: PropTypes.bool,
    onClick:  PropTypes.func
  };

  static defaultProps = {
    label: 'Preview'
  };

  onClick = (event) => {
    event.preventDefault();
    const { disabled, onClick } = this.props;

    if (disabled) {
      return;
    }

    onClick();
  };

  render() {
    const { label, iconOnly, disabled } = this.props;

    return (
      <button
        className={classNames('ui basic button preview-button', { disabled, icon: iconOnly || !label })}
        onClick={this.onClick}
      >
        <i className={classNames('delete', 'icon')} />
        {!iconOnly && label}
      </button>
    );
  }
}

