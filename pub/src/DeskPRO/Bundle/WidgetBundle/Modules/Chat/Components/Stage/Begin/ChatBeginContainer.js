import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { createChat } from '../../../Actions/chatActions';
import { Header } from './Header';

@connect()
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      name: '',
      email: '',
      hiddenEmail: false
    };
  }

  onChangeName = event => {
    this.setState({
      name: event.target.value
    });
  };

  onChangeEmail = event => {
    this.setState({
      email: event.target.value
    });
  };

  onToggleHiddenEmail = () => {
    this.setState({
      hiddenEmail: !this.state.hiddenEmail
    });
  };

  onSubmit = () => {
    this.props.dispatch(createChat(this.state));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    const content = React.cloneElement(children, {
      ...props,
      ...childProps,

      name: this.state.name,
      email: this.state.email,
      hiddenEmail: this.state.hiddenEmail,

      onChangeName: this.onChangeName,
      onChangeEmail: this.onChangeEmail,
      onToggleHiddenEmail: this.onToggleHiddenEmail,
      onSubmit: this.onSubmit
    });

    return (
      <div>
        <Header />
        {content}
      </div>
    );
  }
}
