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
    type:         PropTypes.string
  };
  static defaultProps = {
    type: 'text'
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', this.props.icon)} />;
    }
    return null;
  };

  render() {
    const { placeholder, icon, iconPosition, id, type, value } = this.props;
    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input id={id}  value={value} ref={(c) => { this.input = c; }} type={type} name={name} placeholder={placeholder} />
        {this.getIcon()}
      </div>
    );
  }
}
export default Input;
