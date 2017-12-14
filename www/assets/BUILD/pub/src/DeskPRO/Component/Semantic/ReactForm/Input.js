import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Input extends React.Component {

  static propTypes = {
    icon:         PropTypes.string,
    iconPosition: PropTypes.string,
    onChange:     PropTypes.func
  };

  onChange = (event) => {
    this.props.onChange(event.currentTarget.value || '');
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', this.props.icon)} />;
    }
    return null;
  };

  render() {
    const { icon, iconPosition } = this.props;

    return (
      <div className={classNames('ui', 'input', iconPosition, { icon: !!icon })}>
        <input {...this.props} onChange={this.onChange} ref={(c) => { this.input = c; }} />
        {this.getIcon()}
      </div>
    );
  }
}

export default Input;
