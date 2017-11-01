import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';

export class BaseTaskCard extends React.Component {

  static propTypes = {
    task:             PropTypes.object,
    selected:         PropTypes.bool,
    onToggleSelected: PropTypes.func,
    onChange:         PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      comments: 1
    };
  }

  onToggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  isMinimized() {
    const { task } = this.props;
    return !this.state.expanded && task.get('is_done');
  }

  onChange = (prop, value) => {
    if (this.props.onChange) {
      this.props.onChange(prop, value);
    }
  };
}
