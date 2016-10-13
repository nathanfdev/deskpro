import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Input extends React.Component {
  static propTypes = {
    icon:         PropTypes.string,
    iconPosition: PropTypes.string,
    id:           PropTypes.string,
    name:         PropTypes.string,
    placeholder:  PropTypes.string,
    value:        PropTypes.string,
    type:         PropTypes.string,
    onChange:     PropTypes.func
  };
  static defaultProps = {
    onChange() {

    },
    type: 'text'
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', this.props.icon)} />;
    }
    return null;
  };

  render() {
    const { placeholder, icon, iconPosition, id, type, value, name, onChange } = this.props;
    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input
          onChange={onChange}
          id={id}
          value={value}
          ref={(c) => { this.input = c; }}
          type={type}
          name={name}
          placeholder={placeholder}
        />
        {this.getIcon()}
      </div>
    );
  }
}
export default Input;
