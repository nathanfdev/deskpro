import React, { PropTypes } from 'react';

export class UserInfoForm extends React.Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    onSubmit: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit();
  };

  render() {
    const { title, children } = this.props;

    return (
      <div className="dpdesignportal-collect-user-info">
        <span className="title">{title}</span>
        <form onSubmit={this.onSubmit}>
          {children}
        </form>
      </div>
    );
  }
}
