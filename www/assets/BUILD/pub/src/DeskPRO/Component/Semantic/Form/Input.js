import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Input extends React.Component {
  static propTypes = {
    icon:         PropTypes.string,
    iconPosition: PropTypes.string,
    onChange:     PropTypes.func,
    onKeyPress:   PropTypes.func,
    onEnterKey:   PropTypes.func
  };
  static defaultProps = {
    onChange() {},
    onKeyPress() {},
    onEnterKey() {}
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', this.props.icon)} />;
    }
    return null;
  };

  handleChange = (event) => {
    this.props.onChange(event.target.value);
  };

  keyPress = (event) => {
    this.props.onKeyPress(event.target.value);

    if (event.key === 'Enter') {
      this.props.onEnterKey(event.target.value);
    }
  };

  render() {
    const { icon, iconPosition, ...rest } = this.props;
    const divProps = Object.assign({}, rest);
    delete divProps.onEnterKey;
    delete divProps.onChange;
    delete divProps.onKeyPress;
    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input
          ref={(c) => { this.input = c; }}
          onChange={this.handleChange}
          onKeyPress={this.keyPress}
          {...divProps}
        />
        {this.getIcon()}
      </div>
    );
  }
}
export default Input;
