import React, { PropTypes } from 'react';
import Immutable from 'immutable';

export class BaseListGroup extends React.Component {

  static propTypes = {
    group:             PropTypes.object.isRequired,
    isOver:            PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired,
    onUpdate:          PropTypes.func
  };

  constructor(props) {
    super(props);
    const empty = Immutable.fromJS([]);
    this.state = {
      title:      props.group.get('title', ''),
      elements:   props.group.get('elements', empty),
      updateData: props.group.get('updateData', {}),
      isOver:     props.isOver
    };
  }

  componentWillReceiveProps(props) {
    const empty = Immutable.fromJS([]);
    this.setState({
      title:      props.group.get('title', ''),
      elements:   props.group.get('elements', empty),
      updateData: props.group.get('updateData', Immutable.Map()),
      isOver:     props.isOver
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.elements, state.elements) || this.state.isOver !== state.isOver;
  }

  componentWillUpdate(props, state) {
    if (this.props.onUpdate) {
      this.props.onUpdate(state.elements);
    }
  }

  onUpdate = (key, task) => {
    this.setState({
      elements: this.state.elements.set(key, task)
    });
  };

  render() {
    return <div />;
  }
}
