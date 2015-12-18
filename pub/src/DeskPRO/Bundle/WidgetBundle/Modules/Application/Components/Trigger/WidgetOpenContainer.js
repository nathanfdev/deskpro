import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openWidget } from '../../Actions/dpWindowActions';
import { chatModeSelector } from '../../Selectors/dpWindow';
import history from '../../../../Services/history';

@connect(state => ({
  chatMode: chatModeSelector(state)
}))
export class WidgetOpenContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node,
    chatMode: PropTypes.string
  };

  onClick = () => {
    const { chatMode, dispatch } = this.props;

    dispatch(openWidget());
    switch (chatMode) {
      default:
      case 'simple':
        history.replace('/chat/begin/simple');
        break;
      case 'conversation':
        history.replace('/chat/begin/conversation');
        break;
      case 'form':
        history.replace('/chat/begin/form');
        break;
    }
  };

  render() {
    const props = this.props;
    const child = props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...props,
      ...childProps,

      onClick: this.onClick
    });
  }
}
