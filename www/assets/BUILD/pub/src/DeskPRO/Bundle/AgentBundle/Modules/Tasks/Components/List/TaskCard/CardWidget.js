import React, { PropTypes } from 'react';

export class CardWidget extends React.Component {

  static propTypes = {
    value:        PropTypes.any,
    isOpen:       PropTypes.bool,
    onChange:     PropTypes.func,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: props.isOpen,
      value:  props.value
    };
  }

  onChange = value => {
    this.setState({ value });
  };

  onOpen = () => {
    const { onSetEditing } = this.props;
    if (onSetEditing) {
      onSetEditing(true);
    }

    this.setState({
      isOpen: true
    });
  };

  onClose = () => {
    const { onSetEditing } = this.props;
    if (onSetEditing) {
      onSetEditing(false);
    }

    this.setState({
      isOpen: false
    });
  };
}
