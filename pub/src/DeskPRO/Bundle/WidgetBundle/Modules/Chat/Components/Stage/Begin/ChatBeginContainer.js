import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { createChat } from '../../../Actions/chatActions';
import { isCreated } from '../../../Selectors/chat';
import { Header } from './Header';
import history from '../../../../../Services/history';

@connect(state => ({
  isCreated: isCreated(state)
}))
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    children: PropTypes.node,
    isCreated: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      name: '',
      email: '',
      hiddenEmail: false
    };
  }

  componentDidUpdate() {
    if (this.props.isCreated) {
      history.replaceState(null, '/chat/waiting');
    }
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

  onToggleHiddenEmail = event => {
    event.preventDefault();
    this.setState({
      hiddenEmail: !this.state.hiddenEmail
    });
  };

  onSubmit = event => {
    event.preventDefault();
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
