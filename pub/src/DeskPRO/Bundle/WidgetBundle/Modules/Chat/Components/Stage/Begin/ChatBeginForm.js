import React, { PropTypes } from 'react';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    onChangeName: PropTypes.func,
    onChangeEmail: PropTypes.func,
    onSubmit: PropTypes.func
  };

  render() {
    const { name, email } = this.props;
    const { onChangeName, onChangeEmail, onSubmit } = this.props;

    return (
      <div>
        Begin form mode

        <form onSubmit={onSubmit}>
          <input type="text" name={name} onChange={onChangeName} />
          <input type="text" name={email} onChange={onChangeEmail} />

          <input type="submit" />
        </form>
      </div>
    );
  }
}
