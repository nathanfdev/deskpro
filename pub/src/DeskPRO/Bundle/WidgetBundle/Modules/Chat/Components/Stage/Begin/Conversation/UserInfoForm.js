import React, { PropTypes } from 'react';

export class UserInfoForm extends React.Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    onSubmit: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  render() {
    const { title, onSubmit, children } = this.props;

    return (
      <div className="dpdesignportal-collect-user-info">
        <span className="title">{title}</span>
        <form onSubmit={onSubmit}>
          {children}
        </form>
      </div>
    );
  }
}
