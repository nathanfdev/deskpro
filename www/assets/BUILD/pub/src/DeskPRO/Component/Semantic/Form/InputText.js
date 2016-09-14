import React, { PropTypes } from 'react';
import classNames from 'classnames';

class InputText extends React.Component {
  static propTypes = {
    icon:         PropTypes.string,
    iconPosition: PropTypes.string,
    id:           PropTypes.string,
    name:         PropTypes.string,
    placeholder:  PropTypes.string,
    value:        PropTypes.string
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', this.props.icon)} />;
    }
    return null;
  };

  render() {
    const { placeholder, icon, iconPosition, id, value } = this.props;
    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input id={id}  value={value} ref={(c) => { this.input = c; }} type="text" name={name} placeholder={placeholder} />
        {this.getIcon()}
      </div>
    );
  }
}
export default InputText;
