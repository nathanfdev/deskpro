import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { createChat } from '../../../Actions/chatActions';
import { liveDemoSelector } from '../../../../Application/Selectors/dpWindow';
import { requireChatEmailValidationSelector, requireChatLoginSelector } from '../../../../Application/Selectors/bootstrap';
import { history } from '../../../../../Services/history';

@connect(state => ({
  liveDemo:               liveDemoSelector(state),
  requireEmailValidation: requireChatEmailValidationSelector(state),
  requireLogin:           requireChatLoginSelector(state)
}))
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    requireEmailValidation: PropTypes.bool,
    requireLogin:           PropTypes.bool,
    dispatch:               PropTypes.func.isRequired,
    children:               PropTypes.node,
    isCreated:              PropTypes.bool,
    liveDemo:               PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      name:         '',
      email:        '',
      hidden_email: false,
      submit:       false,
      errors:       null
    };
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillReceiveProps(newProps) {
    if (newProps.children !== this.props.children) {
      this.setState({
        name:         '',
        email:        '',
        hidden_email: false,
        errors:       null
      });
    }
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeName = event => {
    this.setState({
      name:   event.target.value,
      errors: null
    });
  };

  onChangeEmail = event => {
    this.setState({
      email:  event.target.value,
      errors: null
    });
  };

  onToggleHiddenEmail = event => {
    event.preventDefault();
    this.setState({
      hidden_email: !this.state.hidden_email
    });
  };

  onSubmit = event => {
    if (event) {
      event.preventDefault();
    }

    const { liveDemo, requireEmailValidation, requireLogin } = this.props;

    // Disabled in live demo mode
    if (liveDemo) {
      return;
    }

    this.setState({
      submit: true
    });

    const promise = this.props.dispatch(createChat({
      name:  this.state.name,
      email: this.state.email
    }));

    promise.then(
      () => {
        if (requireEmailValidation && !requireLogin) {
          history.replace('/chat/validation/email');
        } else {
          history.replace('/chat/waiting');
        }

        if (this.mounted) {
          this.setState({
            submit: false
          });
        }
      },
      result => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData()
          });
        }
      }
    );
  };

  render() {
    const props = this.props;
    const state = this.state;

    const { children } = props;
    const childProps = children.props;

    const content = React.cloneElement(children, {
      ...props,
      ...childProps,
      ...state,

      hiddenEmail: state.hidden_email,

      onChangeName:        this.onChangeName,
      onChangeEmail:       this.onChangeEmail,
      onToggleHiddenEmail: this.onToggleHiddenEmail,
      onSubmit:            this.onSubmit
    });

    return (
      <div>
        {content}
      </div>
    );
  }
}
