import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Input extends React.Component {
  static propTypes = {
    icon:         PropTypes.string,
    iconPosition: PropTypes.string,
    id:           PropTypes.string,
    maxLength:    PropTypes.number,
    name:         PropTypes.string,
    placeholder:  PropTypes.string,
    value:        PropTypes.string,
    type:         PropTypes.string,
    onChange:     PropTypes.func,
    onKeyPress:   PropTypes.func,
    onEnterKey:   PropTypes.func
  };
  static defaultProps = {
    type: 'text',
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
    const { placeholder, icon, iconPosition, id, maxLength, name, type, value } = this.props;
    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input
          id={id}
          value={value}
          ref={(c) => { this.input = c; }}
          type={type}
          name={name}
          maxLength={maxLength}
          onChange={this.handleChange}
          onKeyPress={this.keyPress}
          placeholder={placeholder}
        />
        {this.getIcon()}
      </div>
    );
  }
}
export default Input;
