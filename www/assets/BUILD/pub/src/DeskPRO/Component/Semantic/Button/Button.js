import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

class Button extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string,
    onClick:   PropTypes.func,
    disabled:  PropTypes.bool,
    confirm:   PropTypes.bool,
  };
  static defaultProps = {
    disabled: false,
    confirm:  false,
    onClick() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      confirm: false
    };
  }

  getLabel = () => {
    if (this.state.confirm) {
      return 'Are you sure?';
    }
    return this.props.children;
  };

  cancelConfirm = () => {
    this.setState({
      confirm: false
    });
  };

  handleClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!this.props.disabled) {
      if (!this.props.confirm || this.state.confirm) {
        this.props.onClick(e);
        this.setState({
          confirm: false
        });
      } else if (this.props.confirm) {
        this.setState({
          confirm: true
        });
      }
    }
  };

  render() {
    const { className } = this.props;
    const button = (
      <button
        className={classNames('ui button', className, { disabled: this.props.disabled })}
        onClick={this.handleClick}
      >
        {this.getLabel()}
      </button>
    );
    if (this.state.confirm) {
      return (
        <ClickOut onClickOut={this.cancelConfirm} className={className}>{button}</ClickOut>
      );
    }
    return button;
  }
}
export default Button;
