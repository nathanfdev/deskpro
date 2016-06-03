import React, { PropTypes } from 'react';

export class ChatBeginSimple extends React.Component {

  static propTypes = {
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    this.props.onSubmit();
  }

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-waiting">
        <div className="spinner">
          <i />
        </div>
      </div>
    );
  }
}
