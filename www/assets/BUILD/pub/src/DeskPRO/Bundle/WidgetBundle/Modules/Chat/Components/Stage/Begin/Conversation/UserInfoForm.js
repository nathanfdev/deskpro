import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class UserInfoForm extends React.Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    onSubmit: PropTypes.func.isRequired,
    children: PropTypes.any,
    error: PropTypes.bool
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit();
  };

  render() {
    const { title, children, error } = this.props;

    return (
      <div className={classNames('dpdesignportal-collect-user-info', {'error-field': error})}>
        <span className="title">{title}</span>
        <form onSubmit={this.onSubmit}>
          {children}
        </form>
      </div>
    );
  }
}
